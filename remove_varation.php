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

$variation_id = (int)$_POST['variation_id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Verify variation belongs to seller's product
$checkStmt = $conn->prepare("
    SELECT pv.variation_image, p.seller_id 
    FROM product_variations pv 
    JOIN products p ON pv.product_id = p.product_id 
    WHERE pv.variation_id = ?
");
$checkStmt->bind_param("i", $variation_id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$variationData = $result->fetch_assoc();

if (!$variationData || $variationData['seller_id'] != $seller_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Delete variation image file if exists
if ($variationData['variation_image'] && file_exists($variationData['variation_image'])) {
    unlink($variationData['variation_image']);
}

// Delete variation record
$deleteStmt = $conn->prepare("DELETE FROM product_variations WHERE variation_id = ?");
$deleteStmt->bind_param("i", $variation_id);

if ($deleteStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Variation removed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove variation']);
}

$conn->close();
?>