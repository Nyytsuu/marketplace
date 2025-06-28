<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['AccountID'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['Username'] ?? 'Guest';
$cart_count = 0;
$cart_total = 0.00;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
        $cart_total += $item['price'] * $item['quantity'];
    }
}


$buyer_id = $_SESSION['AccountID'];

$orderQuery = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$orderStmt = $pdo->prepare($orderQuery);
$orderStmt->execute([$buyer_id]);
$orders = $orderStmt->fetchAll();

// Example pagination setup
$limit = 10; // orders per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $limit;

// Fetch total number of orders for the current user
include 'db_connection.php'; // or wherever your DB connection is

$username = $_SESSION['username']; // assuming session is started

// Count total orders for pagination using PDO
$sql_count = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$stmt = $pdo->prepare($sql_count);
$stmt->execute([$buyer_id]);

$row = $stmt->fetch();
$total_orders = $row['total'];


// Calculate total pages
$total_pages = ceil($total_orders / $limit);

// Fetch paginated orders
$sql_orders = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ?, ?";
$stmt = $pdo->prepare($sql_orders);
$stmt->bindValue(1, $buyer_id, PDO::PARAM_INT);

$stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to generate protected links
function protected_link($target) {
    global $username;
    return ($username === 'Guest') ? 'finalslogin.php' : $target;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer's Order History</title>
    <link rel="stylesheet" href="orderhistory1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
<body>
<style>
  .header-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 85%;
  margin: 0 auto;
  gap: 20px;
}

.logo img {
  height: 90px;
  margin-left: 20px;
  margin-top: 10px;
  margin-bottom: -15px;
}

