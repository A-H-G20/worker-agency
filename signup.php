
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

function validatePassword($password)
{
    // Check password length
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    // Check for uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must include at least one uppercase letter.";
    }

    // Check for lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must include at least one lowercase letter.";
    }

    // Check for digit
    if (!preg_match('/\d/', $password)) {
        return "Password must include at least one digit.";
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $category = $_POST['category'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $date_of_birth = $_POST['date_of_birth'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];
    $profile = 'images.png'; // Set default profile image
    $role = 'user';
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
            // Validate password
            $passwordValidation = validatePassword($password);
            if ($passwordValidation !== true) {
                $error = $passwordValidation;
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

                    // Insert the new user into the database, including the profile image
                    $stmt = $conn->prepare("INSERT INTO users (name, username, email, gender, category, address, date_of_birth, phone_number, password, verification_code, profile, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user')");
                    $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
                    $stmt->bind_param("sssssssssss", $name, $username, $email, $gender, $category, $address, $date_of_birth, $phone_number, $hashed_password, $verification_code, $profile);

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
                    <label for="name">Full Name:*</label>
                    <input type="text" id="name" name="name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Accounting & Auditing">Accounting & Auditing</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Acting">Acting</option>
                        <option value="Administrative Roles">Administrative Roles</option>
                        <option value="Advertising">Advertising</option>
                        <option value="Aerospace & Aeronautics">Aerospace & Aeronautics</option>
                        <option value="Air Traffic Control">Air Traffic Control</option>
                        <option value="Architecture">Architecture</option>
                        <option value="Banking & Finance">Banking & Finance</option>
                        <option value="Bartenders">Bartenders</option>
                        <option value="Beauty Industry">Beauty Industry</option>
                        <option value="Biologists">Biologists</option>
                        <option value="Biotechnology">Biotechnology</option>
                        <option value="Botanists">Botanists</option>
                        <option value="Business & Operations Management">Business & Operations Management</option>
                        <option value="Business Analysts">Business Analysts</option>
                        <option value="Business Development">Business Development</option>
                        <option value="Carpentry">Carpentry</option>
                        <option value="Chef & Culinary Arts">Chef & Culinary Arts</option>
                        <option value="Chemical Industry">Chemical Industry</option>
                        <option value="Childcare">Childcare</option>
                        <option value="Commercial Artist">Commercial Artist</option>
                        <option value="Community Services">Community Services</option>
                        <option value="Compliance & Regulatory Affairs">Compliance & Regulatory Affairs</option>
                        <option value="Construction Industry">Construction Industry</option>
                        <option value="Consulting">Consulting</option>
                        <option value="Counseling">Counseling</option>
                        <option value="Crafts">Crafts</option>
                        <option value="Cybersecurity">Cybersecurity</option>
                        <option value="Dentistry">Dentistry</option>
                        <option value="Diplomacy">Diplomacy</option>
                        <option value="Electrician">Electrician</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Event Planning">Event Planning</option>
                        <option value="Fashion Industry">Fashion Industry</option>
                        <option value="Fine Art">Fine Art</option>
                        <option value="Fitness & Wellness">Fitness & Wellness</option>
                        <option value="Forestry">Forestry</option>
                        <option value="Government Worker">Government Worker</option>
                        <option value="Hotel Industry">Hotel Industry</option>
                        <option value="Insurance Industry">Insurance Industry</option>
                        <option value="Customer Service">Customer Service</option>
                        <option value="Data Science">Data Science</option>
                        <option value="Design">Design</option>
                        <option value="Education & Training">Education & Training</option>
                        <option value="Emergency Services">Emergency Services</option>
                        <option value="Environmental Science">Environmental Science</option>
                        <option value="Farming & Agricultural Science">Farming & Agricultural Science</option>
                        <option value="Film Industry">Film Industry</option>
                        <option value="Fishing">Fishing</option>
                        <option value="Flight Attendants">Flight Attendants</option>
                        <option value="Game Developers">Game Developers</option>
                        <option value="Hospitality Industry">Hospitality Industry</option>
                        <option value="Human Resources">Human Resources</option>
                        <option value="Investment Banking">Investment Banking</option>
                        <option value="Journalism">Journalism</option>
                        <option value="Landscaping">Landscaping</option>
                        <option value="Librarians">Librarians</option>
                        <option value="Manufacturing Industry">Manufacturing Industry</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Mathematicians">Mathematicians</option>
                        <option value="Medicine">Medicine</option>
                        <option value="Culture Industry">Culture Industry</option>
                        <option value="Nonprofit Management">Nonprofit Management</option>
                        <option value="Occupational Health & Safety">Occupational Health & Safety</option>
                        <option value="Personal Services">Personal Services</option>
                        <option value="Pharmaceutical Industry">Pharmaceutical Industry</option>
                        <option value="Pilots">Pilots</option>
                        <option value="Laboratory Technicians">Laboratory Technicians</option>
                        <option value="Law">Law</option>
                        <option value="Maintenance & Repair Technicians">Maintenance & Repair Technicians</option>
                        <option value="Market Research">Market Research</option>
                        <option value="Materials Science">Materials Science</option>
                        <option value="Media Industry">Media Industry</option>
                        <option value="Museum Curator">Museum Curator</option>
                        <option value="Music Industry">Music Industry</option>
                        <option value="Nursing">Nursing</option>
                        <option value="Office Worker">Office Worker</option>
                        <option value="Pet Services & Animal Care">Pet Services & Animal Care</option>
                        <option value="Physicists">Physicists</option>
                        <option value="Professional Services">Professional Services</option>
                        <option value="project_management">Project management</option>
                        <option value="psychiatry">Psychiatry</option>
                        <option value="public_relations">Public relations & communications</option>
                        <option value="quality_assurance">Quality assurance</option>
                        <option value="real_estate_agents">Real estate agents</option>
                        <option value="research">Research</option>
                        <option value="restaurant_industry">Restaurant industry</option>
                        <option value="retail_industry">Retail industry</option>
                        <option value="risk_management">Risk management</option>
                        <option value="sales">Sales</option>
                        <option value="science">Science</option>
                        <option value="social_work">Social work</option>
                        <option value="software_developer">Software developer</option>
                        <option value="sports_industry">Sports industry</option>
                        <option value="teaching">Teaching</option>
                        <option value="technology_r&d">Technology research & development</option>
                        <option value="technology_specialists">Technology specialists</option>
                        <option value="technology_operations">Technology operations</option>
                        <option value="trades">Trades</option>
                        <option value="transportation_logistics">Transportation & logistics</option>
                        <option value="travel_industry">Travel industry</option>
                        <option value="veterinarians">Veterinarians</option>
                        <option value="wildlife_conservation">Wildlife conservation</option>
                        <option value="zoologists">Zoologists</option>
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
                    <input type="password" id="password" name="password" required placeholder="Must be at least 8 char 1 capital 1 small letter 1 digits ">
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