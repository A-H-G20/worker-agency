<?php
ob_start();
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

// Fetch posts only created by the logged-in user ordered by the newest date first
$query_user_posts = "
    SELECT u.profile, u.name, p.post_id, p.user_post, p.create_at,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) AS comment_count
    FROM users u
    JOIN post p ON u.id = p.user_id
    WHERE p.user_id = ?
    ORDER BY p.create_at DESC
";

$stmt_posts = mysqli_prepare($conn, $query_user_posts);
if (!$stmt_posts) {
    echo 'Error preparing the posts query: ' . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt_posts, 'i', $user_id);
mysqli_stmt_execute($stmt_posts);
$result_all_posts = mysqli_stmt_get_result($stmt_posts);
if (!$result_all_posts) {
    echo 'Error fetching posts: ' . mysqli_error($conn);
    exit();
}

$posts = [];
while ($row = mysqli_fetch_assoc($result_all_posts)) {
    $posts[] = $row;
}

// Close the connection
mysqli_stmt_close($stmt_posts);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/profile.css">
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
        <li><a href="profile.php">Profile</a></li>
        <li><a href="contact.php">Chat</a></li>
        <li><a href="logout.php">Logout</a></li>
    </nav>
</header>

<div class="info">
    <img src="profile" alt="">
    <label for="name"><?php echo htmlspecialchars($data['name']); ?></label>
</div>

<?php if (!empty($posts)): ?>
    <?php foreach ($posts as $post): ?>
        <div class="full-post">
            <div class="profile">
                <?php if (!empty($post['profile'])): ?>
                    <img src="image/<?php echo htmlspecialchars($post['profile']); ?>" alt="Profile Image" />
                <?php else: ?>
                    <p>No profile image available.</p>
                <?php endif; ?>
                <div>
                    <label for=""><?php echo htmlspecialchars($post['name']); ?></label>
                    <data value="<?php echo htmlspecialchars($post['create_at']); ?>"><?php echo htmlspecialchars($post['create_at']); ?></data>
                </div>
            </div>

            <div class="post">
                <?php
                $postPath = 'image/' . htmlspecialchars($post['user_post']);
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
                            <img src="image/icons/coment.png" alt="Comments" />
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

</body>
</html>
