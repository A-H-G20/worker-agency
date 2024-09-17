<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all users except the logged-in user
require 'config.php';

$user_id = $_SESSION['user_id'];

// Fetch all users except the logged-in user
$query_all_users = "
    SELECT id, profile, name
    FROM users
    WHERE id != ?
";
$stmt = $conn->prepare($query_all_users);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result_all_users = $stmt->get_result();
$users = [];
while ($row = $result_all_users->fetch_assoc()) {
    $users[] = $row;
}

// Fetch the list of friends for the logged-in user
$query_friends = "
    SELECT friend_id
    FROM friends
    WHERE user_id = ?
";
$stmt = $conn->prepare($query_friends);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result_friends = $stmt->get_result();
$friends = [];
while ($row = $result_friends->fetch_assoc()) {
    $friends[] = $row['friend_id'];
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/friends.css">
</head>

<body>
    <header class="sidebar">
        <nav>
            <div class="logo">
                <?php if (!empty($data['profile'])): ?>
                    <!-- here profile image-->
                <?php else: ?>
                    <p>No profile image available.</p>
                <?php endif; ?>
            </div>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="profile.php">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
        </nav>
    </header>

    <main>
        <div class="friends-container">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <form class="friends" action="add_friends.php" method="post">
                        <div class="profile-image">
                            <?php if (!empty($user['profile'])): ?>
                                <img src="image/<?php echo htmlspecialchars($user['profile']); ?>" alt="Profile Image">
                            <?php else: ?>
                                <img src="image/default-profile.png" alt="Default Profile Image">
                            <?php endif; ?>
                        </div>
                        <label for="name"><?php echo htmlspecialchars($user['name']); ?></label>
                        <input type="hidden" name="friend_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                        <button type="submit" <?php echo in_array($user['id'], $friends) ? 'disabled' : ''; ?>>
                            <?php echo in_array($user['id'], $friends) ? 'Added' : 'Add'; ?>
                        </button>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No users available.</p>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>


<?php


require 'config.php'; // Ensure database connection is established

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Retrieve user ID from session

        // Retrieve the friend ID from POST data
        $friend_id = isset($_POST['friend_id']) ? $_POST['friend_id'] : null;

        if ($friend_id) {
            // Insert the friend request into the friends table
            $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id, date) VALUES (?, ?, NOW())");
            $stmt->bind_param('ii', $user_id, $friend_id);
            $stmt->execute();

            // Optionally, you might want to update some count or other related data in the users table
            // Example: Increase friend count (if applicable)
            $stmt = $conn->prepare("UPDATE users SET count_frnd= count_frnd + 1 WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();

            // Redirect back to the friends page (or any other desired page)
            echo "The friends added";
            header("Location: add_friends.php");
            exit();
        } else {
            echo "Friend ID cannot be empty.";
        }
    } else {
        echo "You must be logged in to add friends.";
    }
    exit(); // Make sure to exit after processing POST requests
}
?>