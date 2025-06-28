<?php
// Start session if not already started
session_start();

// Include your PDO connection
include('db_connection.php');

// Get the buyer_id from session
$buyer_id = $_SESSION['AccountID'];  // Make sure this is set after login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $full_name = $_POST['full_name'];
    $barangay = $_POST['barangay'];
    $phone_number = $_POST['phone'];
    $street_address = $_POST['street_address'];
    $region = $_POST['region'];
    $postal_code = $_POST['postal_code'];
    $province = $_POST['province'];

    try {
       // Check how many addresses the user already has
$checkQuery = "SELECT COUNT(*) FROM buyer_addresses WHERE buyer_id = ?";
$checkStmt = $pdo->prepare($checkQuery);
$checkStmt->execute([$buyer_id]);
$addressCount = $checkStmt->fetchColumn();

// Decide if this address should be default
$is_default = ($addressCount == 0) ? 1 : 0;

// Insert address with dynamic is_default
$insertQuery = "INSERT INTO buyer_addresses 
    (buyer_id, full_name, phone_number, street_address, barangay, province, region, postal_code, address_type, is_default)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'billing', ?)";

$insertStmt = $pdo->prepare($insertQuery);
$insertStmt->execute([
    $buyer_id,
    $full_name,
    $phone_number,
    $street_address,
    $barangay,
    $province,
    $region,
    $postal_code,
    $is_default
]);
        // Success
        header("Location: buyerAddress.php");
        exit();

    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}
?>

