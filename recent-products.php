<?php
// Get seller ID from session
$seller_id = $_SESSION['user_id'] ?? null;

if (!$seller_id) {
    echo '<p style="color: #666; text-align: center;">Please log in to view your products.</p>';
    return;
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get recent products for the logged-in seller only
    $sql = "
        SELECT 
            product_id,
            product_name,
            price,
            main_image,
            created_at
        FROM products 
        WHERE seller_id = :seller_id 
        ORDER BY created_at DESC 
        LIMIT 5
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    $recentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($recentProducts)) {
        echo '<div class="no-data">
                <p>No products added yet.</p>
                <a href="sellerproductss.php" class="add-product-btn">Add Your First Product</a>
              </div>';
    } else {
      foreach ($recentProducts as $product) {
  echo '
  <div class="recent-product-container">
  <div class="recent-product-item">
    <div class="item">
      <div class="product-image">
        <img src="' . htmlspecialchars($product['main_image']) . '" alt="' . htmlspecialchars($product['product_name']) . '" height="50" width="50">
      </div>
      <div class="item-details">
        <p>' . htmlspecialchars($product['product_name']) . '</p>
        <p class="product-price"><strong>₱' . number_format($product['price'], 2) . '</strong></p>
        <p class="product-date">' . date('M j, Y') . '</p>
      </div>
    </div>
  </div>';
}
        
        // View all products link
        echo '<div class="view-all">
                <a href="sellerproductss.php">View All Products →</a>
              </div>';
    }

} catch (PDOException $e) {
    echo '<div class="error-message">
            <p>Error loading recent products: ' . htmlspecialchars($e->getMessage()) . '</p>
          </div>';
}
?>

<style>
.recent-products-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 300px;
    overflow-y: auto;
}

.recent-product-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: background-color 0.2s;
    margin-top: 10px;
}

.recent-product-item:hover {
    background: #e9ecef;
}

.product-image {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details {
    flex: 1;
    min-width: 0;
}

.product-details h4 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-price {
    margin: 0 0 2px 0;
    font-size: 13px;
    font-weight: 600;
    color: #007bff;
}

.product-date {
    margin: 0;
    font-size: 11px;
    color: #666;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: #666;
}

.add-product-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 16px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.2s;
}

.add-product-btn:hover {
    background: #0056b3;
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