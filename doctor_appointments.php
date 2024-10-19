<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    die("Access denied. Doctors only.");
}

// Include the database connection
include 'db.php';

$doctor_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_id']) && isset($_POST['action'])) {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];

    // Update the status based on the doctor's action
    if ($action === 'accept') {
        $status = 'accepted';
    } elseif ($action === 'decline') {
        $status = 'declined';
    }

    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
    $stmt->bind_param("si", $status, $appointment_id);

    if ($stmt->execute()) {
        echo "Appointment " . ucfirst($status) . " successfully!";
    } else {
        echo "Error updating appointment: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch appointments for the doctor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_date'])) {
    $appointment_date = $_POST['appointment_date'];

    // Fetch all appointments for the selected date, ordered by time
    $stmt = $conn->prepare("SELECT appointments.appointment_id, users.name, appointments.time, appointments.status FROM appointments 
                            JOIN users ON appointments.patient_id = users.id 
                            WHERE doctor_id = ? AND date = ? 
                            ORDER BY time ASC");
    $stmt->bind_param("is", $doctor_id, $appointment_date);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Appointments for $appointment_date</h2>";
    echo "<table border='1'>
        <tr>
            <th>Patient Name</th>
            <th>Appointment Time</th>
            <th>Status</th>
            <th>Action</th>
        </tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['time']}</td>
            <td>{$row['status']}</td>
            <td>";

    // Display Accept and Decline buttons only for Pending appointments
    if ($row['status'] == 'pending') {
        echo "<form action='doctor_appointments.php' method='POST' style='display:inline-block;'>
                <input type='hidden' name='appointment_id' value='{$row['appointment_id']}'>
                <button type='submit' name='action' value='accept'>Accept</button>
              </form>";
        echo "<form action='doctor_appointments.php' method='POST' style='display:inline-block;'>
                <input type='hidden' name='appointment_id' value='{$row['appointment_id']}'>
                <button type='submit' name='action' value='decline'>Decline</button>
              </form>";
    } else {
        // If the appointment is not pending, just display its status
        echo ucfirst($row['status']);
    }

    echo "</td>
          </tr>";
}
echo "</table>";

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
</head>
<body>
    <h1>Doctor Dashboard</h1>

    <h2>Check Appointments by Date</h2>
    <form action="doctor_appointments.php" method="POST">
        <label for="appointment_date">Select Date:</label>
        <input type="date" id="appointment_date" name="appointment_date" required><br><br>

        <input type="submit" value="Check Appointments">
    </form>

    <a href="logout.php">Log Out</a>


    <?php include 'navbar.html'; ?>
</body>
</html>
