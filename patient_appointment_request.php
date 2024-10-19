<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'patient') {
    die("Access denied. Patients only.");
}

// Include the database connection
include 'db.php';

date_default_timezone_set('Asia/Manila');
echo date('h:i:s A');

$doctors = [];
$stmt = $conn->prepare("SELECT doctor_id, name FROM doctors");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['date'];
    $patient_id = $_SESSION['user_id'];
    
    // Get the current time from the server as reference
    $appointment_time = date("H:i:s");

    // Check if the patient already has an appointment on the selected date
    $stmt_check_appointment = $conn->prepare("SELECT * FROM appointments WHERE patient_id = ? AND date = ?");
    $stmt_check_appointment->bind_param("is", $patient_id, $appointment_date);
    $stmt_check_appointment->execute();
    $result_check_appointment = $stmt_check_appointment->get_result();

    if ($result_check_appointment->num_rows > 0) {
        // If the patient already has an appointment for the selected date
        echo "You already have an appointment for this date. Please choose another date.";
    } else {

    // Check how many patients have already booked for the selected date
    $stmt_check = $conn->prepare("SELECT COUNT(*) as current_patients FROM appointments WHERE doctor_id = ? AND date = ?");
    $stmt_check->bind_param("is", $doctor_id, $appointment_date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $current_patients = $row_check['current_patients'];

    // Fetch the doctor's maximum patient limit for the selected date
    $stmt_limit = $conn->prepare("SELECT max_patients FROM doctor_availability WHERE doctor_id = ? AND cater_date = ?");
    $stmt_limit->bind_param("is", $doctor_id, $appointment_date);
    $stmt_limit->execute();
    $result_limit = $stmt_limit->get_result();
    $row_limit = $result_limit->fetch_assoc();
    $max_patients = $row_limit['max_patients'];

    if ($current_patients < $max_patients) {
        // If the doctor has not reached the limit, book the appointment
        $stmt_appointment = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, date, time, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt_appointment->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);

        if ($stmt_appointment->execute()) {
            echo "Appointment booked successfully!";
            header("Location: patient_dashboard.php");
            exit();
        } else {
            echo "Error booking appointment: " . $stmt_appointment->error;
        }

        $stmt_appointment->close();
    } else {
        // If the doctor has reached the limit
        echo "Sorry, the doctor has already reached the maximum number of patients for the selected date.";
    }

    $stmt_check->close();
    $stmt_limit->close();
}
$stmt_check_appointment->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Appointment</title>
</head>
<body>
    <h1>Request an Appointment</h1>
    <form action="patient_appointment_request.php" method="POST">
        <label for="doctor_id">Select Doctor:</label>
        <select id="doctor_id" name="doctor_id" required>
            <option value="">--Select Doctor--</option>
            <?php foreach ($doctors as $doctor): ?>
                <option value="<?php echo $doctor['doctor_id']; ?>">
                    <?php echo htmlspecialchars($doctor['name']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="date">Select Date:</label>
        <input type="date" id="date" name="date" required><br><br>

        <input type="submit" value="Request Appointment">
    </form>

    <a href="patient_dashboard.php">Back to Dashboard</a>
</body>
</html>
