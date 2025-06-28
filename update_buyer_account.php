<?php
session_start();
include 'db_connection.php';  // This sets $pdo

if (!isset($_SESSION['AccountID'])) {
    die("You must be logged in to update your account.");
}
$buyer_id = $_SESSION['AccountID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_number)) {
        die("All fields are required.");
    }

    $sql = "UPDATE buyers SET first_name = ?, last_name = ?, email = ?, phone_number = ? WHERE AccountID = ?";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$first_name, $last_name, $email, $phone_number, $buyer_id]);

    if ($success) {
        header("Location: buyerdashboard.php"); 
        exit;       
    } else {
        echo "Error updating account.";
    }
}
?>
