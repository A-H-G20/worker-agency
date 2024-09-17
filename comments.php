<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if post_id is passed via GET
if (isset($_GET['post_id'])) {
    $post_id = intval($_GET['post_id']); // Sanitize post_id from the URL
} else {
    echo "Post ID not found.";
    exit();
}

require 'config.php'; // Assumed DB connection

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['comment'])) {
        // Adding a comment
        $user_id = $_SESSION['user_id']; // Retrieve user ID from session
        $comment = trim($_POST['comment']); // Get the comment and sanitize it
        $post_id = intval($_POST['post_id']); // Get the post ID

        if (!empty($comment)) {
            // Insert the comment into the comments table
            $stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, comments, create_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param('iis', $user_id, $post_id, $comment);
            $stmt->execute();
            $stmt->close();

            // Update the comment count in the posts table
            $stmt = $conn->prepare("UPDATE post SET count_cmnts = count_cmnts + 1 WHERE id = ?");
            $stmt->bind_param('i', $post_id);
            $stmt->execute();
            $stmt->close();

            // Redirect back to the post page (or any other desired page)
            header("Location: comments.php?post_id=" . urlencode($post_id));
            exit();
        } else {
            echo "Comment cannot be empty.";
        }
    } elseif (isset($_POST['delete_comment_id'])) {
        // Deleting a comment
        if (isset($_SESSION['user_id'])) {
            $comment_id = intval($_POST['delete_comment_id']);

            // Begin a transaction
            $conn->begin_transaction();

            try {
                // Get the post ID associated with the comment
                $stmt = $conn->prepare("SELECT post_id FROM comments WHERE id = ?");
                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param('i', $comment_id);
                $stmt->execute();
                $stmt->bind_result($post_id);
                $stmt->fetch();
                $stmt->close();

                if ($post_id) {
                    // Delete the comment
                    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
                    if ($stmt === false) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param('i', $comment_id);
                    $stmt->execute();
                    $stmt->close();

                    // Decrease comment count in the posts table
                    $stmt = $conn->prepare("UPDATE post SET count_cmnts = count_cmnts - 1 WHERE id = ?");
                    if ($stmt === false) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param('i', $post_id);
                    $stmt->execute();
                    $stmt->close();

                    // Commit the transaction
                    $conn->commit();
                    echo "Comment deleted successfully.";
                } else {
                    echo "Comment not found.";
                }
            } catch (Exception $e) {
                // Rollback the transaction if something failed
                $conn->rollback();
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "You must be logged in to delete comments.";
        }
        exit(); // Ensure to exit after processing POST requests
    }
}

// Fetch all comments for the given post_id
$stmt = $conn->prepare("
    SELECT u.profile, u.name, c.comments, c.create_at, c.id
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.create_at DESC
");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$comments_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/comments.css">
    <title>Comments Page</title>
</head>
<body>
    <div class="head">
        <a href="dashboard.php">
            <button class="return-home">
                <img src="image/icons/back.png" />
            </button>
        </a>
        <h2>Comments</h2>
    </div>

    <div class="contents">
        <?php while ($comment = $comments_result->fetch_assoc()): ?>
        <div class="comments">
            <div class="profile">
                <img src="image/<?php echo htmlspecialchars($comment['profile'] ?? 'default.png'); ?>" alt="Profile Image" />
                <div>
                    <label><?php echo htmlspecialchars($comment['name'] ?? 'Anonymous'); ?></label>
                    <label><?php echo date('g:i a', strtotime($comment['create_at'])); ?></label>
                </div>
            </div>
            <p class="mesg"><?php echo htmlspecialchars($comment['comments'] ?? 'No comment'); ?></p>
            <img class="options" src="image/icons/options.png" alt="Options" onclick="togglePopup(<?php echo htmlspecialchars($comment['id']); ?>)">
        </div>
        <?php endwhile; ?><br><br><br>
    </div>

    <div class="add-comments">
        <form method="POST" action="">
            <input type="text" name="comment" placeholder="Add your comments" required>
            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">
            <button type="submit">
                <img src="image/icons/send.png" alt="Send">
            </button>
        </form>
    </div>

    <!-- Small popup box with delete option -->
    <div class="popup-box" id="popup-box" style="display:none;">
        <div class="popup-content">
            <form method="POST" action="">
                <input type="hidden" name="delete_comment_id" id="delete-comment-id" value="">
                <button type="submit">Delete</button>
                <button type="button" onclick="togglePopup()">Cancel</button>
            </form>
        </div>
    </div>

    <script src="js/comments.js"></script>
</body>
</html>
