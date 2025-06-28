<?php
session_start();
require 'db_connection.php'; // your PDO connection

function clean($data) {
    return htmlspecialchars(trim($data));
}

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User not logged in.");
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $first_name = clean($_POST['first_name'] ?? '');
    $last_name = clean($_POST['last_name'] ?? '');
    $address = clean($_POST['address'] ?? '');
    $region = clean($_POST['region'] ?? '');
    $province = clean($_POST['province'] ?? '');
    $zip_code = clean($_POST['zip_code'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $email = clean($_POST['email'] ?? '');

    // Basic validation
    if (!$first_name || !$last_name || !$address || !$region || !$province || !$zip_code || !$phone || !$email) {
        die("All fields are required.");
    }

    try {
        // Check if billing info already exists
        $stmt_check = $pdo->prepare("SELECT * FROM billing_info WHERE user_id = ?");
        $stmt_check->execute([$user_id]);

        if ($stmt_check->rowCount() > 0) {
            // Update existing billing info
            $stmt = $pdo->prepare("UPDATE billing_info SET 
                first_name = ?, last_name = ?, address = ?, region = ?, province = ?, 
                zip_code = ?, phone = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$first_name, $last_name, $address, $region, $province, $zip_code, $phone, $email, $user_id]);
        } else {
            // Insert new billing info
            $stmt = $pdo->prepare("INSERT INTO billing_info 
                (user_id, first_name, last_name, address, region, province, zip_code, phone, email) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $first_name, $last_name, $address, $region, $province, $zip_code, $phone, $email]);
        }

        // Redirect or show success message
        header("Location: billing_success.php"); // or wherever you want
        exit();

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}
?>
