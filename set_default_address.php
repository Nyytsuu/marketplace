<?php
session_start();
include('db_connection.php'); // assumes this sets $pdo

// Check if user is logged in
if (!isset($_SESSION['AccountID'])) {
    echo "You must be logged in.";
    exit();
}

$buyer_id = $_SESSION['AccountID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];

    try {
        // Reset other addresses
        $reset = $pdo->prepare("UPDATE buyer_addresses SET is_default = FALSE WHERE buyer_id = ?");
        $reset->execute([$buyer_id]);

        // Set new default
       $update = $pdo->prepare("UPDATE buyer_addresses SET is_default = TRUE WHERE buyer_id = ? AND address_id = ?");
       $update->execute([$buyer_id, $address_id]);
        // Redirect or reload page
        header("Location: buyerAddress.php"); // or reload current page
        exit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

