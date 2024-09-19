<?php
session_start();
require '../config.php'; // Include your database connection details

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } else {
        // Fetch the current password hash from the database
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $current_password_hash);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Verify the old password
        if (!password_verify($old_password, $current_password_hash)) {
            $error_message = "Old password is incorrect.";
        } else {
            // Hash the new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $query_update = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $query_update);
            mysqli_stmt_bind_param($stmt_update, 'si', $new_password_hash, $user_id);
            if (mysqli_stmt_execute($stmt_update)) {
                $success_message = "Password updated successfully.";
            } else {
                $error_message = "Error updating password: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_update);
        }
    }
    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/settings.css"> <!-- Include your CSS file -->
    <link href="../image/local_image/logo.png" rel="icon">
</head>

<body>
<header class="sidebar">
    <nav>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="admin_management.php">Admin Management</a></li>
        <li><a href="post_management.php">Post Management</a></li>
        <li><a href="settings.php">Setting</a></li>
        <li><a href="logout.php">Logout</a></li>
    </nav>
</header>

    <form method="POST" action="">
        <div class="input-pass">
            <input type="password" name="old_password" required placeholder="Old Password">
            <input type="password" name="new_password" required placeholder="New Password">
            <input type="password" name="confirm_password" required placeholder="Confirm Password">
        </div>
        <div class="button">
            <button type="submit">Confirm</button>
        </div>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php elseif (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
    </form>

</body>

</html>