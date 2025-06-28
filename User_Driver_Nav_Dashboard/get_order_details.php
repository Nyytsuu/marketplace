<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (!isset($_SESSION['Username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['deliveryID'])) {
    echo json_encode(['success' => false, 'message' => 'Delivery ID missing']);
    exit;
}

$deliveryID = intval($_POST['deliveryID']);
$newStatus = 'accepted';

$stmt = $conn->prepare("UPDATE deliveries SET status = ? WHERE deliveryID = ?");
$stmt->bind_param("si", $newStatus, $deliveryID);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update delivery status']);
}

$stmt->close();
$conn->close();
?>
