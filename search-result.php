<?php
session_start();

// Grab the search term (if any)
$search_query = trim($_GET['search'] ?? '');

// Pagination setup
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Fetch matching products
$products = [];
$totalProducts = 0;

if ($search_query !== '') {
    $conn = new mysqli("localhost", "root", "", "marketplace");
    if ($conn->connect_error) {
        die("DB Error: " . $conn->connect_error);
    }
    $like = "%{$search_query}%";

    // Count total matches
    $countStmt = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM products
        WHERE product_name LIKE ? OR description LIKE ?
    ");
    $countStmt->bind_param("ss", $like, $like);
    $countStmt->execute();
    $totalProducts = $countStmt->get_result()->fetch_assoc()['cnt'];
    $countStmt->close();

    // Page results
    $dataStmt = $conn->prepare("
        SELECT product_id, product_name, price, main_image
        FROM products
        WHERE product_name LIKE ? OR description LIKE ?
        ORDER BY product_name
        LIMIT ? OFFSET ?
    ");
    $dataStmt->bind_param("ssii", $like, $like, $perPage, $offset);
    $dataStmt->execute();
    $res = $dataStmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
    $dataStmt->close();
    $conn->close();
}

// Calculate total pages
$totalPages = $perPage ? (int)ceil($totalProducts / $perPage) : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - Marketplace</title>
    <link rel="stylesheet" href="search.css"> <!-- Link to your CSS file -->
</head>
<body>
<header>
    <h1>Marketplace</h1>
    <form action="search-results.php" method="get" class="search-bar">
        <input type="text" name="search" placeholder="Search for products" value="<?= htmlspecialchars($search_query) ?>" required>
        <button type="submit">Search</button>
    </form>
</header>

<div class="results-header">
    <h2>Search Results</h2>
    <?php if (!empty($search_query)): ?>
        <p>Showing results for: <strong><?= htmlspecialchars($search_query) ?></strong></p>
        <p><?= $totalProducts ?> product(s) found</p>
    <?php endif; ?>
</div>

<div class="products-grid">
    <?php if ($totalProducts > 0): ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($product['main_image'] ?? 'pics/placeholder.jpg') ?>" 
                     alt="<?= htmlspecialchars($product['product_name']) ?>" 
                     class="product-image"
                     onerror="this.src='pics/placeholder.jpg'">
                <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                <a href="product-view.php?id=<?= $product['product_id'] ?>" class="view-product-btn">View Product</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        $baseUrl = "search-results.php?search=" . urlencode($search_query) . "&page=";
        for ($i = 1; $i <= $totalPages; $i++) {
            $activeClass = $i === $page ? 'active' : '';
            echo "<a href='{$baseUrl}{$i}' class='pagination-link $activeClass'>$i</a> ";
        }
        ?>
    </div>
<?php endif; ?>

</body>
</html>
