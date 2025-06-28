<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_id = $_POST['address_id'] ?? null;
    $buyer_id = $_SESSION['AccountID'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['buyer_id'] ?? null;

    if (!$buyer_id || !$address_id) {
        echo json_encode(['success' => false, 'message' => 'Missing buyer ID or address ID.']);
        exit;
    }

    try {
        // Check if the address belongs to the buyer and is not default
        $stmt = $pdo->prepare("SELECT * FROM buyer_addresses WHERE address_id = ? AND buyer_id = ? AND is_default = 0");
        $stmt->execute([$address_id, $buyer_id]);
        $address = $stmt->fetch();

        if (!$address) {
            echo json_encode(['success' => false, 'message' => 'Address not found or cannot delete default address.']);
            exit;
        }

        // Delete the address
        $stmt = $pdo->prepare("DELETE FROM buyer_addresses WHERE address_id = ?");
        $stmt->execute([$address_id]);

        echo json_encode(['success' => true, 'message' => 'Address deleted successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting address: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
