<?php
// Start session and include database configuration
session_start();
require '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get post ID from query string
if (isset($_GET['post_id'])) {
    $post_id = intval($_GET['post_id']); // Sanitize input

    // Begin transaction
    mysqli_begin_transaction($conn);

    try {
        // Delete comments related to the post
        $delete_comments_query = "DELETE FROM comments WHERE post_id = ?";
        $stmt = mysqli_prepare($conn, $delete_comments_query);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $post_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Delete the post
        $delete_post_query = "DELETE FROM post WHERE post_id = ?";
        $stmt = mysqli_prepare($conn, $delete_post_query);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $post_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Commit transaction
        mysqli_commit($conn);

        // Redirect to the dashboard
        header("Location: post_management.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}

// Close connection
mysqli_close($conn);
?>
