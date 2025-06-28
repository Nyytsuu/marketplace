<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = mysqli_connect("localhost", "root", "", "driver");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['Username'])) {
    header("Location: driver.php");
    exit();
}

$Username = $_SESSION['Username'];

// Fetch driver data
$query = "SELECT * FROM driver_signup WHERE Username = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $Username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $driver = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    die("Database error: " . mysqli_error($conn));
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pass'])) {
    $current = trim($_POST['current-password']);
    $new = trim($_POST['new-password']);

    // Fetch existing hashed password
    $query = "SELECT Password FROM driver_signup WHERE Username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $Username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user && password_verify($current, $user['Password'])) {
        $newHashed = password_hash($new, PASSWORD_DEFAULT);
        $update = "UPDATE driver_signup SET Password = ? WHERE Username = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "ss", $newHashed, $Username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: driverprofile.php?success=1");
        exit();
    } else {
        header("Location: driverprofile.php?error=wrongpass");
        exit();
    }
}
?>
