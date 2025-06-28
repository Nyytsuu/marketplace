<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['Username'])) {
    echo "Not logged in.";
    exit();
}

$Username = $_SESSION['Username'];

$stmt = $conn->prepare("DELETE FROM drivers WHERE Username = ?");
$stmt->bind_param("s", $Username);

if ($stmt->execute()) {
    session_unset();
    session_destroy();
    echo "success";
} else {
    echo "Failed to delete account.";
}

$stmt->close();
$conn->close();
?>
