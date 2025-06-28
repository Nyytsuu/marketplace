<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed");
}

$driver_id = $_SESSION['driver_id'] ?? null;

if (!$driver_id) {
    echo "Please login first";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    
    // Get order details to calculate earnings
    $orderStmt = $conn->prepare("SELECT total_price, delivery_address_id FROM orders WHERE order_id = ?");
    $orderStmt->bind_param("i", $order_id);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    $order = $orderResult->fetch_assoc();
    
    if ($order) {
        // Calculate earnings (e.g., 10% of order total or fixed amount)
        $earnings = $order['total_price'] * 0.10; // 10% commission
        
        // Insert into deliveries table
        $insertStmt = $conn->prepare("INSERT INTO deliveries (order_id, driver_id, address_id, earnings, status) VALUES (?, ?, ?, ?, 'accepted')");
        $insertStmt->bind_param("iiid", $order_id, $driver_id, $order['delivery_address_id'], $earnings);
        
        if ($insertStmt->execute()) {
            // Update order status
            $updateStmt = $conn->prepare("UPDATE orders SET status = 'accepted' WHERE order_id = ?");
            $updateStmt->bind_param("i", $order_id);
            $updateStmt->execute();
            
            echo "success";
        } else {
            echo "Failed to accept delivery";
        }
    } else {
        echo "Order not found";
    }
} else {
    echo "Invalid request";
}
?>