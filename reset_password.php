<?php
session_start();
include 'db.php';

if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_email'])) {
    die("Access denied. Please request a password reset.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['code'];
    $new_password = $_POST['new_password'];
    $email = $_SESSION['reset_email'];
    
    // Check if the entered code matches the generated code
    if ($entered_code == $_SESSION['reset_code']) {
        // Update the password in the database
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            echo "Your password has been updated successfully!";
            // Clear session variables
            unset($_SESSION['reset_code']);
            unset($_SESSION['reset_email']);
            header("Location: login.php"); // Redirect to the login page
            exit();
        } else {
            echo "Error updating password: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid reset code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Password</h1>
    <form action="reset_password.php" method="POST">
        <label for="code">Enter the reset code:</label>
        <input type="text" id="code" name="code" required><br><br>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <input type="submit" value="Reset Password">
    </form>
</body>
</html>
