<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    die("Access denied. Admins only.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome to the Admin Dashboard</h1>
    <p>You are logged in as an admin.</p>
    <a href="admin_register_doctor.php">Register Doctor</a><br>
    <a href="logout.php">Log Out</a>
</body>
</html>
