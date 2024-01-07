<?php
session_start();
if (!isset($_SESSION['employee_name'])) {
    // Redirect to index.php for authentication
    header("Location: index.php");
    exit(); // Ensure that no further code is executed after redirection
}
// Your fixed token
$fixedToken = '12345';

// Check if the user is logged in
if (!isset($_SESSION['employee_name'])) {
    // Check if the auto-login parameters are present in the URL
    if (isset($_GET['user']) && isset($_GET['token'])) {
        // Check if the provided token matches the fixed token
        if ($_GET['token'] === $fixedToken) {
            // Log in the user using the provided username
            $_SESSION['employee_name'] = $_GET['user'];
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Invalid token.";
            exit();
        }
    } else {
        // Redirect to the login page if no auto-login parameters are present
        header("Location: index.php");
        exit();
    }
}

$servername = 'localhost';
$dbname = 'login';
$username = 'root';
$password = '';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set default time zone for PHP
date_default_timezone_set('Asia/Kolkata');

// Check if the user is logged in
if (!isset($_SESSION['employee_name'])) {
    header("Location: index.php");
    exit();
}

$employee_name = $_SESSION['employee_name'];
$currentDate = date("Y-m-d");

// Check if attendance for today exists
$query = "SELECT * FROM attendance WHERE employee_name = '$employee_name' AND Date = '$currentDate'";
$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) == 0) {
        // Attendance for today doesn't exist, show check-in button
        $markAttendanceButton = '<form method="post" action="">
                                    <input type="submit" name="checkin" value="Check-In" class="mark-attendance-btn">
                                </form>';
    } else {
        // Attendance for today exists
        $attendanceData = mysqli_fetch_assoc($result);

        if ($attendanceData['first'] == null) {
            // Check-in time is not set, show check-in button
            $markAttendanceButton = '<form method="post" action="">
                                        <input type="submit" name="checkin" value="Check-In" class="mark-attendance-btn">
                                    </form>';
        } else {
            // Check-in time is set, show check-out button
            $markAttendanceButton = '<form method="post" action="">
                                        <input type="submit" name="checkout" value="Check-Out" class="mark-attendance-btn">
                                    </form>';
        }

        // Display totalminutes if Checkin and Checkout times are set
        if ($attendanceData['first'] !== null && $attendanceData['last'] !== null) {
            $totalminutes = $attendanceData['totalminutes'];
        }

        // Display totaltime if it is set
        if ($attendanceData['totalminutes'] !== null) {
            $totalminutes = $attendanceData['totalminutes'];
        }
    }
} else {
    $error_message = "Error in the query: " . mysqli_error($conn);
}

// Handle check-in or check-out submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['checkin'])) {
        // Check-in button clicked
        $checkinTime = date("H:i:s");
        $query = "INSERT INTO attendance (employee_name, Date, first, status) VALUES ('$employee_name', '$currentDate', '$checkinTime', 'Work From Home')";
        mysqli_query($conn, $query);
        header("Location: dashboard.php");
        exit();
    } elseif (isset($_POST['checkout'])) {
        // Check-out button clicked
        $checkoutTime = date("H:i:s");
        $query = "UPDATE attendance SET last = '$checkoutTime', totalminutes = TIME_TO_SEC(TIMEDIFF('$checkoutTime', first))/60, status = 'Work From Home' WHERE employee_name = '$employee_name' AND Date = '$currentDate'";
        mysqli_query($conn, $query);
        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System - Dashboard</title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        h3 {
            color: #555;
            text-align: center;
            margin-top: 20px;
        }

        /* Button styles */
        .mark-attendance-btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .mark-attendance-btn:hover {
            background-color: #2980b9;
        }

        /* Animation for h2 */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        h2 {
            animation: fadeIn 1s ease;
        }

        /* Responsive styles */
        @media screen and (max-width: 600px) {
            h2 {
                font-size: 24px;
            }

            h3 {
                font-size: 18px;
            }

            .mark-attendance-btn {
                font-size: 14px;
            }
        }

        /* Logo styles */
        .company-logo {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- Company Logo -->
    <img src="logo.png" alt="Company Logo" class="company-logo">

    <h2>Welcome, <?php echo $_SESSION['employee_name']; ?>!</h2>

    <h3>Mark Attendance</h3>
    <?php
    // Display error message if any
    if (isset($error_message)) {
        echo '<p style="color:red;">' . $error_message . '</p>';
    }

    // Display mark attendance button
    echo $markAttendanceButton;
    ?>
</body>

</html>
