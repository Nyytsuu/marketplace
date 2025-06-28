<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['AccountID'])) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buyer_id = $_SESSION['AccountID'];

    $address_id = $_POST['address_id'] ?? null;
    $full_name = $_POST['full_name'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $phone_number = $_POST['phone'] ?? '';
    $street_address = $_POST['street_address'] ?? '';
    $region = $_POST['region'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $province = $_POST['province'] ?? '';

    if (!$address_id) {
        die('Address ID missing.');
    }

    $stmt = $pdo->prepare("UPDATE buyer_addresses SET 
        full_name = ?, 
        phone_number = ?, 
        street_address = ?, 
        barangay = ?, 
        province = ?, 
        region = ?, 
        postal_code = ? 
        WHERE address_id = ? AND buyer_id = ?");

    $stmt->execute([
        $full_name,
        $phone_number,
        $street_address,
        $barangay,
        $province,
        $region,
        $postal_code,
        $address_id,
        $buyer_id
    ]);

    if ($stmt->rowCount() > 0) {
        // redirect or success message
        header("Location: buyerAddress.php?update=success");
        exit;
    } else {
        echo "No changes made or update failed.";
    }
}
?>
