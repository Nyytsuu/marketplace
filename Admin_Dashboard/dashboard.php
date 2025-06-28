<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the admin is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: ../Admin_Dashboard/dashboard.php");
    exit();
}

$Username = $_SESSION['Username']; 

$query = "SELECT * FROM admin_signin WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $Username);  
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    die("Database error: " . mysqli_error($conn));
}

// Dashboard metrics calculations
// 1. Calculate Total Revenue
$totalRevenue = 0;
$revenueQuery = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'";
$revenueResult = mysqli_query($conn, $revenueQuery);
if ($revenueResult && $revenueRow = mysqli_fetch_assoc($revenueResult)) {
    $totalRevenue = $revenueRow['total_revenue'] ?? 0;
}

// 2. Calculate Orders This Week
$ordersThisWeek = 0;
$weekQuery = "SELECT COUNT(*) as week_orders FROM orders WHERE WEEK(order_date) = WEEK(NOW()) AND YEAR(order_date) = YEAR(NOW())";
$weekResult = mysqli_query($conn, $weekQuery);
if ($weekResult && $weekRow = mysqli_fetch_assoc($weekResult)) {
    $ordersThisWeek = $weekRow['week_orders'] ?? 0;
}

// 3. Calculate Total Users (buyers + sellers + drivers)
$totalUsers = 0;

// Count buyers
$buyersQuery = "SELECT COUNT(*) as buyer_count FROM buyers";
$buyersResult = mysqli_query($conn, $buyersQuery);
$buyerCount = 0;
if ($buyersResult && $buyerRow = mysqli_fetch_assoc($buyersResult)) {
    $buyerCount = $buyerRow['buyer_count'] ?? 0;
}

// Count sellers
$sellersQuery = "SELECT COUNT(*) as seller_count FROM sellers";
$sellersResult = mysqli_query($conn, $sellersQuery);
$sellerCount = 0;
if ($sellersResult && $sellerRow = mysqli_fetch_assoc($sellersResult)) {
    $sellerCount = $sellerRow['seller_count'] ?? 0;
}

// Count drivers - FIX: Actually count the drivers
$driversQuery = "SELECT COUNT(*) as driver_count FROM drivers";
$driversResult = mysqli_query($conn, $driversQuery);
$driverCount = 0;
if ($driversResult && $driverRow = mysqli_fetch_assoc($driversResult)) {
    $driverCount = $driverRow['driver_count'] ?? 0;
}

// Get driver details for display
$driverDetailsQuery = "SELECT driver_id, full_name, email, phone_number, vehicle_info, status, date_joined FROM drivers ORDER BY date_joined DESC LIMIT 10";
$driverDetailsResult = mysqli_query($conn, $driverDetailsQuery);
$driverDetails = [];
if ($driverDetailsResult) {
    while ($row = mysqli_fetch_assoc($driverDetailsResult)) {
        $driverDetails[] = $row;
    }
}

// Get recent buyers
$recentBuyersQuery = "SELECT first_name, last_name, email, date_joined FROM buyer_signup ORDER BY date_joined DESC LIMIT 10";
$recentBuyersResult = mysqli_query($conn, $recentBuyersQuery);
$recentBuyers = [];
if ($recentBuyersResult) {
    while ($row = mysqli_fetch_assoc($recentBuyersResult)) {
        $recentBuyers[] = $row;
    }
}

// Get recent sellers
$recentSellersQuery = "SELECT fullname, email, created_at FROM seller_signup ORDER BY created_at DESC LIMIT 10";
$recentSellersResult = mysqli_query($conn, $recentSellersQuery);
$recentSellers = [];
if ($recentSellersResult) {
    while ($row = mysqli_fetch_assoc($recentSellersResult)) {
        $recentSellers[] = $row;
    }
}



$totalUsers = $buyerCount + $sellerCount + $driverCount;

// 4. Get Top Selling Products - FIX: Added error checking and alternative query
$topProductsQuery = "
    SELECT p.product_name, p.price, SUM(oi.quantity) as total_sold
    FROM products p
    INNER JOIN order_items oi ON p.product_id = oi.product_id
    INNER JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status = 'completed'
    GROUP BY p.product_id, p.product_name, p.price
    ORDER BY total_sold DESC
    LIMIT 3
";
$topProductsResult = mysqli_query($conn, $topProductsQuery);
$topProducts = [];

