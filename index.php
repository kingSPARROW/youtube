<?php
session_start();
$servername = 'localhost';
$dbname = 'login';
$username = 'root';
$password = '';
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from the form
    $employee_name = $_POST['employee_name'];
    $user_password = $_POST['user_password'];

    // Sanitize user input to prevent SQL injection
    $employee_name = mysqli_real_escape_string($conn, $employee_name);
    $user_password = mysqli_real_escape_string($conn, $user_password);

    // Query to check if the user exists
    $query = "SELECT * FROM users WHERE employee_name = '$employee_name' AND user_password = '$user_password'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        // Check if a row was returned
        if (mysqli_num_rows($result) == 1) {
            // Valid user, set session variable and redirect to dashboard.php
            $_SESSION['employee_name'] = $employee_name;
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Error in the query: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .login-container {
            width: 300px;
            margin: 100px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php
        // Display error message if any
        if (isset($error_message)) {
            echo '<p class="error-message">' . $error_message . '</p>';
        }
        ?>
        <form method="post" action="">
            <label for="employee_name">Employee Name:</label>
            <input type="text" name="employee_name" required><br>

            <label for="user_password">Password:</label>
            <input type="password" name="user_password" required><br>

            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>
