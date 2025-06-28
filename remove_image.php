<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    echo json_encode(['success' => false, 'message' => 'Seller not logged in']);
    exit();
}

$id = (int)$_POST['id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Verify image belongs to seller's product
$checkStmt = $conn->prepare("
    SELECT pi.image_path, p.seller_id 
    FROM product_images pi 
    JOIN products p ON pi.product_id = p.product_id 
    WHERE pi.id = ?
");
$checkStmt->bind_param("i", $image_id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$imageData = $result->fetch_assoc();

if (!$imageData || $imageData['seller_id'] != $seller_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Delete image file
if (file_exists($imageData['image_path'])) {
    unlink($imageData['image_path']);
}

// Delete image record
$deleteStmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Image removed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove image']);
}

$conn->close();
?>