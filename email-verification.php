<link rel="stylesheet" href="css/verify.css">
<link href="image/local_image/logo.png" rel="icon">
<div class="container">
    <img src="image/local_image/logo.png" alt="Logo" onclick="window.location.href='index.php';">
    <form method="POST">
        <input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
        <input type="text" name="verification_code" placeholder="Enter verification code" required />
        <input type="submit" name="verify_email" value="Verify Email">
    </form>
</div>

<?php
include('config.php');
if (isset($_POST["verify_email"])) {
    $email = $_POST["email"];
    $verification_code = $_POST["verification_code"];
    $verified = 1; // Assuming you want to set verified as 1
    $sql = "UPDATE users SET email_verified_at = NOW(), verified = '" . $verified . "' WHERE email = '" . $email . "' AND verification_code = '" . $verification_code . "'";
    $result  = mysqli_query($conn, $sql);
    if (mysqli_affected_rows($conn) == 0) {
        die("Verification code failed.");
    }
    header("Location: login.php");
    exit();
}
?>