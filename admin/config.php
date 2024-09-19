<?php
// config.php
$host = 'localhost'; // or your host
$user = 'root'; // your database username
$password = ''; // your database password
$dbname = 'agency'; // your database name

// Create a new mysqli instance
$mysqli = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