.search-bar {
  display: flex;
  align-items: center;
  background-color: #f2f2f2;
  border-radius: 100px;
  overflow: hidden;
  height: 45px;
  flex-grow: 1;
  max-width: 600px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.search-bar input {
  border: none;
  background: transparent;
  padding: 0 20px;
  font-size: 15px;
  outline: none;
  flex-grow: 1;
}

.search-bar i {
  font-size: 26px;
  color: white;
  height: 45px;
  width: 55px;
  background-color: #004AAD;
  display: flex;
  justify-content: center;
  align-items: center;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #004AAD;
  text-decoration: none;
  position: relative;
}

.nav-right i {
  font-size: 37px;
}

.cart-info {
  display: flex;
  flex-direction: column;
  font-size: 16px;
  color: #333;
  line-height: 1.2;
}

.cart-badge {
  position: absolute;
  top: -5px;
  right: -10px;
  background-color: red;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 12px;
  font-weight: bold;
  min-width: 20px;
  text-align: center;
}

</style>
<body>
<header class="top-bar">
    <div class="container">
        <div class="social-section">
            <span style="font-size: 13px;">Follow us:</span>
            <div class="social-icon">
                <i class='bx bxl-facebook'></i>
                <i class='bx bxl-instagram'></i>
                <i class='bx bxl-twitter'></i>
            </div>
        </div>
            <div class="divider">|</div>
            <a href="../User_Driver_SignIn/driver.php">Be a Rider</a>
            <div class="divider">|</div>
            <a href="../signup.php">Be a Seller</a>
            <div class="divider">|</div>
        <a href="<?= protected_link('../buyerdashboard.php') ?>">
            <i class='bx bx-user'></i>
            <span class="username">@<?= htmlspecialchars($username) ?></span>
        </a>
    </div>
</header>
<header class="main-header">
  <div class="container header-container">
    
    <!-- LOGO -->
    <a href="#" class="logo">
      <img src="../5. pictures/logo.png" alt="CartIT Logo">
    </a>

    <!-- SEARCH BAR -->
    <form action="#" method="get" class="search-bar">
      <input type="text" name="search" placeholder="Search for products" required>
      <i class="bx bx-search"></i> 
    </form>

    <!-- CART ICON -->
    <a href="buyershoppingcart.php" class="nav-right">
      <i class='bx bx-cart'></i>
      <div class="cart-info">
        <span>Shopping cart</span>
        <span style="color: blue;">₱<?= number_format($cart_total, 2) ?></span>
      </div>

      <?php if ($cart_count > 0): ?>
        <span class="cart-badge"><?= $cart_count ?></span>
      <?php endif; ?>
    </a>

  </div>
</header>

<header class="second-header">
    <div class="second-container">
        <nav class="nav-links">
            <div class="dropdown-wrapper">
  <a href="#" class="categoryToggle">All Categories </a>
  <i class='bx bx-chevron-down' id="categoryToggle" style="cursor: pointer; font-size: 24px; color: white;"></i>
  <div class="dropdown" id=categoryMenu>
   <a href="feature-page.php?category=Women's Fashion">Women's Fashion</a>
    <a href="feature-page.php?category=Men's Fashion">Men's Fashion</a>
    <a href="feature-page.php?category=Beauty & Fragrance">Beauty & Fragrance</a>
    <a href="feature-page.php?category=Sports">Sports & Activewear</a>
    <a href="feature-page.php?category=Home & Living">Home & Living</a>
    <a href="feature-page.php?category=Mobile & Gadgets">Mobile & Gadgets</a>
    <a href="feature-page.php?category=Gaming & Tech Accessories">Gaming & Tech Accessories</a>
    <a href="feature-page.php?category=Toys & Kid">Toys & Kid</a>
    <a href="feature-page.php?category=Audio">Audio</a>
    <a href="feature-page.php?category=Health & Personal Care">Health & Personal Care</a>
    <a href="feature-page.php?category=Automotive & Motor Accessories">Automotive & Motor Accessories</a>
    <a href="feature-page.php?category=Pet Care">Pet Care</a>
    <a href="feature-page.php?category=Home Appliances">Home Appliances</a>
  </div>
</div>
            <a href="homepage.php">Home</a>
            <a href="Header_About_Us/aboutus.php">About</a>
            <a href="Header_Contact_Us/contactus.php">Contact</a>
</nav>
    </div>
</header>

<main class="dashboard">
    <div class="main-container">
        <aside class="sidebar" aria-label="Account Navigation">
            <h2>Navigation</h2>
            <nav>
            <ul class="nav-list">
        <li><a href="buyerdashboard.php"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
        <li class="active"><a href="orderhistory.php"><i class='bx bx-history'></i> Order History</a></li>
        <li><a href="buyerAddress.php"><i class='bx bx-map'></i> Address</a></li>
        <li><a href="buyer.settings.php"><i class='bx bx-cog'></i> Settings</a></li>
        <li id="logout-btn" style="cursor: pointer;"><i class='bx bx-log-out'></i> Log-out</li>
            </nav>
        </aside>
<script>
document.getElementById('logout-btn').addEventListener('click', function () {
  Swal.fire({
    title: 'Are you sure?',
    text: "You will be logged out of your account.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, log out',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      // Redirect to logout.php
      window.location.href = 'buyer_logout.php';
    }
  });
});
</script>
        <div class="order-history">
            <div class="profile-head">
                <h2>Recent Order History</h2>
            </div>
            <hr><br>
            <table class="order-table">
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($orders): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                            <td class="status <?= strtolower($order['status']) ?>"><?= strtoupper($order['status']) ?></td>
                            <td><?= date("M d, Y", strtotime($order['order_date'])) ?></td>
                            <td>₱<?= number_format($order['total_price'], 2) ?></td>
                            <td><a href="buyerorderdetails.php?order_id=<?= $order['order_id'] ?>" class="view-link">View Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No orders found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php
    // Previous button
    if ($current_page > 1): ?>
        <a href="?page=<?= $current_page - 1 ?>" title="Previous Page">
            <i class='bx bx-chevron-left'></i>
        </a>
    <?php else: ?>
        <a href="#" class="disabled" title="Previous Page">
            <i class='bx bx-chevron-left'></i>
        </a>
    <?php endif;

    // Page numbers logic
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);

    // Show first page if not in range
    if ($start_page > 1): ?>
        <a href="?page=1">1</a>
        <?php if ($start_page > 2): ?>
            <span class="ellipsis">...</span>
        <?php endif;
    endif;

    // Show page numbers
    for ($i = $start_page; $i <= $end_page; $i++): ?>
        <a href="?page=<?= $i ?>" <?= $i == $current_page ? 'class="active"' : '' ?>>
            <?= $i ?>
        </a>
    <?php endfor;

    // Show last page if not in range
    if ($end_page < $total_pages): ?>
        <?php if ($end_page < $total_pages - 1): ?>
            <span class="ellipsis">...</span>
        <?php endif; ?>
        <a href="?page=<?= $total_pages ?>"><?= $total_pages ?></a>
    <?php endif;

    // Next button
    if ($current_page < $total_pages): ?>
        <a href="?page=<?= $current_page + 1 ?>" title="Next Page">
            <i class='bx bx-chevron-right'></i>
        </a>
    <?php else: ?>
        <a href="#" class="disabled" title="Next Page">
            <i class='bx bx-chevron-right'></i>
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>


