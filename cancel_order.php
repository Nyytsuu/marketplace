<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['AccountID'])) {
    echo json_encode(['success' => false, 'message' => 'Login required.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$buyer_id = $_SESSION['AccountID'];
$order_id = $_POST['order_id'] ?? '';
$cancel_reason = $_POST['cancel_reason'] ?? '';

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Check if order exists and belongs to the buyer
    $orderQuery = "SELECT o.*, p.seller_id 
                   FROM orders o 
                   JOIN order_items oi ON o.order_id = oi.order_id 
                   JOIN products p ON oi.product_id = p.product_id 
                   WHERE o.order_id = ? AND o.user_id = ? 
                   LIMIT 1";
    $orderStmt = $pdo->prepare($orderQuery);
    $orderStmt->execute([$order_id, $buyer_id]);
    $order = $orderStmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found or access denied.');
    }
    
    // Check if order can be cancelled (only pending and processing orders)
    if (!in_array($order['status'], ['pending', 'processing'])) {
        throw new Exception('Order cannot be cancelled at this stage.');
    }
    
    // Update order status to cancelled
    $updateQuery = "UPDATE orders SET status = 'cancelled', cancel_reason = ?, cancelled_at = NOW() WHERE order_id = ?";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([$cancel_reason, $order_id]);
    
    // Optional: You can add email notification logic here if needed
    // For now, we'll just update the database
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order has been cancelled successfully.'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>