if ($topProductsResult) {
    while ($row = mysqli_fetch_assoc($topProductsResult)) {
        $topProducts[] = $row;
    }
} else {
    // Alternative query if the main one fails - get products with most recent orders
    $altProductsQuery = "
        SELECT p.product_name, p.price, COUNT(oi.product_id) as total_sold
        FROM products p
        LEFT JOIN order_items oi ON p.product_id = oi.product_id
        GROUP BY p.product_id, p.product_name, p.price
        ORDER BY total_sold DESC
        LIMIT 3
    ";
    $altResult = mysqli_query($conn, $altProductsQuery);
    if ($altResult) {
        while ($row = mysqli_fetch_assoc($altResult)) {
            $topProducts[] = $row;
        }
    }
}

// 5. Get Top Sellers by Revenue - FIX: Added error checking and alternative query
$topSellersQuery = "
    SELECT s.store_name, SUM(oi.quantity * oi.price) as total_revenue
    FROM sellers s
    INNER JOIN products p ON s.seller_id = p.seller_id
    INNER JOIN order_items oi ON p.product_id = oi.product_id
    INNER JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status = 'completed'
    GROUP BY s.seller_id, s.store_name
    ORDER BY total_revenue DESC
    LIMIT 3
";
$topSellersResult = mysqli_query($conn, $topSellersQuery);
$topSellers = [];

if ($topSellersResult) {
    while ($row = mysqli_fetch_assoc($topSellersResult)) {
        $topSellers[] = $row;
    }
} else {
    // Alternative query if the main one fails - get sellers with most products
    $altSellersQuery = "
        SELECT s.store_name, COUNT(p.product_id) as product_count, 
               AVG(p.price) as avg_price,
               (COUNT(p.product_id) * AVG(p.price)) as estimated_revenue
        FROM sellers s
        LEFT JOIN products p ON s.seller_id = p.seller_id
        GROUP BY s.seller_id, s.store_name
        ORDER BY estimated_revenue DESC
        LIMIT 3
    ";
    $altResult = mysqli_query($conn, $altSellersQuery);
    if ($altResult) {
        while ($row = mysqli_fetch_assoc($altResult)) {
            $topSellers[] = [
                'store_name' => $row['store_name'],
                'total_revenue' => $row['estimated_revenue'] ?? 0
            ];
        }
    }
}

// Debug: Check if we have data
// Uncomment these lines to debug
// echo "<!-- Debug: Top Products Count: " . count($topProducts) . " -->";
// echo "<!-- Debug: Top Sellers Count: " . count($topSellers) . " -->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../Admin_Dashboard/dashboard.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <title>Dashboard Admin</title>
</head>
<body>
  <div class="grid-container">
       <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
      <div class="sidebar-title">
        <div class="sidebar-brand">
          <img src="../5. pictures/admin.png" alt="Logo" class="logo">
        </div>
      </div>

      <ul class="sidebar-list">
        <div class="sidebar-line"></div>

<li class="sidebar-list-item dashboard-item">
    <a href="../Admin_Dashboard/dashboard.php">
    <span class="material-icons-outlined">dashboard</span>
    Dashboard
  </a>
</li>

<li class="sidebar-list-item active">
  <a href="../Admin_User_management/user-management.php">
    <span class="material-icons-outlined">inventory_2</span>
    User Management
  </a>
</li>

<li class="sidebar-list-item">
  <a href="../Admin_order_managment/ordermanagement.php">
    <span class="material-icons-outlined">add_shopping_cart</span>
    Order Management
  </a>
</li>

<li class="sidebar-list-item">
  <a href="../Admin_Category_management/categorymanagement.php">
    <span class="material-icons-outlined">shopping_cart</span>
    Category Management
  </a>
</li>
<li class="sidebar-list-item">
  <a href="../Admin_Withdrawal/withdrawal.php">
    <span class="material-icons-outlined">poll</span>
    Withdrawal Management
  </a>
</li>
<li class="sidebar-list-item">
  <a href="../Admin_Delivery_Management/delivery_man.php">
    <span class="material-icons-outlined">local_shipping</span>
    Delivery Management
  </a>
