<?php
// Include the database connection
include 'db.php';

// Check if the logged-in user is an admin
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    die("Access denied. Admins only.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $specialization_name = $_POST['specialization_name'];
    $availability_schedule = $_POST['availability_schedule'];
    $years_of_experience = $_POST['years_of_experience'];

    // Check if email already exists
    $email_check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $email_check->bind_param("s", $email);
    $email_check->execute();
    $result = $email_check->get_result();

    if ($result->num_rows > 0) {
        echo "Email is already registered!";
    } else {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $doctor_id = $conn->insert_id;

            // Insert into doctors table without specialization ID
            $stmt_doctor = $conn->prepare("INSERT INTO doctors (doctor_id, name, email, password, specialization_name, availability_schedule, years_of_experience) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_doctor->bind_param("isssssi", $doctor_id, $name, $email, $password, $specialization_name, $availability_schedule, $years_of_experience);

            if ($stmt_doctor->execute()) {
                echo "Doctor registered successfully!";
            } else {
                echo "Error in registering doctor: " . $stmt_doctor->error;
            }

            $stmt_doctor->close();
        } else {
            echo "Error in user registration.";
        }

        $stmt->close();
    }

    $email_check->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Doctor</title>
</head>
<body>
    <h2>Register a New Doctor</h2>
    <form action="admin_register_doctor.php" method="POST">
        <label for="name">Doctor's Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="specialization_name">Specialization:</label>
        <input type="text" id="specialization_name" name="specialization_name" required><br><br>

        <label for="availability_schedule">Availability Schedule:</label>
        <input type="text" id="availability_schedule" name="availability_schedule" required><br><br>

        <label for="years_of_experience">Years of Experience:</label>
        <input type="number" id="years_of_experience" name="years_of_experience" required><br><br>

        <input type="submit" value="Register Doctor">
    </form>
</body>
</html>
