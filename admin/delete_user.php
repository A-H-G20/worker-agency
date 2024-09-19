<?php
// Include database configuration file
include('config.php');

// Check if 'id' is set in the query string
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']); // Get the user ID and ensure it's an integer

    // Start transaction
    $mysqli->begin_transaction();

    try {
        // Prepare and execute query to delete related messages
        $stmt = $mysqli->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $stmt->close();

        // Prepare and execute query to delete related friends records
        $stmt = $mysqli->prepare("DELETE FROM friends WHERE user_id = ? OR friend_id = ?");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $stmt->close();

        // Prepare and execute query to delete the user
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $mysqli->commit();

        // Redirect to the user management page with a success message
        header("Location: user_management.php?message=User%20and%20related%20records%20deleted%20successfully");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();

        // Redirect to the user management page with an error message
        header("Location: user_management.php?error=An%20error%20occurred:%20" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirect to the user management page with an error message if no ID is set
    header("Location: user_management.php?error=No%20user%20ID%20provided");
    exit();
}

// Close the connection
$mysqli->close();
?>
