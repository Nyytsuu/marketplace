<?php
session_start();

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die(json_encode(['error' => 'DB connection failed']));
}

$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    die(json_encode(['error' => 'Seller not logged in']));
}

// 1. Total Sales
$stmtSales = $conn->prepare("
    SELECT COALESCE(SUM(total_price), 0) AS total_sales
    FROM orders
    WHERE user_id = ? AND status = 'delivered'
");
$stmtSales->bind_param("i", $seller_id);
$stmtSales->execute();
$resultSales = $stmtSales->get_result();
$totalSales = $resultSales->fetch_assoc()['total_sales'] ?? 0;
$stmtSales->close();

// 2. Total Orders
$stmtOrders = $conn->prepare("
    SELECT COUNT(*) AS total_orders
    FROM orders
    WHERE user_id = ? AND status = 'delivered'
");
$stmtOrders->bind_param("i", $seller_id);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();
$totalOrders = $resultOrders->fetch_assoc()['total_orders'] ?? 0;
$stmtOrders->close();

// 3. Total Products Sold
$stmtProducts = $conn->prepare("
    SELECT COALESCE(SUM(oi.quantity), 0) AS total_products_sold
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.user_id = ? AND o.status = 'delivered'
");
$stmtProducts->bind_param("i", $seller_id);
$stmtProducts->execute();
$resultProducts = $stmtProducts->get_result();
$totalProductsSold = $resultProducts->fetch_assoc()['total_products_sold'] ?? 0;
$stmtProducts->close();

// 4. New Customers (distinct customer_id with delivered orders in last 30 days)
$stmtNewCustomers = $conn->prepare("
    SELECT COUNT(DISTINCT user_id) AS new_customers
    FROM orders
    WHERE user_id = ? AND status = 'delivered' AND order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
");
$stmtNewCustomers->bind_param("i", $seller_id);
$stmtNewCustomers->execute();
$resultNewCustomers = $stmtNewCustomers->get_result();
$newCustomers = $resultNewCustomers->fetch_assoc()['new_customers'] ?? 0;
$stmtNewCustomers->close();

// 5. Monthly Sales (grouped by month)
$stmtMonthlySales = $conn->prepare("
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, COALESCE(SUM(total_price), 0) AS total
    FROM orders
    WHERE user_id = ? AND status = 'delivered'
    GROUP BY month
    ORDER BY month ASC
");
$stmtMonthlySales->bind_param("i", $seller_id);
$stmtMonthlySales->execute();
$resultMonthlySales = $stmtMonthlySales->get_result();
$monthlySales = [];
while ($row = $resultMonthlySales->fetch_assoc()) {
    $monthlySales[] = ['month' => $row['month'], 'total' => (float)$row['total']];
}
$stmtMonthlySales->close();

// 6. Monthly Orders (grouped by month)
$stmtMonthlyOrders = $conn->prepare("
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, COUNT(*) AS total
    FROM orders
    WHERE user_id = ? AND status = 'delivered'
    GROUP BY month
    ORDER BY month ASC
");
$stmtMonthlyOrders->bind_param("i", $seller_id);
$stmtMonthlyOrders->execute();
$resultMonthlyOrders = $stmtMonthlyOrders->get_result();
$monthlyOrders = [];
while ($row = $resultMonthlyOrders->fetch_assoc()) {
    $monthlyOrders[] = ['month' => $row['month'], 'total' => (int)$row['total']];
}
$stmtMonthlyOrders->close();

// 7. Monthly Products Sold (grouped by month)
$stmtMonthlyProducts = $conn->prepare("
    SELECT DATE_FORMAT(o.order_date, '%Y-%m') AS month, COALESCE(SUM(oi.quantity), 0) AS total
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.user_id = ? AND o.status = 'delivered'
    GROUP BY month
    ORDER BY month ASC
");
$stmtMonthlyProducts->bind_param("i", $seller_id);
$stmtMonthlyProducts->execute();
$resultMonthlyProducts = $stmtMonthlyProducts->get_result();
$monthlyProductsSold = [];
while ($row = $resultMonthlyProducts->fetch_assoc()) {
    $monthlyProductsSold[] = ['month' => $row['month'], 'total' => (int)$row['total']];
}
$stmtMonthlyProducts->close();

// 8. Monthly New Customers (count distinct customer_id per month)
$stmtMonthlyNewCustomers = $conn->prepare("
    SELECT month, COUNT(DISTINCT user_id) AS total FROM (
        SELECT user_id, DATE_FORMAT(order_date, '%Y-%m') AS month
        FROM orders
        WHERE user_id = ? AND status = 'delivered'
    ) AS sub
    GROUP BY month
    ORDER BY month ASC
");
$stmtMonthlyNewCustomers->bind_param("i", $seller_id);
$stmtMonthlyNewCustomers->execute();
$resultMonthlyNewCustomers = $stmtMonthlyNewCustomers->get_result();
$monthlyNewCustomers = [];
while ($row = $resultMonthlyNewCustomers->fetch_assoc()) {
    $monthlyNewCustomers[] = ['month' => $row['month'], 'total' => (int)$row['total']];
}
$stmtMonthlyNewCustomers->close();

$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'totalSales' => (float)$totalSales,
    'totalOrders' => (int)$totalOrders,
    'totalProductsSold' => (int)$totalProductsSold,
    'newCustomers' => (int)$newCustomers,
    'monthlySales' => $monthlySales,
    'monthlyOrders' => $monthlyOrders,
    'monthlyProductsSold' => $monthlyProductsSold,
    'monthlyNewCustomers' => $monthlyNewCustomers,
]);
?>