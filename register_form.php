<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>

<h2>Register</h2>
<form action="register.php" method="POST">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>

    <label for="role">Role:</label>
    <select id="role" name="role" onchange="toggleDoctorFields()" required>
        <option value="patient">Patient</option>
        <option value="doctor">Doctor</option>
    </select><br><br>

    <div id="doctorFields" style="display:none;">
        <label for="specialization_id">Specialization:</label>
        <select id="specialization_id" name="specialization_id">
            <!-- Add options dynamically from your database -->
            <option value="1">General Practitioner</option>
            <option value="2">Dentist</option>
            <!-- Add more specializations as needed -->
        </select><br><br>

        <label for="availability_schedule">Availability Schedule:</label>
        <input type="text" id="availability_schedule" name="availability_schedule" placeholder="e.g., Mon-Fri 9am-5pm"><br><br>
    </div>

    <input type="submit" value="Register">
</form>

<script>
// Toggle visibility of doctor-specific fields
function toggleDoctorFields() {
    var role = document.getElementById("role").value;
    var doctorFields = document.getElementById("doctorFields");
    if (role === "doctor") {
        doctorFields.style.display = "block";
    } else {
        doctorFields.style.display = "none";
    }
}
</script>

</body>
</html>
