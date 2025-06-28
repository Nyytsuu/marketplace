<?php
// Get seller ID from session
$seller_id = $_SESSION['user_id'] ?? null;

if (!$seller_id) {
    return []; // Return empty array if no seller logged in
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query to get products only for the current seller
    $sql = "SELECT 
                product_id,
                product_name,
                price,
                main_image,
                seller_id,
                stocks,
                created_at
            FROM products 
            WHERE seller_id = :seller_id 
            ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $products;
    
} catch (PDOException $e) {
    error_log("Database error in showproductimage.php: " . $e->getMessage());
    return []; // Return empty array on error
}
?>