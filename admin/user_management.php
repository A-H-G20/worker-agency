<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .sidebar {
            background-color: #f8f9fa;
            padding: 10px;
        }
        .sidebar nav li {
            list-style-type: none;
        }
        .sidebar nav a {
            text-decoration: none;
            color: #007bff;
        }
        .delete-btn {
            color: red;
            cursor: pointer;
            text-decoration: none;
        }
        .delete-btn:hover {
            text-decoration: underline;
        }
        .message {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<header class="sidebar">
    <nav>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="add_friends.php">Add Friends</a></li>
        <li><a href="user_friend.php">My Friends</a></li>
        <li><a href="contact.php">Chat</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="settings.php">Setting</a></li>
        <li><a href="logout.php">Logout</a></li>
    </nav>
</header>

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
        <tr>
            <th>ID</th>
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
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
        echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
        echo "<td>" . htmlspecialchars($row['date_of_birth']) . "</td>";
        // Add a confirmation dialog to prevent accidental deletions
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

</body>
</html>
