<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    die("Access denied. Doctors only.");
}

// Include the database connection
include 'db.php';

date_default_timezone_set('Asia/Manila');

$doctor_id = $_SESSION['user_id'];
$doctor_info = [];
$availability_info = [];

// Fetch doctor's availability information
$stmt_availability = $conn->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ?");
$stmt_availability->bind_param("i", $doctor_id);
$stmt_availability->execute();
$result_availability = $stmt_availability->get_result();

if ($result_availability->num_rows > 0) {
    $availability_info = $result_availability->fetch_all(MYSQLI_ASSOC);
}

$stmt_availability->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h2>Current Availability</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Max Patients</th>
                <th>Start Time</th>
                <th>End Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($availability_info)): ?>
                <?php foreach ($availability_info as $availability): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($availability['cater_date']); ?></td>
                        <td><?php echo htmlspecialchars($availability['max_patients']); ?></td>
                        <td><?php echo htmlspecialchars($availability['start_time']); ?></td>
                        <td><?php echo htmlspecialchars($availability['end_time']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No availability set.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>