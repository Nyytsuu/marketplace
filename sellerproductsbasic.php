<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'marketplace';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productId = $_POST['product_id'] ?? null;
    $productName = $_POST['product_name'] ?? '';
    $description = $_POST['product_description'] ?? '';
    $category = $_POST['category'] ?? '';

    $sellerId = $_SESSION['user_id'] ?? null;

    if (!$sellerId || !$productId) {
        die("Seller not logged in or product ID missing.");
    }

    // Update product
    $stmt = $pdo->prepare("UPDATE products SET product_name = ?, product_description = ?, category = ? WHERE product_id = ? AND seller_id = ?");
    $stmt->execute([$productName, $description, $category, $productId, $sellerId]);

    // Optional: Save product_id to session (for image upload page)
    $_SESSION['product_id'] = $productId;

    echo "<h2>Product Updated Successfully</h2>";
    echo "<p><strong>Product Name:</strong> " . htmlspecialchars($productName) . "</p>";
    echo "<p><strong>Description:</strong> " . htmlspecialchars($description) . "</p>";
    echo "<p><strong>Category:</strong> " . htmlspecialchars($category) . "</p>";
    echo '<a href="sellerproductss.php">Back to Products</a>';
} else {
    echo "<p>Invalid request.</p>";
}
?>
