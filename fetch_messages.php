<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$current_user_id = $_SESSION['user_id'];
$chat_with_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($chat_with_user_id === 0) {
    echo json_encode(['error' => 'Invalid user ID.']);
    exit();
}

// Fetch chat history (new messages since the last check)
$query_chat_history = "
    SELECT * FROM messages
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at
";

$stmt_chat = mysqli_prepare($conn, $query_chat_history);
if (!$stmt_chat) {
    echo json_encode(['error' => 'Error preparing the query: ' . mysqli_error($conn)]);
    exit();
}

mysqli_stmt_bind_param($stmt_chat, 'iiii', $current_user_id, $chat_with_user_id, $chat_with_user_id, $current_user_id);
mysqli_stmt_execute($stmt_chat);

// Bind result variables for chat history
mysqli_stmt_bind_result($stmt_chat, $msg_id, $sender_id, $receiver_id, $message, $created_at);

$chat_history = [];
while (mysqli_stmt_fetch($stmt_chat)) {
    $chat_history[] = [
        'msg_id' => $msg_id,
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'message' => $message,
        'created_at' => $created_at
    ];
}

mysqli_stmt_close($stmt_chat);
mysqli_close($conn);

header('Content-Type: application/json');
echo json_encode($chat_history);
?>
