<?php
session_start();
include 'config.php'; // Your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Fetch user data from the database
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);

// Check if the SQL query was prepared successfully
if ($stmt === false) {
    die('MySQL prepare statement error: ' . $conn->error); // Display MySQL error
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the result is valid
if ($result === false) {
    die('Error fetching result: ' . $stmt->error); // Display error if fetching result fails
}

// Fetch the user data
$user = $result->fetch_assoc();

if (!$user) {
    die('No user found with the given ID'); // Handle the case if no user is found
}

// Handle form submission to update user data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];

    // Handle file upload for profile picture
   // Handle file upload for profile picture
$profile = $user['profile']; // Default to existing picture
if (isset($_FILES['profile']) && $_FILES['profile']['error'] == 0) {
    $uploadedFileName = basename($_FILES['profile']['name']); // Extract the file name only
    $profile = $uploadedFileName; // Save only the file name in the database
    move_uploaded_file($_FILES['profile']['tmp_name'], 'image/' . $uploadedFileName); // Save the file to the server
}


    // Update the user's information in the database
    $update_query = "UPDATE users SET name = ?, category = ?, profile = ?, phone_number = ?, address = ?, date_of_birth = ?, gender = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);

    // Check if the SQL query was prepared successfully
    if ($stmt === false) {
        die('MySQL prepare statement error: ' . $conn->error); // Display MySQL error
    }

    $stmt->bind_param('sssssssi', $name, $category, $profile, $phone_number, $address, $date_of_birth, $gender, $user_id);

    if ($stmt->execute()) {
        header("location: edit_info.php");
        exit(); // Make sure to exit after redirect
    } else {
        $error_msg = "Error updating information: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/edit_info.css">
    <link href="image/local_image/logo.png" rel="icon">
    <title>Edit Information</title>
</head>

<body>
    <div class="head">
        <a href="profile.php">
            <button class="return-home">
                <img src="image/icons/back.png" alt="Back" />
            </button>
        </a>
        <h2>Edit Information</h2>
    </div>

    <?php if ($success_msg): ?>
        <div class="success-msg"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="error-msg"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="edit-info">

        <div class="user-info">
            <label for="name">Full Name:</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>" placeholder="Full Name">

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="Accounting & Auditing" <?= $user['category'] == 'Accounting & Auditing' ? 'selected' : '' ?>>Accounting & Auditing</option>
                <option value="Computer Science" <?= $user['category'] == 'Computer Science' ? 'selected' : '' ?>>Computer Science</option>
            </select>

            <label for="profile">Profile</label>
            <input type="file" name="profile" placeholder="Profile">

            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" required value="<?= htmlspecialchars($user['phone_number']) ?>" placeholder="Phone Number">

            <label for="address">Address:</label>
            <input type="text" name="address" required value="<?= htmlspecialchars($user['address']) ?>" placeholder="Address">

            <label for="date_of_birth">Date of Birth:</label>
            <input type="text" name="date_of_birth" required value="<?= htmlspecialchars($user['date_of_birth']) ?>" placeholder="Date of Birth">

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>
        <div class="button">
            <button type="submit">Change</button>
        </div>
    </form>

</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
