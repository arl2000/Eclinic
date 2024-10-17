<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Appointment System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 50px;
        }

        h1 {
            color: #333;
        }

        .container {
            display: inline-block;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .button:hover {
            background-color: #218838;
        }

        .button-secondary {
            background-color: #007bff;
        }

        .button-secondary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <h1>Welcome to the Clinic Appointment System</h1>

    <div class="container">
        <p>Existing patient? Please log in below:</p>
        <a href="login.php" class="button">Log In</a>

        <p>New patient? Create an account:</p>
        <a href="register_patient.php" class="button button-secondary">Register as a Patient</a>
    </div>

</body>
</html>
