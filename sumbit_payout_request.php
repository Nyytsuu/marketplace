<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    echo json_encode(['success' => false, 'message' => 'Seller not logged in']);
    exit;
}

$amount = floatval($_POST['amount'] ?? 0);
$payment_method = $_POST['payment_method'] ?? '';
$account_number = $_POST['account_number'] ?? '';
$account_name = $_POST['account_name'] ?? '';

// Validation
if ($amount < 100) {
    echo json_encode(['success' => false, 'message' => 'Minimum withdrawal amount is ₱100.00']);
    exit;
}

if (empty($payment_method) || empty($account_number) || empty($account_name)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Check seller's available balance
    $balanceQuery = "SELECT available_balance FROM seller_balance WHERE seller_id = ?";
    $balanceStmt = $conn->prepare($balanceQuery);
    if (!$balanceStmt) {
        throw new Exception("Error preparing balance query: " . $conn->error);
    }
    
    $balanceStmt->bind_param("i", $seller_id);
    $balanceStmt->execute();
    $balanceResult = $balanceStmt->get_result();
    $balance = $balanceResult->fetch_assoc();
    $balanceStmt->close();
    
    if (!$balance || $balance['available_balance'] < $amount) {
        echo json_encode(['success' => false, 'message' => 'Insufficient balance']);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Create payment details JSON
    $payment_details = json_encode([
        'account_number' => $account_number,
        'account_name' => $account_name,
        'requested_at' => date('Y-m-d H:i:s')
    ]);
    
    // Insert payout request
    $requestQuery = "
        INSERT INTO payout_requests (seller_id, amount, payment_method, payment_details, status)
        VALUES (?, ?, ?, ?, 'pending')
    ";
    
    $requestStmt = $conn->prepare($requestQuery);
    if (!$requestStmt) {
        throw new Exception("Error preparing request query: " . $conn->error);
    }
    
    $requestStmt->bind_param("idss", $seller_id, $amount, $payment_method, $payment_details);
    
    if (!$requestStmt->execute()) {
        throw new Exception("Error inserting payout request: " . $requestStmt->error);
    }
    
    $requestStmt->close();
    
    // Update seller balance (reduce available balance)
    $updateBalanceQuery = "
        UPDATE seller_balance 
        SET available_balance = available_balance - ?,
            pending_balance = pending_balance + ?
        WHERE seller_id = ?
    ";
    
    $updateStmt = $conn->prepare($updateBalanceQuery);
    if (!$updateStmt) {
        throw new Exception("Error preparing balance update query: " . $conn->error);
    }
    
    $updateStmt->bind_param("ddi", $amount, $amount, $seller_id);
    
    if (!$updateStmt->execute()) {
        throw new Exception("Error updating balance: " . $updateStmt->error);
    }
    
    $updateStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Withdrawal request submitted successfully'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error submitting payout request: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
} finally {
    $conn->close();
}
?>