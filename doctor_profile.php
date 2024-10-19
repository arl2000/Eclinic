<?php
// Include the database connection
include 'db.php';

// Get the doctor_id from the GET request
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

// Fetch doctor's information
$doctor_info = [];
$doctor_stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor_result = $doctor_stmt->get_result();

if ($doctor_result->num_rows === 1) {
    $doctor_info = $doctor_result->fetch_assoc();
}

$doctor_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Doctor Profile</title>
</head>
<body>
    <div class="container">
        <h1>Doctor Profile</h1>

        <?php if (!empty($doctor_info)): ?>
            <h2>Dr. <?php echo htmlspecialchars($doctor_info['name']); ?></h2>
            <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor_info['specialization_name']); ?></p>
            <p><strong>Years of Experience:</strong> <?php echo htmlspecialchars($doctor_info['years_of_experience']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($doctor_info['email']); ?></p>
            
            <h3>Services Offered</h3>
            <?php if (!empty($services)): ?>
                <ul class="list-group">
                    <?php foreach ($services as $service): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($service['service_name']); ?></strong><br>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <p><strong>Price:</strong> <?php echo htmlspecialchars($service['price']); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No services available for this doctor at the moment.</p>
            <?php endif; ?>

            <h3>Book an Appointment</h3>
            <form action="book_appointment.php" method="POST">
                <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                <button type="submit" class="btn btn-primary">Book Appointment</button>
            </form>
        <?php else: ?>
            <p>Doctor not found.</p>
        <?php endif; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
