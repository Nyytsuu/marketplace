<?php
// This file handles updating seller earnings when an order is shipped
function updateSellerEarnings($order_id, $conn) {
    try {
        // Get all items for this order with seller information
        $itemsQuery = "
            SELECT oi.*, p.seller_id, p.product_name,
                   (oi.price * oi.quantity) as total_amount
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ";
        
        $itemsStmt = $conn->prepare($itemsQuery);
        if (!$itemsStmt) {
            throw new Exception("Error preparing items query: " . $conn->error);
        }
        
        $itemsStmt->bind_param("i", $order_id);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $commission_rate = 5.00; // 5% commission rate
        
        while ($item = $itemsResult->fetch_assoc()) {
            $total_amount = $item['total_amount'];
            $commission_amount = ($total_amount * $commission_rate) / 100;
            $net_amount = $total_amount - $commission_amount;
            
            // Insert earning record
            $earningQuery = "
                INSERT INTO seller_earnings 
                (seller_id, order_id, product_id, amount, commission_rate, commission_amount, net_amount, status, available_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'available', NOW())
            ";
            
            $earningStmt = $conn->prepare($earningQuery);
            if (!$earningStmt) {
                throw new Exception("Error preparing earning query: " . $conn->error);
            }
            
            $earningStmt->bind_param("iiidddd", 
                $item['seller_id'], 
                $order_id, 
                $item['product_id'], 
                $total_amount, 
                $commission_rate, 
                $commission_amount, 
                $net_amount
            );
            
            if (!$earningStmt->execute()) {
                throw new Exception("Error inserting earning: " . $earningStmt->error);
            }
            
            // Update seller balance
            updateSellerBalance($item['seller_id'], $net_amount, $conn);
            
            $earningStmt->close();
        }
        
        $itemsStmt->close();
        return true;
        
    } catch (Exception $e) {
        error_log("Error updating seller earnings: " . $e->getMessage());
        return false;
    }
}

function updateSellerBalance($seller_id, $amount, $conn) {
    // Update or insert seller balance
    $balanceQuery = "
        INSERT INTO seller_balance (seller_id, available_balance, total_earned)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
        available_balance = available_balance + VALUES(available_balance),
        total_earned = total_earned + VALUES(total_earned),
        last_updated = NOW()
    ";
    
    $balanceStmt = $conn->prepare($balanceQuery);
    if (!$balanceStmt) {
        throw new Exception("Error preparing balance query: " . $conn->error);
    }
    
    $balanceStmt->bind_param("idd", $seller_id, $amount, $amount);
    
    if (!$balanceStmt->execute()) {
        throw new Exception("Error updating balance: " . $balanceStmt->error);
    }
    
    $balanceStmt->close();
}
?>