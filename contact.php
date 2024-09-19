<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$user_id = $_SESSION['user_id'];

// Fetch user details for the sidebar
$query_user = "
    SELECT profile, name
    FROM users
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $query_user);
if (!$stmt) {
    echo 'Error preparing the query: ' . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    echo 'Error fetching user details: ' . mysqli_error($conn);
    exit();
}

// Fetch data from the result set
$data = mysqli_fetch_assoc($result);
if (!$data) {
    echo 'No data found.';
    $data = [];
}

// Close the statement
mysqli_stmt_close($stmt);

// Fetch friends of the logged-in user
$query_friends = "
    SELECT u.id, u.name, u.profile
    FROM users u
    JOIN friends f ON u.id = f.friend_id
    WHERE f.user_id = ?
";

$stmt_friends = mysqli_prepare($conn, $query_friends);
if (!$stmt_friends) {
    echo 'Error preparing the query: ' . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt_friends, 'i', $user_id);
mysqli_stmt_execute($stmt_friends);
$result_friends = mysqli_stmt_get_result($stmt_friends);
if (!$result_friends) {
    echo 'Error fetching friends: ' . mysqli_error($conn);
    exit();
}

$friends = [];
while ($row = mysqli_fetch_assoc($result_friends)) {
    $friends[] = $row;
}

// Close the statement and connection
mysqli_stmt_close($stmt_friends);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/contact.css">
    <title>Friends List</title>
</head>

<body>
    <header class="sidebar">
        <nav>
            <div class="logo">
                <?php if (!empty($data['profile'])): ?>
                    <img src="image/<?php echo htmlspecialchars($data['profile']); ?>" alt="Profile Image" />
                <?php else: ?>
                    <p>No profile image available.</p>
                <?php endif; ?>
            </div>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="add_friends.php">Add Friends</a></li>
            <li><a href="contact.php">chat</a></li>
            <li><a href="profile.php">profile</a></li>
            <li><a href="settings.php">Setting</a></li>
            <li><a href="logout.php">Logout</a></li>
        </nav>
    </header>

    <div class="head">
        <h2>Friends List</h2>
    </div>

    <?php if (!empty($friends)): ?>
        <div class="users">
            <?php foreach ($friends as $friend): ?>
                <div class="user">
                    <a href="chat_with_user.php?user_id=<?php echo htmlspecialchars($friend['id']); ?>">
                        <?php if (!empty($friend['profile'])): ?>
                            <img src="image/<?php echo htmlspecialchars($friend['profile']); ?>" alt="Profile Image">
                        <?php else: ?>
                            <img src="default_profile.png" alt="Default Profile">
                        <?php endif; ?>
                        <label for="name"><?php echo htmlspecialchars($friend['name']); ?></label>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No friends found.</p>
    <?php endif; ?>

</body>

</html>