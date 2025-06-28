<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$last_check = $input['last_check'] ?? 0;
$buyer_id = $input['buyer_id'] ?? null;

$response = ['updated' => false];

if (!$buyer_id) {
    echo json_encode($response);
    exit;
}

try {
    // Check if user data was updated since last check
    $sql = "SELECT UNIX_TIMESTAMP(updated_at) as last_updated 
            FROM buyers 
            WHERE AccountID = ? AND UNIX_TIMESTAMP(updated_at) > ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$buyer_id, $last_check]);
    $user_update = $stmt->fetch();

    // Check if address data was updated since last check
    $sql = "SELECT UNIX_TIMESTAMP(updated_at) as last_updated 
            FROM buyer_addresses 
            WHERE buyer_id = ? AND is_default = 1 AND UNIX_TIMESTAMP(updated_at) > ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$buyer_id, $last_check]);
    $address_update = $stmt->fetch();

    if ($user_update || $address_update) {
        $response['updated'] = true;
    }

} catch (PDOException $e) {
    error_log("Error checking for updates: " . $e->getMessage());
}

echo json_encode($response);
?>