</li>
      </ul>
    </aside>

    <!-- Header -->
    <header class="header">
      <div class="relative flex items-center space-x-4">
        <i class='bx bxs-user-circle text-3xl'></i>
        <div class="text-sm text-right">
          <p class="font-medium">@<?php echo htmlspecialchars($admin['Username']); ?></p>
          <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($admin['AdminID']); ?></p>
        </div>
        <i class='bx bx-chevron-down cursor-pointer text-xl' id="dropdownToggle"></i>
        <div class="absolute top-12 right-0 bg-white shadow rounded hidden" id="logoutPopup">
          <button class="block px-4 py-2 text-sm hover:bg-gray-100" id="logoutBtn">Logout</button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
<main class="content">
  <!-- Top Metrics Row -->
  <div class="metrics-row">
    <div class="metric-card revenue-card">
      <h4>Total Revenue</h4>
      <p>₱<?php echo number_format($totalRevenue, 2); ?></p>
    </div>
    <div class="metric-card order-card">
      <h4>Order This Week</h4>
      <p><?php echo $ordersThisWeek; ?></p>
    </div>
    <div class="metric-card users-card">
      <h4>Total Users</h4>
      <p><?php echo $totalUsers; ?></p>
      <small class="text-gray-500">
        Buyers: <?php echo $buyerCount; ?> | 
        Sellers: <?php echo $sellerCount; ?> | 
        Drivers: <?php echo $driverCount; ?>
      </small>
    </div>
  </div>

  <!-- Widgets Container -->
  <div class="widgets-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
    <!-- Top Selling Products -->
    <div class="widget-card">
      <h4><i class="material-icons-outlined" style="vertical-align: middle; margin-right: 8px;">trending_up</i>Top Selling Products</h4>
      <div style="margin-top: 15px;">
        <?php if (empty($topProducts)): ?>
          <div style="text-align: center; padding: 20px; color: #666;">
            <i class="material-icons-outlined" style="font-size: 48px; color: #ddd;">inventory_2</i>
            <p>No sales data available</p>
          </div>
        <?php else: ?>
          <?php foreach ($topProducts as $index => $product): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; margin-bottom: 8px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;">
              <div style="display: flex; align-items: center;">
                <span style="background: #007bff; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; margin-right: 12px;">
                  <?php echo $index + 1; ?>
                </span>
                <div>
                  <div style="font-weight: 600; color: #333; margin-bottom: 2px;">
                    <?php echo htmlspecialchars($product['product_name']); ?>
                  </div>
                  <small style="color: #666;">
                    <?php echo $product['total_sold']; ?> sold
                  </small>
                </div>
              </div>
              <div style="text-align: right;">
                <div style="font-weight: bold; color: #28a745;">
                  ₱<?php echo number_format($product['price'], 2); ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Top Sellers by Revenue -->
    <div class="widget-card">
      <h4><i class="material-icons-outlined" style="vertical-align: middle; margin-right: 8px;">store</i>Top Sellers by Revenue</h4>
      <div style="margin-top: 15px;">
        <?php if (empty($topSellers)): ?>
          <div style="text-align: center; padding: 20px; color: #666;">
            <i class="material-icons-outlined" style="font-size: 48px; color: #ddd;">store</i>
            <p>No sales data available</p>
          </div>
        <?php else: ?>
          <?php foreach ($topSellers as $index => $seller): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; margin-bottom: 8px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #6f42c1;">
              <div style="display: flex; align-items: center;">
                <span style="background: #6f42c1; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; margin-right: 12px;">
                  <?php echo $index + 1; ?>
                </span>
                <div>
                  <div style="font-weight: 600; color: #333; margin-bottom: 2px;">
                    <?php echo htmlspecialchars($seller['store_name']); ?>
                  </div>
                  <small style="color: #666;">Store Revenue</small>
                </div>
              </div>
              <div style="text-align: right;">
                <div style="font-weight: bold; color: #28a745;">
                  ₱<?php echo number_format($seller['total_revenue'], 2); ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Drivers -->
  <div class="widget-card" style="margin-top: 20px;">
    <h4><i class="material-icons-outlined" style="vertical-align: middle; margin-right: 8px;">local_shipping</i>Recent Drivers</h4>
    <div style="margin-top: 15px;">
      <?php if (empty($driverDetails)): ?>
        <div style="text-align: center; padding: 20px; color: #666;">
          <i class="material-icons-outlined" style="font-size: 48px; color: #ddd;">local_shipping</i>
          <p>No drivers registered yet</p>
        </div>
      <?php else: ?>
        <?php foreach ($driverDetails as $driver): ?>
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; margin-bottom: 8px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #fd7e14;">
            <div style="display: flex; align-items: center;">
              <div style="background: linear-gradient(135deg, #fd7e14, #ff6b35); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px;">
                <?php echo strtoupper(substr($driver['full_name'], 0, 1)); ?>
              </div>
              <div>
                <div style="font-weight: 600; color: #333; margin-bottom: 2px;">
                  <?php echo htmlspecialchars($driver['full_name']); ?>
                </div>
                <small style="color: #666;">
                  <?php echo htmlspecialchars($driver['email']); ?>
                </small>
              </div>
            </div>
            <div style="text-align: right;">
              <span style="<?php echo $driver['status'] === 'Active' ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?> padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                <?php echo htmlspecialchars($driver['status']); ?>
              </span>
              <div style="font-size: 11px; color: #666; margin-top: 4px;">
                Joined <?php echo date('M d, Y', strtotime($driver['date_joined'])); ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

    <!-- Recent Buyers -->

