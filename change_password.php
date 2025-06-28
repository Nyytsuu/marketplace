<?php
session_start();
include 'db_connection.php'; // contains $pdo

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: buyer.settings.php?error=invalid_request");
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['AccountID'])) {
    header("Location: finalslogin.php");
    exit;
}

$buyer_id = $_SESSION['AccountID'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Check if new password and confirmation match
if ($new_password !== $confirm_password) {
    header("Location: buyer.settings.php?error=password_mismatch");
    exit;
}

// Check if passwords are not empty
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    header("Location: buyer.settings.php?error=empty_fields");
    exit;
}

try {
    // Get current password from database
    $stmt = $pdo->prepare("SELECT Password FROM buyers WHERE AccountID = ?");
    $stmt->execute([$buyer_id]);
    $stored_password = $stmt->fetchColumn();

    if (!$stored_password) {
        header("Location: buyer.settings.php?error=user_not_found");
        exit;
    }

    // Compare plain text passwords (Note: This is insecure, consider using password_hash() and password_verify())
    if ($current_password !== $stored_password) {
        header("Location: buyer.settings.php?error=incorrect_password");
        exit;
    }

    // Update password
    $stmt = $pdo->prepare("UPDATE buyers SET Password = ? WHERE AccountID = ?");
    $success = $stmt->execute([$new_password, $buyer_id]);

    if ($success) {
        header("Location: buyer.settings.php?success=password_updated");
        exit;
    } else {
        header("Location: buyer.settings.php?error=update_failed");
        exit;
    }

} catch (Exception $e) {
    error_log("Password change error: " . $e->getMessage());
    header("Location: buyer.settings.php?error=database_error");
    exit;
}
?>