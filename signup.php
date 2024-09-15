<?php
session_start();
require 'config.php'; // Include the DB connection

function generateUsername($name)
{
    // Generate a simple username by appending a random number to the name
    return strtolower($name) . rand(100, 999);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $category = $_POST['category'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $date_of_birth = $_POST['date_of_birth'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '22130479@students.liu.edu.lb'; // Your Gmail address
        $mail->Password = 'jqujaycttktvlevd'; // Your Gmail password or App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Basic validation
        if (empty($name) || empty($email) || empty($gender) || empty($category) || empty($date_of_birth) || empty($address) || empty($phone_number) || empty($password)) {
            $error = "All fields are required!";
        } else {
            // Check if the email or phone number already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone_number = ?");
            $stmt->bind_param("ss", $email, $phone_number);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Email or phone number already exists
                $error = "This email or phone number is already registered!";
            } else {
                // Generate a random username
                $username = generateUsername($name);

                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert the new user into the database
                $stmt = $conn->prepare("INSERT INTO users (name, username, email, gender, category, address, date_of_birth, phone_number, password, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
                $stmt->bind_param("ssssssssss", $name, $username, $email, $gender, $category, $address, $date_of_birth, $phone_number, $hashed_password, $verification_code);

                if ($stmt->execute()) {
                    // Set up the email content
                    $mail->setFrom('your_email@gmail.com', 'Worker Agency Administrator');
                    $mail->addAddress($email, $name);
                    $mail->isHTML(true);
                    $mail->Subject = 'Email verification';
                    $mail->Body = '<p>Dear <b>' . htmlspecialchars($name) . '</b>,</p>
                                   <p>Your verification code is: <b style="font-size: 15px;">' . htmlspecialchars($verification_code) . '</b></p>
                                   <p>Your username is: <b style="font-size: 15px;">' . htmlspecialchars($username) . '</b></p>
                                   <p>Regards,</p><p>Worker Agency Administrator</p>';

                    // Send email
                    $mail->send();

                    // Redirect to email verification page
                    header("Location: email-verification.php?email=" . urlencode($email));
                    exit();
                } else {
                    $error = "Error: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    } catch (mysqli_sql_exception $e) {
        echo "Database Error: {$e->getMessage()}";
    }
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/signup.css">
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="image/logo.png" alt="Logo">
        </div>
        <h1>Register Now</h1>
        <form method="POST" action="">
            <div class="form-container">
                <div class="right-side">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>

                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="left-side">
                    <label for="date_of_birth">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>

                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>

                    <label for="phone_number">Phone Number:</label>
                    <input type="text" id="phone_number" name="phone_number" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
            </div>

            <button type="submit">Register</button>
            <div id="log">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </form>
    </div>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
</body>

</html>