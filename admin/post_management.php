<?php
ob_start();
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../config.php';

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

// Initialize $data to avoid undefined variable warnings
$data = mysqli_fetch_assoc($result) ?? null;

if (!$data) {
    // If no data found, set a default profile and name (you can customize this)
    $data = [
        'profile' => 'default-profile.png',  // Default profile image
        'name' => 'Guest',  // Default name
    ];
}

// Close the statement
mysqli_stmt_close($stmt);

// Fetch all posts (public posts) ordered by the newest date first
$query_all_posts = "
    SELECT u.profile, u.name, p.post_id, p.user_post, p.create_at,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) AS comment_count
    FROM users u
    JOIN post p ON u.id = p.user_id
    ORDER BY p.create_at DESC
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/post_management.css"> <!-- Link to your CSS -->
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
<main>
    <div class="header-container">
        <h2>Dashboard</h2>
    </div>

    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="full-post">
                <div class="profile">
                    <?php if (!empty($post['profile'])): ?>
                        <img src="../image/<?php echo htmlspecialchars($post['profile']); ?>" alt="Profile Image" />
                    <?php else: ?>
                        <p>No profile image available.</p>
                    <?php endif; ?>
                    <div class="post-header">
                        <div class="profile-info">
                            <label class="profile-name"><?php echo htmlspecialchars($post['name']); ?></label>
                            <data class="post-date" value="<?php echo htmlspecialchars($post['create_at']); ?>"><?php echo htmlspecialchars($post['create_at']); ?></data>
                        </div>
                        <div class="options">
                            <img src="../image/icons/options.png" alt="Options" class="options-icon" id="optionsIcon">
                            <div class="options-popup" id="optionsPopup">
                                <a href="delete_post.php?post_id=<?php echo htmlspecialchars($post['post_id']); ?>" class="popup-item" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="post">
                    <?php
                    $postPath = '../image/' . htmlspecialchars($post['user_post']);
                    $fileExtension = pathinfo($postPath, PATHINFO_EXTENSION);
                    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    $allowedVideoTypes = ['mp4', 'webm', 'ogg'];

                    if (!empty($post['user_post'])):
                        if (in_array($fileExtension, $allowedImageTypes)): ?>
                            <img src="<?php echo $postPath; ?>" alt="Post Image" />
                        <?php elseif (in_array($fileExtension, $allowedVideoTypes)): ?>
                            <video controls>
                                <source src="<?php echo $postPath; ?>" type="video/<?php echo $fileExtension; ?>">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <p>Unsupported file type.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No post available.</p>
                    <?php endif; ?>
                </div>

                <div class="like-cmnts">
                    <div class="buttons">
                        <a href="comments.php?post_id=<?php echo htmlspecialchars($post['post_id']); ?>">
                            <button class="comments">
                                <img src="../image/icons/coment.png" alt="Comments" />
                            </button>
                        </a>
                    </div>
                    <div class="count">
                        <label for="count-comments" class="count-label">
                            <span id="count-comments"><?php echo htmlspecialchars($post['comment_count']); ?></span> Comments
                        </label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts available.</p>
    <?php endif; ?>
</main>
</body>
</html>
