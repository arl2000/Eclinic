<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'patient') {
    header("Location: index.php");
}

// Include the database connection
include 'db.php';

$patient_id = $_SESSION['user_id'];
$patient_info = [];

// Fetch current patient's information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $patient_info = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];

    // Initialize variable for profile image and c photo path
    $profile_image = $patient_info['profile_image'];
    $cover_photo = $patient_info['cover_photo'];

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $profile_image = 'uploads/profile_' . $patient_id . '_' . $_FILES['profile_image']['name'];
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_image);
    }
   // Handle cover photo upload
        if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] == UPLOAD_ERR_OK) {
            $cover_photo = 'uploads/cover_' . $patient_id . '_' . $_FILES['cover_photo']['name'];
            move_uploaded_file($_FILES['cover_photo']['tmp_name'], $cover_photo);
        }

        // Update patient's personal information in the database
        $stmt_update = $conn->prepare("UPDATE users SET name=?, address=?, contact_number=?, profile_image=?, cover_photo=? WHERE id=?");
        $stmt_update->bind_param("sssssi", $name, $address, $contact_number, $profile_image, $cover_photo, $patient_id);

    if ($stmt_update->execute()) {
        echo "Profile updated successfully!";
        // Optionally, you can reload the page to reflect changes
        header("Location: patient_dashboard.php");
        exit();
    } else {
        echo "Error updating profile: " . $stmt_update->error;
    }

    $stmt_update->close();
}

// Fetch the assigned doctor information (assuming you have a doctor_id in the users table)
$doctor_info = [];
$doctor_stmt = $conn->prepare("SELECT * FROM doctors");
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
    <title>Patient Dashboard</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
    }
    button {
        padding: 10px 15px;
        font-size: 16px;
        background-color: #4CAF50; /* Green */
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    button:hover {
        background-color: #45a049; /* Darker green */
    }
    .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 80%;
            margin-top: -100px;
        }
        .cover-photo{
            width: 100%;
            height: 300px;
        }
        .doctors .img-fluid{
            width: 100px; 
            height: 100px; 
            border-radius: 50%;
            left: auto;
        }
</style>

</head>
<body>
    <h1>Welcome to the Patient Dashboard</h1>
    
        <div class="mb-1">
                <?php if (isset($patient_info['cover_photo']) && $patient_info['cover_photo']): ?>
                    <img src="<?php echo htmlspecialchars($patient_info['cover_photo']); ?>" alt="Cover photo" class="cover-photo"><br>
                <?php endif; ?>
        </div>

            <div class="mb-3">
                <?php if (isset($patient_info['profile_image']) && $patient_info['profile_image']): ?>
                    <img src="<?php echo htmlspecialchars($patient_info['profile_image']); ?>" alt="Profile Image" class="profile-image"><br>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <strong> <?php echo htmlspecialchars($patient_info['name'] ?? ''); ?></strong>
            </div>

            <div class="mb-3">
                Addrress: <?php echo htmlspecialchars($patient_info['address'] ?? ''); ?>
            </div>

            <div class="mb-3">
                <?php echo htmlspecialchars($patient_info['contact_number'] ?? 0); ?> 
            </div>

            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                Edit Profile
            </button>

    <h2>Set an Appointment</h2>
    <a href="patient_appointment_request.php"><button>Request Appointment</button></a>
    
    <a href="logout.php">Log Out</a>

                        <!-- Modal for editing profile -->
            <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="patient_dashboard.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="update_profile" value="1">

                                <!-- Display current profile image -->
                                <div class="mb-3">
                                    <?php if (isset($patient_info['profile_image']) && $patient_info['profile_image']): ?>
                                        <img src="<?php echo htmlspecialchars($patient_info['profile_image']); ?>" alt="Profile Image" style="width: 100px; height: 100px;"><br>
                                    <?php endif; ?>
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="form-control">
                                </div>

                                <!-- Display current cover photo -->
                                <div class="mb-3">
                                    <?php if (isset($patient_info['cover_photo']) && $patient_info['cover_photo']): ?>
                                        <img src="<?php echo htmlspecialchars($patient_info['cover_photo']); ?>" alt="Cover photo" style="width: 100%; height: 400px;"><br>
                                    <?php endif; ?>
                                    <input type="file" id="cover_photo" name="cover_photo" accept="image/*" class="form-control">
                                </div>

                                <!-- Name Field -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name:</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($patient_info['name'] ?? ''); ?>" class="form-control" required>
                                </div>

                                <!-- Address Field -->
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address:</label>
                                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($patient_info['address'] ?? ''); ?>" class="form-control" required>
                                </div>

                                <!-- Contact Number Field -->
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number:</label>
                                    <input type="number" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($patient_info['contact_number'] ?? 0); ?>" class="form-control" required>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

                                <hr>
            <!-- displays available doctors -->
    <div class="doctors">
    <?php if (!empty($doctor_info)): ?>
            <h2>Your looking for?</h2>
            <div class="mb-3">
                <a href="doctor_profile.php?doctor_id=<?php echo $doctor_info['doctor_id']; ?>">
                    <img src="<?php echo htmlspecialchars($doctor_info['profile_image']); ?>" alt="Doctor Image" class="img-fluid">
                    <h3><?php echo htmlspecialchars($doctor_info['name']); ?></h3>
                    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor_info['specialization_name']); ?></p>
                </a>
            </div>
        <?php else: ?>
            <p>No doctor assigned.</p>
        <?php endif; ?>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
