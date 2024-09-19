<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$current_user_id = $_SESSION['user_id'];

// Get the user ID from the query parameter
$chat_with_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($chat_with_user_id === 0) {
    echo 'Invalid user ID.';
    exit();
}

// Fetch user details for the chat
$query_chat_user = "
    SELECT profile, name
    FROM users
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $query_chat_user);
if (!$stmt) {
    echo 'Error preparing the query: ' . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, 'i', $chat_with_user_id);
mysqli_stmt_execute($stmt);

// Bind result variables
mysqli_stmt_bind_result($stmt, $profile, $name);

// Fetch the result
if (mysqli_stmt_fetch($stmt)) {
    $chat_user = [
        'profile' => $profile,
        'name' => $name
    ];
} else {
    echo 'No data found.';
    $chat_user = [];
}

mysqli_stmt_close($stmt);

// Fetch chat history
$query_chat_history = "
    SELECT sender_id, content, created_at
    FROM messages
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at
";

$stmt_chat = mysqli_prepare($conn, $query_chat_history);
if (!$stmt_chat) {
    echo 'Error preparing the query: ' . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt_chat, 'iiii', $current_user_id, $chat_with_user_id, $chat_with_user_id, $current_user_id);
mysqli_stmt_execute($stmt_chat);

// Bind result variables for chat history
mysqli_stmt_bind_result($stmt_chat, $sender_id, $message, $created_at);

$chat_history = [];
while (mysqli_stmt_fetch($stmt_chat)) {
    $date = date('Y-m-d', strtotime($created_at));
    $dayOfWeek = date('l', strtotime($created_at)); // Day of the week
    $chat_history[$date] = [
        'day' => $dayOfWeek,
        'messages' => $chat_history[$date]['messages'] ?? []
    ];
    $chat_history[$date]['messages'][] = [
        'sender_id' => $sender_id,
        'message' => $message,
        'created_at' => $created_at
    ];
}

mysqli_stmt_close($stmt_chat);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/chat.css">
    <link href="image/local_image/logo.png" rel="icon">
    <title>Chat with <?php echo htmlspecialchars($chat_user['name']); ?></title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatWithUserId = <?php echo json_encode($chat_with_user_id); ?>;
            const chatMessagesElement = document.querySelector('.chat-messages');
            const messageForm = document.querySelector('form');
            const messageInput = document.querySelector('input[name="message"]');

            function fetchMessages() {
                fetch('fetch_messages.php?user_id=' + chatWithUserId)
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data)) {
                            chatMessagesElement.innerHTML = '';
                            let lastDate = '';

                            data.forEach(message => {
                                const messageDate = new Date(message.created_at).toLocaleDateString();
                                const dayOfWeek = new Date(message.created_at).toLocaleDateString('en-US', {
                                    weekday: 'long'
                                });

                                if (messageDate !== lastDate) {
                                    const dateHeader = document.createElement('div');
                                    dateHeader.className = 'date-header';
                                    dateHeader.textContent = `${dayOfWeek}, ${messageDate}`;
                                    chatMessagesElement.appendChild(dateHeader);
                                    lastDate = messageDate;
                                }

                                const messageElement = document.createElement('div');
                                messageElement.className = `message ${message.sender_id === <?php echo json_encode($current_user_id); ?> ? 'sent' : 'received'}`;
                                messageElement.innerHTML = `
                                    <div class="message-text">
                                        <p>${message.message}</p>
                                    </div>
                                    <div class="message-time">
                                        <small>${new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</small>
                                    </div>
                                `;
                                chatMessagesElement.appendChild(messageElement);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching messages:', error));
            }

            // Fetch messages every 3 seconds
            setInterval(fetchMessages, 3000);

            messageForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(messageForm);
                fetch('send_message.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(() => {
                        messageInput.value = '';
                        fetchMessages(); // Fetch new messages after sending
                    })
                    .catch(error => console.error('Error sending message:', error));
            });

            fetchMessages(); // Initial fetch of messages
        });
    </script>
</head>

<body>
    <div class="chat-container">

        <div class="chat-header">
            <a href="contact.php" class="back-link">
                <img src="image/icons/back.png " alt="Back to Content" class="back-image">
            </a>
            <span class="chat-header-name"><?php echo htmlspecialchars($chat_user['name']); ?></span>
        </div>

        <div class="chat-messages">
            <!-- Messages will be injected here by JavaScript -->
        </div>
        <div class="chat-input-container">
            <form action="send_message.php" method="post">
                <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($chat_with_user_id); ?>">
                <input type="text" name="message" required placeholder="Type a message...">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
</body>

</html>