<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    echo json_encode(['success' => false, 'message' => 'Seller not logged in']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;
$current_status = $_POST['current_status'] ?? null;

if (!$order_id || !$current_status) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Define next status mapping
$next_status_map = [
    'pending' => 'processing',
    'processing' => 'shipped',
    'shipped' => null, // No next status
];

if (!array_key_exists($current_status, $next_status_map)) {
    echo json_encode(['success' => false, 'message' => 'Invalid current status']);
    exit;
}

$new_status = $next_status_map[$current_status];
if ($new_status === null) {
    echo json_encode(['success' => false, 'message' => 'No further updates allowed from status: ' . $current_status]);
    exit;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

// Verify that the order belongs to seller's product(s) and current status matches
$stmt = $conn->prepare("
    SELECT COUNT(*) AS cnt
    FROM orders o
    INNER JOIN order_items oi ON o.order_id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.product_id
    WHERE o.order_id = ? AND p.seller_id = ? AND o.status = ?
");
$stmt->bind_param("iis", $order_id, $seller_id, $current_status);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($result['cnt'] == 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found, does not belong to you, or status mismatch']);
    exit;
}

// Update order status
$updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
$updateStmt->bind_param("si", $new_status, $order_id);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true, 'message' => "Order status updated to $new_status", 'new_status' => $new_status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . $updateStmt->error]);
}

$updateStmt->close();
$conn->close();
?>
