<?php
// Get seller ID from session
$seller_id = $_SESSION['user_id'] ?? null;

if (!$seller_id) {
    echo '<p style="color: #666; text-align: center;">Please log in to view your orders.</p>';
    return;
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get recent orders for the logged-in seller's products only
$sql = "
SELECT DISTINCT
    o.order_id,
    o.user_id,
    o.total_price,
    o.status AS order_status,
    o.order_date AS created_at,
    b.username as buyer_name,
    COUNT(oi.product_id) as item_count
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
LEFT JOIN buyers b ON o.user_id = b.AccountID
WHERE p.seller_id = :seller_id
GROUP BY o.order_id, o.user_id, o.total_price, o.status, o.order_date, b.username
ORDER BY o.order_date DESC
LIMIT 5
";




    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($recentOrders)) {
        echo '<div class="no-data">
                <p>No orders received yet.</p>
                <p class="sub-text">Orders will appear here when customers purchase your products.</p>
              </div>';
    } else {
        echo '<div class="recent-orders-list">';
        foreach ($recentOrders as $order) {
            $orderId = htmlspecialchars($order['order_id']);
            $buyerName = htmlspecialchars($order['buyer_name'] ?? 'Unknown Buyer');
            $totalAmount = number_format($order['total_price'], 2);
            $orderStatus = htmlspecialchars($order['order_status']);
            $itemCount = (int)$order['item_count'];
            $orderDate = date('M j, Y g:i A', strtotime($order['created_at']));
            
            // Status styling
            $statusClass = '';
            switch (strtolower($orderStatus)) {
                case 'pending':
                    $statusClass = 'status-pending';
                    break;
                case 'processing':
                    $statusClass = 'status-processing';
                    break;
                case 'shipped':
                    $statusClass = 'status-shipped';
                    break;
                case 'delivered':
                    $statusClass = 'status-delivered';
                    break;
                case 'cancelled':
                    $statusClass = 'status-cancelled';
                    break;
                default:
                    $statusClass = 'status-default';
            }
            
            echo '<div class="recent-order-item">
                    <div class="order-header">
                        <div class="order-info">
                            <h4>Order #' . $orderId . '</h4>
                            <p class="buyer-name">By: ' . $buyerName . '</p>
                        </div>
                        <div class="order-status">
                            <span class="status-badge ' . $statusClass . '">' . ucfirst($orderStatus) . '</span>
                        </div>
                    </div>
                    <div class="order-details">
                        <div class="order-amount">₱' . $totalAmount . '</div>
                        <div class="order-meta">
                            <span class="item-count">' . $itemCount . ' item' . ($itemCount > 1 ? 's' : '') . '</span>
                            <span class="order-date">' . $orderDate . '</span>
                        </div>
                    </div>
                  </div>';
        }
        echo '</div>';
        
        // View all orders link
        echo '<div class="view-all">
                <a href="seller.orders.php">View All Orders →</a>
              </div>';
    }

} catch (PDOException $e) {
    echo '<div class="error-message">
            <p>Error loading recent orders: ' . htmlspecialchars($e->getMessage()) . '</p>
          </div>';
}
?>

<style>
.recent-orders-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 300px;
    overflow-y: auto;
}

.recent-order-item {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    transition: background-color 0.2s;
}

.recent-order-item:hover {
    background: #e9ecef;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.order-info h4 {
    margin: 0 0 2px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.buyer-name {
    margin: 0;
    font-size: 12px;
    color: #666;
}

.status-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-delivered {
    background: #d1ecf1;
    color: #0c5460;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.status-default {
    background: #e2e3e5;
    color: #383d41;
}

.order-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-amount {
    font-size: 16px;
    font-weight: 700;
    color: #28a745;
}

.order-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.item-count {
    font-size: 12px;
    color: #666;
}

.order-date {
    font-size: 11px;
    color: #999;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: #666;
}

.sub-text {
    font-size: 14px;
    color: #999;
    margin-top: 5px;
}

.view-all {
    text-align: center;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e9ecef;
}

.view-all a {
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.view-all a:hover {
    text-decoration: underline;
}

.error-message {
    text-align: center;
    padding: 20px;
    color: #dc3545;
    background: #f8d7da;
    border-radius: 4px;
    margin: 10px 0;
}
</style>