<!--Footer-->

<footer class="footer">
    <div class="footer-top">
        <div class="footer-logo">
            <img src="pics/white logo.png" alt="CartIT Logo" />
            <p>CartIT – Where Smart Shopping Begins. Discover quality finds, hot deals, and fast delivery—all in one cart.</p>
            <div class="social-icons">
                <i class='bx bxl-facebook'></i>
                <i class='bx bxl-instagram'></i>
                <i class='bx bxl-twitter'></i>
            </div>
        </div>
        <div class="footer-links">
            <h3>My Account</h3>
            <a href="<?= protected_link('buyer.settings.php') ?>">My Account</a>
            <a href="<?= protected_link('orderhistory.php') ?>">Order History</a>
            <a href="<?= protected_link('buyershoppingcart.php') ?>">Shopping Cart</a>
        </div>
        <div class="footer-links">
            <h3>Helps</h3>
            <a href="../Header_Contact_Us/contactus.php">Contact</a>
            <a href="../Footer_Buyer_Terms_And_Condition/buyertoc.php">Terms & Condition</a>
            <a href="../Footer_Buyer_Privacy_Policy/buyerprivacy.php">Privacy Policy</a>
        </div>
        <div class="footer-links">
            <h3>Proxy</h3>
            <a href="../Online Marketing System/Header_About_Us/aboutus.php">About Us</a>
            <a href="<?= protected_link('feature-page.php') ?>">Browse All Product</a>
        </div>
        <div class="footer-contact">
            <h3>Customer Supports:</h3>
            <p>(63+) 000 0000 000</p>
            <h3>Contact Us</h3>
            <p>info@cartit.com</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 CartIT eCommerce. All Rights Reserved</p>
        <div class="payment-icons">
            <img src="pics/Payment/GCash.png" alt="GCash" />
            <img src="pics/Payment/MasterCard.png" alt="MasterCard" />
            <img src="pics/Payment/PayPal.png" alt="PayPal" />
            <img src="pics/Payment/Paymaya.png" alt="Paymaya" />
            <img src="pics/Payment/visa.png" alt="Visa" />
        </div>
    </div>
</footer>

<script>
// Logout functionality
document.getElementById('logout-btn').addEventListener('click', function () {
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out of your account.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, log out',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'buyer_logout.php';
        }
    });
});

// Category dropdown functionality
const toggleBtn = document.getElementById('categoryToggle');
const dropdownMenu = document.getElementById('categoryMenu');

toggleBtn.addEventListener('click', function (e) {
    e.preventDefault();
    const isVisible = dropdownMenu.style.display === 'flex';
    dropdownMenu.style.display = isVisible ? 'none' : 'flex';
});

document.addEventListener('click', function (e) {
    if (!toggleBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.style.display = 'none';
    }
});
</script>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>