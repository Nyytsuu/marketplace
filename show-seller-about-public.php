<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product_id = $_GET['id'] ?? $_SESSION['current_product_id'] ?? null;

if (!$product_id) {
    echo "Seller information not available.";
    $conn->close();
    return;
}

$sql = "SELECT s.shop_about
        FROM products p
        JOIN sellers s ON p.seller_id = s.id
        WHERE p.product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $aboutText = $row['shop_about'] ?? '';
    echo empty(trim($aboutText))
        ? "This seller hasn't provided any information yet."
        : nl2br(htmlspecialchars($aboutText));
} else {
    echo "Seller information not available.";
}

$conn->close();
?>
