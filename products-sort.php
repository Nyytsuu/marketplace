<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// URL Parameters
$sort = $_GET['sort'] ?? 'latest';
$category = $_GET['category'] ?? '';
$priceRange = $_GET['price'] ?? '';

// Sorting
switch ($sort) {
    case 'top_sales': $orderBy = 'sales DESC'; break;
    case 'price_asc': $orderBy = 'price ASC'; break;
    case 'price_desc': $orderBy = 'price DESC'; break;
    case 'latest':
    default: $orderBy = 'created_at DESC'; break;
}

//// Get URL parameters
$subcategory = $_GET['sub_category'] ?? '';

// Existing filters
$where = "1=1";
if (!empty($category)) {
    $safeCategory = $conn->real_escape_string($category);
    $where .= " AND category = '$safeCategory'";
}

// Add this to handle subcategory
if (!empty($subcategory)) {
    $safeSubcategory = $conn->real_escape_string($subcategory);
    $where .= " AND sub_category = '$safeSubcategory'";
}

if (!empty($priceRange) && strpos($priceRange, '-') !== false) {
    list($min, $max) = explode('-', $priceRange);
    $where .= " AND price BETWEEN " . intval($min) . " AND " . intval($max);
}

?>
