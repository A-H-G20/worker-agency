<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = 'image/'; // Absolute path to the folder where images are stored
        $imagePath = $uploadDir . $imageName;

        // Verify the directory exists
        if (!is_dir($uploadDir)) {
            echo "Error: Directory does not exist.";
            exit();
        }

        // Validate file type (optional but recommended)
        $allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($imageTmpPath);

        if (in_array($fileType, $allowedFileTypes)) {
            // Move uploaded file to the target directory
            if (move_uploaded_file($imageTmpPath, $imagePath)) {
                // Update data in the database
                $query = "UPDATE users SET profile = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'si', $imageName, $user_id);
                    if (mysqli_stmt_execute($stmt)) {
                        echo "Profile updated successfully!";
                    } else {
                        echo "Error: Could not execute the query.";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo "Error: Could not prepare the query.";
                }
            } else {
                echo "Error: Could not move the uploaded file.";
            }
        } else {
            echo "Error: Invalid file type. Please upload a JPEG, PNG, or GIF image.";
        }
    } else {
        echo "Error: No file uploaded or there was an upload error.";
    }

    // Close the connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <header>
        <li><a href="dashboard.php">home</a></li>
    </header>
    <form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="edit-image">Image</label>
        <input type="file" id="edit-image" name="image" required />
    </div>
    <div class="form-group">
        <button type="submit">Add</button>
        <button type="button" class="edit-cancel-button">Cancel</button>
    </div>
</form>
</body>
</html>