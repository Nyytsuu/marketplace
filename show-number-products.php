<?php
// Get seller ID from session
$seller_id = $_SESSION['user_id'] ?? null;

if (!$seller_id) {
    echo "0";
    return;
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count products for the current seller
    $sql = "SELECT COUNT(*) as product_count FROM products WHERE seller_id = :seller_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $result['product_count'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Database error in show-number-products.php: " . $e->getMessage());
    echo "0";
}
?>