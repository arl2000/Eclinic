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

// Fetch current doctor's information
$stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $doctor_info = $result->fetch_assoc();
}
$stmt->close();

// Handle profile update or set availability form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check which part of the form is submitted
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $name = $_POST['name'] ?? null;
        $availability_schedule = $_POST['availability_schedule'] ?? null;
        $years_of_experience = $_POST['years_of_experience'] ?? null;
        $profile_image = $_FILES['profile_image']['name'] ?? null;
        $cover_photo = $_FILES['cover_photo']['name'] ?? null;
        $license = $_FILES['license']['name'] ?? null;

        // Specify the uploads directory
        $target_dir = "uploads/";

        // Handle profile image upload
        if ($profile_image) {
            $target_file = $target_dir . basename($_FILES['profile_image']['name']);
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                // Update profile image in the database
                $stmt_update_profile = $conn->prepare("UPDATE doctors SET profile_image = ? WHERE doctor_id = ?");
                $stmt_update_profile->bind_param("si", $target_file, $doctor_id);
                if (!$stmt_update_profile->execute()) {
                    echo "Error updating profile image: " . $stmt_update_profile->error;
                }
                $stmt_update_profile->close();
            } else {
                echo "Error uploading profile image.";
            }
        }
        // Handle cover photo upload
        if ($cover_photo) {
            $target_file = $target_dir . basename($_FILES['cover_photo']['name']);
            if (move_uploaded_file($_FILES['cover_photo']['tmp_name'], $target_file)) {
                // Update cover photo in the database
                $stmt_update_profile = $conn->prepare("UPDATE doctors SET cover_photo = ? WHERE doctor_id = ?");
                $stmt_update_profile->bind_param("si", $target_file, $doctor_id);
                if (!$stmt_update_profile->execute()) {
                    echo "Error updating cover photo: " . $stmt_update_profile->error;
                }
                $stmt_update_profile->close();
            } else {
                echo "Error uploading cover photo.";
            }
        }

        // Prepare profile update query
        $stmt_update_profile = $conn->prepare("UPDATE doctors SET name = ?, availability_schedule = ?, years_of_experience = ? WHERE doctor_id = ?");
        $stmt_update_profile->bind_param("ssii", $name, $availability_schedule, $years_of_experience, $doctor_id);
        if (!$stmt_update_profile->execute()) {
            echo "Error updating profile: " . $stmt_update_profile->error;
        }
        $stmt_update_profile->close();

        header("Location: doctor_dashboard.php");
        exit();
    } elseif (isset($_POST['set_availability'])) {
        // Set maximum patients and time slots
        $cater_date = $_POST['cater_date'] ?? null;
        $max_patients = $_POST['max_patients'] ?? null;
        $start_time = $_POST['start_time'] ?? null;
        $end_time = $_POST['end_time'] ?? null;

        if ($cater_date && $max_patients && $start_time && $end_time) {
            // Check if the doctor already has a set limit for that date
            $stmt_check = $conn->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ? AND cater_date = ?");
            $stmt_check->bind_param("is", $doctor_id, $cater_date);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Update existing record
                $stmt_update_availability = $conn->prepare("UPDATE doctor_availability SET max_patients = ?, start_time = ?, end_time = ? WHERE doctor_id = ? AND cater_date = ?");
                $stmt_update_availability->bind_param("issis", $max_patients, $start_time, $end_time, $doctor_id, $cater_date);
                if ($stmt_update_availability->execute()) {
                    echo "Availability updated successfully";
                } else {
                    echo "Error updating availability: " . $stmt_update_availability->error;
                }
                $stmt_update_availability->close();
            } else {
                // Insert new record
                $stmt_insert_availability = $conn->prepare("INSERT INTO doctor_availability (doctor_id, cater_date, max_patients, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
                $stmt_insert_availability->bind_param("isiss", $doctor_id, $cater_date, $max_patients, $start_time, $end_time);
                if ($stmt_insert_availability->execute()) {
                    echo "New availability set successfully";
                } else {
                    echo "Error inserting new availability: " . $stmt_insert_availability->error;
                }
                $stmt_insert_availability->close();
            }

            header("Location: doctor_dashboard.php");
            exit();
        } else {
            echo "Missing form data!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Doctor Dashboard</title>
    <style>
        body {
            background-color: #f3f6f9;
        }
        h2 {
            color: #0073b1;
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
        .modal-header {
            background-color: #0073b1;
            color: white;
        }
        .btn-primary {
            background-color: #0073b1;
            border: none;
        }
        .btn-primary:hover {
            background-color: #005582;
        }
        .mb-3, .btn-info, .availability{
            margin-left: 40px;
        }
        
        .active {
            color: black;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Doctor Profile Information</h2>

            <div class="mb-1">
                <?php if (isset($doctor_info['cover_photo']) && $doctor_info['cover_photo']): ?>
                    <img src="<?php echo htmlspecialchars($doctor_info['cover_photo']); ?>" alt="Cover photo" class="cover-photo"><br>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php if (isset($doctor_info['profile_image']) && $doctor_info['profile_image']): ?>
                    <img src="<?php echo htmlspecialchars($doctor_info['profile_image']); ?>" alt="Profile Image" class="profile-image"><br>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <strong> <?php echo htmlspecialchars($doctor_info['name'] ?? ''); ?></strong>
            </div>

            <div class="mb-3">
                Availability Schedule: <?php echo htmlspecialchars($doctor_info['availability_schedule'] ?? ''); ?>
            </div>

            <div class="mb-3">
                With <?php echo htmlspecialchars($doctor_info['years_of_experience'] ?? 0); ?> years of Experience: 
            </div>

            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                Edit Profile
            </button>

            <!-- Modal for editing profile -->
            <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="doctor_dashboard.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="mb-3">
                                <?php if (isset($doctor_info['profile_image']) && $doctor_info['profile_image']): ?>
                                    <img src="<?php echo htmlspecialchars($doctor_info['profile_image']); ?>" alt="Profile Image" style="width: 100px; height: 100px;"><br>
                                <?php endif; ?>
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="form-control">
                                </div>

                                <div class="mb-3">
                                <?php if (isset($doctor_info['cover_photo']) && $doctor_info['cover_photo']): ?>
                                    <img src="<?php echo htmlspecialchars($doctor_info['cover_photo']); ?>" alt="Cover photo" style="width: 100%; height: 400px;"><br>
                                <?php endif; ?>
                                    <input type="file" id="cover_photo" name="cover_photo" accept="image/*" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Name:</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($doctor_info['name'] ?? ''); ?>" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="availability_schedule" class="form-label">Availability Schedule:</label>
                                    <input type="text" id="availability_schedule" name="availability_schedule" value="<?php echo htmlspecialchars($doctor_info['availability_schedule'] ?? ''); ?>" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="years_of_experience" class="form-label">Years of Experience:</label>
                                    <input type="number" id="years_of_experience" name="years_of_experience" value="<?php echo htmlspecialchars($doctor_info['years_of_experience'] ?? 0); ?>" class="form-control">
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
            <div class="availability">
                <h2>Set Availability</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#setAvailabilityModal">
                        Set Availability
                    </button>

                    <a href="doctor_appointments.php" class="btn btn-success">
                        View Appointments
                    </a>

                    <a href="doctors_availability.php" class="btn btn-success">
                        View Availability
                    </a>

                    <a href="logout.php">Log Out</a>
            </div>
               

            <!-- Modal for setting availability -->
            <div class="modal fade" id="setAvailabilityModal" tabindex="-1" aria-labelledby="setAvailabilityModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="setAvailabilityModalLabel">Set Availability</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="doctor_dashboard.php" method="POST">
                                <input type="hidden" name="set_availability" value="1">
                                
                                <div class="mb-3">
                                    <label for="cater_date" class="form-label">Cater Date:</label>
                                    <input type="date" id="cater_date" name="cater_date" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="max_patients" class="form-label">Max Patients:</label>
                                    <input type="number" id="max_patients" name="max_patients" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time:</label>
                                    <input type="time" id="start_time" name="start_time" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time:</label>
                                    <input type="time" id="end_time" name="end_time" class="form-control" required>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Availability</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
</div>

<?php include 'navbar.html'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
