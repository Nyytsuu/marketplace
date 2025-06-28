<?php
session_start();
include 'db_connection.php'; // Assumes $pdo (PDO connection)

if (isset($_POST['driver_signin'])) {
    $login_input = trim($_POST['Username']); // Can be Username or EmailAdd
    $password = $_POST['Password'];

    $stmt = $pdo->prepare("SELECT DriverID, Username, Password FROM drivers WHERE Username = ? OR EmailAdd = ?");
    $stmt->execute([$login_input, $login_input]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($driver) {
        if ($password === $driver['Password']) { // Plain text comparison
            $_SESSION['DriverID'] = $driver['DriverID'];
            $_SESSION['Username'] = $driver['Username'];
            $_SESSION['success_message'] = "Successfully signed in!";
            header("Location: driver_dashboard.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Incorrect password.";
            header("Location: driver.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Driver not found.";
        header("Location: driver.php");
        exit();
    }
}

if (isset($_POST['driver_signup'])) {
    $username = trim($_POST['Username']);
    $email = trim($_POST['EmailAdd']);
    $phone = trim($_POST['PhoneNum']);
    $password = $_POST['Password']; // Plain text
    $vehicle_info = trim($_POST['vehicle_info']);
    $barangay = trim($_POST['delivery_barangay']);
    $city = trim($_POST['delivery_city']);
    $province = trim($_POST['delivery_province']);

    // Check if username or email already exists
    $check_stmt = $pdo->prepare("SELECT DriverID FROM drivers WHERE Username = ? OR EmailAdd = ?");
    $check_stmt->execute([$username, $email]);
    $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $_SESSION['signup_error'] = "Username or email already exists.";
        header("Location: driver.php");
        exit();
    } else {
        $stmt = $pdo->prepare("INSERT INTO drivers (Username, EmailAdd, PhoneNum, Password, vehicle_info, delivery_barangay, delivery_city, delivery_province) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$username, $email, $phone, $password, $vehicle_info, $barangay, $city, $province]);

        if ($success) {
            $_SESSION['DriverID'] = $pdo->lastInsertId();
            $_SESSION['success_message'] = "Registration successful! You can now sign in.";
            header("Location: driver.php");
            exit();
        } else {
            $_SESSION['signup_error'] = "Signup failed. Try again.";
            header("Location: driver.php");
            exit();
        }
    }
}
?>
