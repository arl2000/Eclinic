<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the OTP code and new password from the form
    $otp_code = $_POST['otp_code'];  // Do NOT initialize this as an empty array
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $email = $_SESSION['reset_email'];

    // Check if the OTP is correct and not expired
    $stmt = $conn->prepare("SELECT * FROM otp_codes WHERE email = ? AND otp_code = ? AND expires_at > NOW()");
    $stmt->bind_param("si", $email, $otp_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // OTP is correct, update the user's password
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $new_password, $email);
        $update_stmt->execute();
        $update_stmt->close();

        echo "Your password has been reset successfully.";
        // Optionally, delete the OTP entry after successful password reset
        $delete_stmt = $conn->prepare("DELETE FROM otp_codes WHERE email = ?");
        $delete_stmt->bind_param("s", $email);
        $delete_stmt->execute();
        $delete_stmt->close();

        header("Location: login.php");
        exit();
    } else {
        echo "Invalid or expired OTP.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
</head>
<body>
    <h1>Verify OTP</h1>
    <form action="verify_otp.php" method="POST">
        <label for="otp">Enter OTP:</label>
        <input type="text" id="otp" name="otp_code" required><br><br>
        
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <input type="submit" value="Verify and Reset Password">
    </form>
</body>
</html>
