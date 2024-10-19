<?php
session_start();

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the Users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a 6-digit OTP code
        $code = rand(100000, 999999);
        $_SESSION['reset_code'] = $code;
        $_SESSION['reset_email'] = $email;

        // Set OTP expiration time (e.g., 15 minutes from now)
        $expires_at = date("Y-m-d H:i:s", strtotime('+15 minutes'));

        // Insert the OTP code into the otp_codes table
        $insert_stmt = $conn->prepare("INSERT INTO otp_codes (email, otp_code, expires_at) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sis", $email, $code, $expires_at);
        $insert_stmt->execute();
        $insert_stmt->close();

        // Send the OTP code to the user's email using PHPMailer
        $mail = new PHPMailer(true); // Passing `true` enables exceptions

        try {
            //Server settings
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'grixia400@gmail.com';                  // SMTP username (your Gmail address)
            $mail->Password   = 'megp jgan oswr rzng';                   // SMTP password (your Gmail password or App Password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          // Enable TLS encryption
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('your_email@gmail.com', 'Your Name');         // Sender email
            $mail->addAddress($email);                                  // Add a recipient

            // Content
            $mail->isHTML(true);                                        // Set email format to HTML
            $mail->Subject = 'Password Reset Code';
            $mail->Body    = "Your password reset code is: <strong>" . $code . "</strong>";

            $mail->send();
            echo "A reset code has been sent to your email address.";
            header("Location: reset_password.php");
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <h1>Forgot Password</h1>
    <form action="forgot_password.php" method="POST">
        <label for="email">Enter your registered email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <input type="submit" value="Send Reset Code">
    </form>
</body>
</html>
