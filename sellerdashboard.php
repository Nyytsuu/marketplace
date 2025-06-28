<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();

}
$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    die("Seller not logged in.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="seller-dashboard4.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <title>Seller Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>  
  <div class="container"> 
  <div class="grid-container">   
     <div class="header-container">
            <!-- Search bar in the middle -->
      <form action="#" method="get" class="search-bar">
        <input type="text" name="search" placeholder="Search for products">
        <i class="bx bx-search"></i>    
      </form>

      <!-- Icons moved to the left -->
      <div class="icons">
        <button type="button" class="profile-btn" onclick="window.location.href='sellerprofilenew.php'">
          <i class="bx bx-user"></i>
        </button>
        <span class="icon-separator">|</span>
        <button type="button" class="edit-dp-btn" onclick="window.location.href='editprofile.php'">
          <i class="bx bx-cog"></i>
        </button>
      </div>

      <!-- Profile section on the right -->
      <div class="profile-wrapper">
        <div class="dp-container">
          <?php include 'get_profile_image.php'; ?>
        </div>
        <div class="user-info">
          <?php include 'show-user-info.php'; ?>
        </div>
        <div class="dropdown">
          <button type="button" class="dropdown-btn" onclick="toggleDropdown()">
            <i class="bx bx-chevron-down"></i>
          </button>
          <div class="dropdown-content" id="dropdownMenu">
            <a href="signin.php">
              <i class="bx bx-log-out"></i>
              <span>Logout</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  <aside class="sidebar">
            <div class="logo">
                <img src="pics/seller.png" alt="cartit-logo" width="205px">
            </div>
            <p class="menu-title">MAIN MENU</p>
            <nav class="menu">
                <ul class="sidebar-list">

                    <li class="sidebar-list-item">
                        <a href="sellerdashboard.php" class="menu-item active">
                            <i class="bx bx-home"></i> 
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="sellershop.php" class="menu-item">
                            <i class="bx bx-store"></i> 
                            <span>Shop</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="sellerproductss.php" class="menu-item">
                            <i class="bx bx-cart-add"></i> 
                            <span>Products</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="seller.orders.php" class="menu-item">
                            <i class="bx bx-package"></i> 
                            <span>Orders</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="seller-transactions.php" class="menu-item">
                            <i class="bx bx-credit-card"></i> 
                            <span>Transaction</span>
                        </a>
                    </li>

                </ul>
            </nav>
        </aside>

        <main class="main-container">

            <section class="heads">
              <h2>Welcome back, <?php include 'show-user-info.php' ?></h2>
              <p>Here are the results of the statistics<p>
            </section>
          <div class="dashboard-top">
    <section class="stats">
      <!-- Stats cards -->
<div class="card">
  <p>Total Sales</p>
  <div id="totalSalesValue">₱0.00</div>
  <canvas id="salesChart" width="150" height="50"></canvas>
</div>

<div class="card">
  <p>Total Orders</p>
  <div id="totalOrdersValue">0</div>
  <canvas id="ordersChart" width="150" height="50"></canvas>
</div>

<div class="card">
  <p>Total Products Sold</p>
  <div id="totalProductsSoldValue">0</div>
  <canvas id="productsChart" width="150" height="50"></canvas>
</div>

<div class="card">
  <p>Monthly Sales</p>
  <div id="monthlySalesValue">₱0.00</div>
  <canvas id="monthlySalesChart" width="150" height="50"></canvas>
</div>

<div class="card">
  <p>Monthly Orders</p>
  <div id="monthlyOrdersValue">0</div>
  <canvas id="monthlyOrdersChart" width="150" height="50"></canvas>
</div>

<div class="card">
  <p>Monthly Products Sold</p>
  <div id="monthlyProductsSoldValue">0</div>
  <canvas id="monthlyProductsSoldChart" width="150" height="50"></canvas>
</div>

<div class="card">
  <p>New Customers</p>
  <div id="newCustomersValue">0</div>
  <canvas id="customersChart" width="700" height="50"></canvas>
</div>

    </section>
   
    <!-- Right column beside stats -->
    <div class="right-column">
      <section class="recent-prod">
        <div class="recent">
          <h3>Recent Product</h3>
         <?php include 'recent-products.php'?>
        </div>
      </section>

      <section class="recent-order">
        <div class="recent">
          <h3><span>Recent Orders</span></h3>
          <?php include 'recent-order.php'?>
        </div>
      </section>
    </div>
  </div>


          <section class="tables">
  <div class="left">
    <h3>Top selling products</h3>
    <table class="top-products-table">
      <thead>
        <tr>
          <th>Product ID</th>
          <th>Product Name</th>
          <th>Stocks</th>
          <th>Total Orders</th>
          <th>Total Sales</th>
          <th>Revenue</th>
        </tr>
      </thead>
    <tbody>
<?php
try {
    // DB connection
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL: Top 5 best-selling products for the current seller only
    $sql = "
      SELECT 
        p.product_id,
        p.product_name,
        p.stocks,
        p.main_image,
        p.price,
        COALESCE(SUM(oi.quantity), 0) AS total_sales,
        COALESCE(COUNT(DISTINCT oi.order_id), 0) AS total_orders,
        COALESCE(SUM(oi.quantity * oi.price), 0) AS total_revenue
      FROM products p
      LEFT JOIN order_items oi ON p.product_id = oi.product_id
      WHERE p.seller_id = :seller_id
      GROUP BY p.product_id, p.product_name, p.stocks, p.main_image, p.price
      ORDER BY total_sales DESC, total_revenue DESC
      LIMIT 5
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($topProducts) > 0) {
        foreach ($topProducts as $product) {
            echo '<tr>
                <td>#' . htmlspecialchars($product['product_id']) . '</td>
                <td class="product-info">
                  <img src="' . htmlspecialchars($product['main_image']) . '" alt="' . htmlspecialchars($product['product_name']) . '" onerror="this.src=\'pics/default-product.png\'">
                  <span>' . htmlspecialchars($product['product_name']) . '</span>
                </td>
                <td>' . (int)$product['stocks'] . '</td>
                <td>' . (int)$product['total_orders'] . '</td>
                <td>' . (int)$product['total_sales'] . '</td>
                <td>₱' . number_format((float)$product['total_revenue'], 2) . '</td>
              </tr>';
        }
    } else {
        echo '<tr><td colspan="6" class="no-data">No products found or no sales yet.</td></tr>';
    }
} catch (PDOException $e) {
    echo '<tr><td colspan="6" class="error-message">Error loading data: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
}
?>
</tbody>
    </table>
  </div>
