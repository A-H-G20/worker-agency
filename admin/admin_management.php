<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration file
include('config.php');

// Function to generate a random username
function generateUsername($name) {
    $username = strtolower(str_replace(' ', '_', $name)) . rand(1000, 9999);
    return $username;
}

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $date_of_birth = $_POST['date_of_birth'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing

    // Generate a username automatically
    $username = generateUsername($name);

    // Prepare and execute the insert statement
    $stmt = $mysqli->prepare("INSERT INTO users (name, username, email, phone_number, gender, address, date_of_birth, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin')");
    $stmt->bind_param("ssssssss", $name, $username, $email, $phone_number, $gender, $address, $date_of_birth, $password);

    if ($stmt->execute()) {
        $message = "Admin user added successfully!";
    } else {
        $error = "Error adding admin user: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch admin users
$stmt = $mysqli->prepare("SELECT * FROM users WHERE role = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="css/admin_managment.css">
    <style>
        /* Basic modal styling */
    
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

<main>
    <?php
    // Display messages and errors
    if (isset($message)) {
        echo "<p class='message'>" . htmlspecialchars($message) . "</p>";
    }
    if (isset($error)) {
        echo "<p class='error'>" . htmlspecialchars($error) . "</p>";
    }
    ?>

    <button id="openModalBtn">Add New Admin</button>

    <div id="adminModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Admin:</h2>
            <form action="admin_management.php" method="post">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br>

                <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" required><br>

                <label for="gender">Gender:</label>
                <input type="text" id="gender" name="gender" required><br>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required><br>

                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" required><br>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br>

                <button type="submit" name="add_admin">Add Admin</button>
            </form>
        </div>
    </div>

    <h2>Admin Management:</h2>
    <table>
        <thead>
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
                echo "<td><a href='delete_admin.php?id=" . htmlspecialchars($row['id']) . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No users found</td></tr>";
        }

        // Close the connection
        $stmt->close();
        $mysqli->close();
        ?>
        </tbody>
    </table>
</main>

<script>
    // Get modal elements
    var modal = document.getElementById("adminModal");
    var btn = document.getElementById("openModalBtn");
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>
