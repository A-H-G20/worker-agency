<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$user_id = $_SESSION['user_id'];

// Fetch user and post details from the database
$query = "
    SELECT u.profile, u.name, p.user_post, p.create_at
    FROM users u
    JOIN post p ON u.id = p.user_id
    WHERE u.id = ?
";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    echo 'Error preparing the query: ' . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    echo 'Error fetching posts: ' . mysqli_error($conn);
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
    SELECT u.profile, u.name, p.user_post, p.create_at
    FROM users u
    JOIN post p ON u.id = p.user_id
";

$result_all_posts = mysqli_query($conn, $query_all_posts);
if (!$result_all_posts) {
    echo 'Error fetching data: ' . mysqli_error($conn);
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
            <li><a href="profile.php">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
           
        </nav>
       
    </header>

    <main>
        <div class="header-container">
            <h2>Dashboard</h2>
            <button class="add">Add post</button>
        </div>
                 
 <!--   <div class="story">
        <div class="img">
            <img src="image/logo.png" alt="">
            <label for="">user1</label>
        </div>
        <div class="img">
            <img src="image/logo.png" alt="">
            <label for="">user2</label>
        </div>
        <div class="img">
            <img src="image/logo.png" alt="">
            <label for="">user3</label>
        </div>
        <div class="img">
            <img src="image/logo.png" alt="">
            <label for="">user4</label>
        </div>
    </div>-->

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
</div><script src="js/add-post.js"></script>



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
                        <label for=""><?php echo htmlspecialchars($post['name']); ?></label> <!-- Name from database -->
                        <data value="<?php echo htmlspecialchars($post['create_at']); ?>"><?php echo htmlspecialchars($post['create_at']); ?></data> <!-- Date from database -->
                    </div>
                </div>

                <div class="post">
                    <?php
                    // Check file type and display accordingly
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
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts available.</p>
    <?php endif; ?>
    </main>
</body>

</html>

<?php
// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        // Check for specific file upload errors
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo "Error: File size exceeds the maximum limit.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "Error: The uploaded file was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo "Error: No file was uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "Error: Missing a temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "Error: Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo "Error: A PHP extension stopped the file upload.";
                    break;
                default:
                    echo "Error: Unknown error occurred during file upload.";
            }
            exit();
        }

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
                        echo "Error: Could not execute the query. " . mysqli_error($conn);
                    }
                } else {
                    echo "Error: Could not prepare the query. " . mysqli_error($conn);
                }
            } else {
                echo "Error: Could not move the uploaded file.";
            }
        } else {
            echo "Error: Invalid file type. Please upload a JPEG, PNG, GIF image, or MP4, WebM, Ogg video.";
        }
    } else {
        echo "Error: No file uploaded.";
    }
}

