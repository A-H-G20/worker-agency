<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// Get the logged-in user's ID from the session
$current_user_id = $_SESSION['user_id'];

// Get the receiver's ID and message from the form submission
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($receiver_id === 0 || empty($message)) {
    echo 'Invalid input.';
    exit();
}

// Insert the message into the database
$query_insert_message = "
    INSERT INTO messages (sender_id, receiver_id, content, created_at)
    VALUES (?, ?, ?, NOW())
";

$stmt = mysqli_prepare($conn, $query_insert_message);
if (!$stmt) {
    echo 'Error preparing the query: ' . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, 'iis', $current_user_id, $receiver_id, $message);
$success = mysqli_stmt_execute($stmt);

if (!$success) {
    echo 'Error executing the query: ' . mysqli_error($conn);
} else {
    // Redirect back to the chat page with the updated chat history
    header("Location: chat_with_user.php?user_id=" . $receiver_id);
    exit();
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
