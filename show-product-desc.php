<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product_id = $_GET['id'] ?? null;  // GET the product ID from the URL

if (!$product_id) {
    echo ''; // Nothing to display
    return;
}

$sql = "SELECT product_description FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$productDescriptionText = '';

if ($row = $result->fetch_assoc()) {
    $productDescriptionText = $row['product_description'] ?? '';
}

$conn->close();

// Echo the about text directly
echo nl2br(htmlspecialchars($productDescriptionText));
?>