<div class="widget-card" style="margin-top: 20px;">
  <h4><i class="material-icons-outlined" style="vertical-align: middle; margin-right: 8px;">person</i>Recent Buyers</h4>
  <div style="margin-top: 15px;">
    <?php if (empty($recentBuyers)): ?>
      <div style="text-align: center; padding: 20px; color: #666;">
        <i class="material-icons-outlined" style="font-size: 48px; color: #ddd;">person</i>
        <p>No buyers registered yet</p>
      </div>
    <?php else: ?>
      <?php foreach ($recentBuyers as $buyer): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; margin-bottom: 8px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">
          <div style="display: flex; align-items: center;">
            <div style="background: linear-gradient(135deg, #17a2b8, #138496); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px;">
              <?php echo strtoupper(substr($buyer['first_name'], 0, 1)); ?>
            </div>
            <div>
              <div style="font-weight: 600; color: #333; margin-bottom: 2px;">
                <?php echo htmlspecialchars($buyer['first_name'] . ' ' . $buyer['last_name']); ?>
              </div>
              <small style="color: #666;"><?php echo htmlspecialchars($buyer['email']); ?></small>
            </div>
          </div>
          <div style="font-size: 11px; color: #666;">
            Joined <?php echo date('M d, Y', strtotime($buyer['date_joined'])); ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

  <!-- Recent sellers -->
<div class="widget-card" style="margin-top: 20px;">
  <h4><i class="material-icons-outlined" style="vertical-align: middle; margin-right: 8px;">store</i>Recent Sellers</h4>
  <div style="margin-top: 15px;">
    <?php if (empty($recentSellers)): ?>
      <div style="text-align: center; padding: 20px; color: #666;">
        <i class="material-icons-outlined" style="font-size: 48px; color: #ddd;">store</i>
        <p>No sellers registered yet</p>
      </div>
    <?php else: ?>
      <?php foreach ($recentSellers as $seller): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; margin-bottom: 8px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745;">
          <div style="display: flex; align-items: center;">
            <div style="background: linear-gradient(135deg, #28a745, #218838); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px;">
              <?php echo strtoupper(substr($seller['fullname'], 0, 1)); ?>
            </div>
            <div>
              <div style="font-weight: 600; color: #333; margin-bottom: 2px;">
                <?php echo htmlspecialchars($seller['fullname']); ?>
              </div>
              <small style="color: #666;"><?php echo htmlspecialchars($seller['email']); ?></small>
            </div>
          </div>
          <div style="font-size: 11px; color: #666;">
            Joined <?php echo date('M d, Y', strtotime($seller['created_at'])); ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>



</main>
  </div>

  <!-- JS Scripts -->
  <script>
    const toggleBtn = document.getElementById('dropdownToggle');
    const popup = document.getElementById('logoutPopup');

    toggleBtn.addEventListener('click', () => {
      popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
    });

    window.addEventListener('click', function(e) {
      if (!toggleBtn.contains(e.target) && !popup.contains(e.target)) {
        popup.style.display = 'none';
      }
    });

    document.getElementById('logoutBtn').addEventListener('click', () => {
      if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../Admin_Dashboard/logout.php';
      }
    });
  </script>
</body>
</html>