</section>

            
  </div>
        </main>
   </div>

    <script>
fetch('dashboard_metrics.php')
  .then(res => res.json())
  .then(data => {
    if (data.error) {
      console.error(data.error);
      return;
    }

    // Set summary numbers
    document.getElementById('totalSalesValue').textContent = `₱${data.totalSales.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    document.getElementById('totalOrdersValue').textContent = data.totalOrders;
    document.getElementById('totalProductsSoldValue').textContent = data.totalProductsSold;
    document.getElementById('newCustomersValue').textContent = data.newCustomers;

    // Set monthly summary numbers if you want to show totals for monthly metrics
    const sumMonthlySales = data.monthlySales.reduce((acc, cur) => acc + cur.total, 0);
    const sumMonthlyOrders = data.monthlyOrders.reduce((acc, cur) => acc + cur.total, 0);
    const sumMonthlyProductsSold = data.monthlyProductsSold.reduce((acc, cur) => acc + cur.total, 0);

    document.getElementById('monthlySalesValue').textContent = `₱${sumMonthlySales.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    document.getElementById('monthlyOrdersValue').textContent = sumMonthlyOrders;
    document.getElementById('monthlyProductsSoldValue').textContent = sumMonthlyProductsSold;

    // Function to create a small bar chart
    function createSmallChart(ctx, labels, dataPoints, color) {
      return new Chart(ctx, {
    type: 'line',  // area chart is a filled line chart
    data: {
      labels: labels,
      datasets: [{
        data: dataPoints,
        fill: true,  // fill the area under the line
        borderColor: color,
        backgroundColor: color.replace('0.7', '0.3'),  // lighter fill color
        tension: 0.3,  // smooth curve
        pointRadius: 2,
        borderWidth: 2
      }]
    },
    options: {
      responsive: false,
      maintainAspectRatio: false,
      scales: {
        x: { display: true },
        y: { display: true, beginAtZero: true }
      },
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      },
      animation: false
    }
  });
}

    // Create charts for each metric with new canvas IDs
    createSmallChart(
      document.getElementById('salesChart').getContext('2d'),
      data.monthlySales.map(d => d.month),
      data.monthlySales.map(d => d.total),
      'rgba(54, 162, 235, 0.7)'
    );

    createSmallChart(
      document.getElementById('ordersChart').getContext('2d'),
      data.monthlyOrders.map(d => d.month),
      data.monthlyOrders.map(d => d.total),
      'rgba(255, 159, 64, 0.7)'
    );

    createSmallChart(
      document.getElementById('productsChart').getContext('2d'),
      data.monthlyProductsSold.map(d => d.month),
      data.monthlyProductsSold.map(d => d.total),
      'rgba(75, 192, 192, 0.7)'
    );

    createSmallChart(
      document.getElementById('monthlySalesChart').getContext('2d'),
      data.monthlySales.map(d => d.month),
      data.monthlySales.map(d => d.total),
      'rgba(54, 162, 235, 0.7)'
    );

    createSmallChart(
      document.getElementById('monthlyOrdersChart').getContext('2d'),
      data.monthlyOrders.map(d => d.month),
      data.monthlyOrders.map(d => d.total),
      'rgba(255, 159, 64, 0.7)'
    );

    createSmallChart(
      document.getElementById('monthlyProductsSoldChart').getContext('2d'),
      data.monthlyProductsSold.map(d => d.month),
      data.monthlyProductsSold.map(d => d.total),
      'rgba(75, 192, 192, 0.7)'
    );

    createSmallChart(
      document.getElementById('customersChart').getContext('2d'),
      data.monthlyNewCustomers.map(d => d.month),
      data.monthlyNewCustomers.map(d => d.total),
      'rgba(153, 102, 255, 0.7)'
    );
  })
  .catch(err => console.error('Fetch error:', err));

  let hideTimeout;

function toggleDropdown() {
  const menu = document.getElementById("dropdownMenu");
  menu.classList.toggle("show");
}

function showDropdown() {
  clearTimeout(hideTimeout);
  document.getElementById("dropdownMenu").classList.add("show");
}

function hideDropdownDelayed() {
  hideTimeout = setTimeout(() => {
    document.getElementById("dropdownMenu").classList.remove("show");
  }, 500); // Adjust delay as needed
}

function clearHideTimeout() {
  clearTimeout(hideTimeout);
}

window.onclick = function(event) {
  if (!event.target.closest('.dropdown')) {
    document.getElementById("dropdownMenu").classList.remove("show");
  }
};
      </script>
</body>
</html>