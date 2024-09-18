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

// Fetch all posts (public posts)
$query_all_posts = "
    SELECT u.profile, u.name, p.post_id, p.user_post, p.create_at,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) AS comment_count
    FROM users u
    JOIN post p ON u.id = p.user_id
";

$result_all_posts = mysqli_query($conn, $query_all_posts);
if (!$result_all_posts) {
    echo 'Error fetching posts: ' . mysqli_error($conn);
    exit();
}

$posts = [];
while ($row = mysqli_fetch_assoc($result_all_posts)) {
    $posts[] = $row;
}

// Close the connection
mysqli_close($conn);


// Include your database connection here
include 'config.php'; // Make sure to replace with your actual connection file

// Get the logged-in user's ID from the session
$current_user_id = $_SESSION['user_id'];

// Query to fetch all users except the current logged-in user
$sql = "SELECT id, name, profile FROM users WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id); // 'i' means integer
$stmt->execute();
$result = $stmt->get_result();
echo '<div class="head">
        <h2>Chatting Page</h2>
    </div>';
// Check if there are any users found
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Output the user details in HTML format
        echo '<div class="users">';
        echo '<a href="chat_with_user.php?user_id=' . htmlspecialchars($row['id']) . '">';
        if (!empty($row['profile'])) {
            echo '<img src="image/' . htmlspecialchars($row['profile']) . '" alt="Profile Image">';
        } else {
            echo '<img src="default_profile.png" alt="Default Profile">';
        }
        echo '<label for="name">' . htmlspecialchars($row['name']) . '</label>';
        echo '</a>';
        echo '</div>';
    }
} else {
    echo "<p>No other users found.</p>";
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/contact.css">
    <title>Document</title>
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
            <li><a href="profile.php">profile</a></li>
            <li><a href="chat.php">chat</a></li>
            <li><a href="logout.php">Logout</a></li>
        </nav>
    </header>
        
        
</body>
</html>
