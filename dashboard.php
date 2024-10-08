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
<html>

<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="image/local_image/logo.png" rel="icon">
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
            <li><a href="user_friend.php">My Friends</a></li>
            <li><a href="contact.php">chat</a></li>
            <li><a href="profile.php">profile</a></li>
            <li><a href="settings.php">Setting</a></li>
            <li><a href="logout.php">Logout</a></li>
        </nav>
    </header>

    <main>
        <div class="header-container">
            <h2>Dashboard</h2>
            <button class="add">Add post</button>
        </div>

        <div class="add-post-container">
            <div class="add-post" id="add-post-form" style="display: none;">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file">Post</label>
                        <input type="file" id="file" name="file" required />
                    </div>
                    <div class="form-group">
                        <button type="submit">Submit</button>
                        <button type="button" class="cancel">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <script src="js/add-post.js"></script>

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
    </main>
</body>

</html>
<?php
$error_message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        // Check for specific file upload errors
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message = "Error: File size exceeds the maximum limit.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message = "Error: The uploaded file was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_message = "Error: No file was uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message = "Error: Missing a temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_message = "Error: Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error_message = "Error: A PHP extension stopped the file upload.";
                    break;
                default:
                    $error_message = "Error: Unknown error occurred during file upload.";
            }
            // Store error and exit without displaying output immediately
        } else {
            $fileTmpPath = $_FILES['file']['tmp_name'];
            $fileName = basename($_FILES['file']['name']);
            $uploadDir = 'image/';
            $filePath = $uploadDir . $fileName;

            // Check if directory exists, if not create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Validate file type
            $allowedFileTypes = [
                'image/jpeg',   // JPEG images
                'image/png',    // PNG images
                'image/gif',    // GIF images
                'video/mp4',    // MP4 videos
                'video/webm',   // WebM videos
                'video/ogg'     // Ogg videos
            ];
            $fileType = mime_content_type($fileTmpPath);

            if (in_array($fileType, $allowedFileTypes)) {
                // Move uploaded file to the target directory
                if (move_uploaded_file($fileTmpPath, $filePath)) {
                    // Insert data into the database
                    $insertDate = date('Y-m-d H:i:s'); // Current date and time
                    require 'config.php';  // Ensure the DB connection is available again
                    $query = "INSERT INTO post (user_id, user_post, create_at) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $fileName, $insertDate);
                        if (mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_close($stmt);
                            mysqli_close($conn);

                            // Redirect to avoid form resubmission
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $error_message = "Error: Could not execute the query. " . mysqli_error($conn);
                        }
                    } else {
                        $error_message = "Error: Could not prepare the query. " . mysqli_error($conn);
                    }
                } else {
                    $error_message = "Error: Could not move the uploaded file.";
                }
            } else {
                $error_message = "Error: Invalid file type. Please upload a JPEG, PNG, GIF image, or MP4, WebM, Ogg video.";
            }
        }
    } else {
        $error_message = "Error: No file uploaded.";
    }
}

// After the script is executed, display any error message
if ($error_message) {
    echo $error_message;
}
ob_end_flush();
?>