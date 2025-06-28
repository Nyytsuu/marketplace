<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get filters from GET
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$subcategory = isset($_GET['sub_category']) ? $conn->real_escape_string($_GET['sub_category']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

// Pagination settings
$limit = 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Build WHERE clause for filters
$where = [];
if ($category !== '') {
    $where[] = "category = '$category'";
}
if ($subcategory !== '') {
    $where[] = "sub_category = '$subcategory'";
}
$whereSql = count($where) ? "WHERE " . implode(' AND ', $where) : "";

// Get total filtered products count
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM products $whereSql");
$totalRow = $totalResult->fetch_assoc();
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $limit);

// Build ORDER BY clause for sorting
switch ($sort) {
    case 'price_asc':
        $orderBy = "ORDER BY price ASC";
        break;
    case 'price_desc':
        $orderBy = "ORDER BY price DESC";
        break;
    case 'top_sales':
        $orderBy = "ORDER BY sales DESC"; // assuming you have 'sales' column
        break;
    case 'latest':
    default:
        $orderBy = "ORDER BY created_at DESC"; // assuming you have 'created_at' timestamp column
        break;
}

// Fetch products with filters and sorting
$sql = "SELECT * FROM products $whereSql $orderBy LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="product-card">';
        echo '<a href="productview.php?id=' . $row['product_id'] . '" class="product-card">';
        echo '<img src="' . $row['main_image'] . '" alt="' . htmlspecialchars($row['product_name']) . '">';
        echo '<div class="product-name">';
        echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3></div>';
        echo '<div class="product-price">';
        echo '<p>₱' . number_format($row['price'], 2) . '</p>';
        echo '</div></a></div>';
    }
} else {
    echo '<p>No products found.</p>';
}

// Include pagination with current filters in the URL
$baseUrl = "?category=" . urlencode($category) . "&sub_category=" . urlencode($subcategory) . "&sort=" . urlencode($sort) . "&page=";
include 'pagination.php';
renderPagination($page, $totalPages, $baseUrl);

?>
