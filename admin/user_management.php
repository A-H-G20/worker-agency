<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
  <link rel="stylesheet" href="css/user_management.css">
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
<?php
// Display messages
if (isset($_GET['message'])) {
    echo "<p class='message'>" . htmlspecialchars($_GET['message']) . "</p>";
}
if (isset($_GET['error'])) {
    echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
}
?>

<table>
    <thead>
        <h2>User Management:</h2>
        <tr>
           
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Gender</th>
            <th>Address</th>
            <th>Date of Birth</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
    // Include database configuration file
    include('config.php');

    // Query to fetch users with the role 'user'
    $sql = "SELECT * FROM users WHERE role = 'user'";
    $result = $mysqli->query($sql);

    // Check if the query was successful
    if (!$result) {
        die("Query failed: " . $mysqli->error);
    }

    // Check if there are results
    if ($result->num_rows > 0) {
        // Output data for each row
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
          
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date_of_birth']) . "</td>";
            echo "<td><a href='delete_user.php?id=" . htmlspecialchars($row['id']) . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='9'>No users found</td></tr>";
    }

    // Close the connection
    $mysqli->close();
    ?>
    </tbody>
</table>
</main>

</body>
</html>
