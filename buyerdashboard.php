<?php
session_start();
include 'db_connection.php';
$username = $_SESSION['Username'] ?? 'Guest';
$cart_count = 0;
$cart_total = 0.00;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
        $cart_total += $item['price'] * $item['quantity'];
    }
}

// Helper function to generate protected links
function protected_link($target) {
    global $username;
    return ($username === 'Guest') ? 'finalslogin.php' : $target;
}

// Debug: Check what's in the session
error_log("Dashboard Session contents: " . print_r($_SESSION, true));

// Check if user is logged in - try multiple possible session variable names
$buyer_id = null;
$username = 'Guest';

if (isset($_SESSION['AccountID'])) {
    $buyer_id = $_SESSION['AccountID'];
} elseif (isset($_SESSION['user_id'])) {
    $buyer_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['id'])) {
    $buyer_id = $_SESSION['id'];
} elseif (isset($_SESSION['buyer_id'])) {
    $buyer_id = $_SESSION['buyer_id'];
}

// If no valid session found, redirect to login
if (!$buyer_id) {
    error_log("Dashboard: No valid session found. Redirecting to login. Session data: " . print_r($_SESSION, true));
    header("Location: finalslogin.php");
    exit();
}

// Get username for display
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} elseif (isset($_SESSION['Username'])) {
    $username = $_SESSION['Username'];
} elseif (isset($_SESSION['email'])) {
    $username = $_SESSION['email'];
}

$cart_count = 0;
$cart_total = 0.00;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
        $cart_total += $item['price'] * $item['quantity'];
    }
}

try {
          $sql = "SELECT b.username, ba.full_name, b.EmailAdd, ba.street_address, ba.phone_number
            FROM buyers b
            LEFT JOIN buyer_addresses ba ON b.AccountID = ba.buyer_id AND ba.is_default = 1
            WHERE b.AccountID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$buyer_id]);
    $user = $stmt->fetch();

    if (!$user) {
        // Try alternative query if first one fails
        $sql = "SELECT username, AccountID as full_name, EmailAdd, '' as street_address, '' as phone_number
                FROM buyers WHERE AccountID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$buyer_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "User data not found for ID: " . htmlspecialchars($buyer_id);
            exit;
        }
    }

    $sql = "SELECT order_id, status, order_date, total_price FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$buyer_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error in dashboard: " . $e->getMessage());
    $user = ['username' => $username, 'full_name' => 'Unknown', 'EmailAdd' => 'Unknown', 'street_address' => 'No address', 'phone_number' => 'No phone'];
    $orders = [];
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CartIT Dashboard</title>
  <link rel="stylesheet" href="style.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
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
  <a href="../Category_All/categories.php" class="categoryToggle">All Categories </a>
  <i class='bx bx-chevron-down' id="categoryToggle" style="cursor: pointer; font-size: 24px; color: white;"></i>
  <div class="dropdown" id="categoryMenu">
    <a href="../feature-page.php?category=Women's Fashion">Women's Fashion</a>
    <a href="../feature-page.php?category=Men's Fashion">Men's Fashion</a>
    <a href="../feature-page.php?category=Beauty+%26+Fragrance">Beauty & Fragrance</a>
    <a href="../feature-page.php?category=Sports+%26+Activewear">Sports & Activewear</a>
    <a href="../feature-page.php?category=Home+appliances">Home & Living</a>
    <a href="../feature-page.php?category=Mobile+and+Gadget">Mobile & Gadgets</a>
    <a href="../feature-page.php?category=Gaming+%26+Tech+Accessories">Gaming & Tech Accessories</a>
    <a href="../feature-page.php?category=Games+And+Toys">Toys & Kid</a>
    <a href="../feature-page.php?category=Audio">Audio</a>
    <a href="../feature-page.php?category=Health+%26+Personal+Care">Health & Personal Care</a>
    <a href="../feature-page.php?category=Automotive+%26+Motor+Accessories">Automotive & Motor Accessories</a>
    <a href="../feature-page.php?category=Pet+Care">Pet Care</a>
    <a href="../feature-page.php?category=Home+appliances">Home Appliances</a>
  </div>
</div>

            <a href="../homepage.php">Home</a>
            <a href="../Header_About_Us/aboutus.php">About Us</a>
            <a href="../Header_Contact_Us/contactus.php">Contact Us</a>
        </nav>
        </nav>
    </div>
</header>

<div class="main-container">
  <!-- Sidebar -->
  <aside class="sidebar" aria-label="Account Navigation">
    <h2>Navigation</h2>
    <nav>
      <ul class="nav-list">
        <li class="active"><a href="buyerdashboard.php"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
        <li><a href="orderhistory.php"><i class='bx bx-history'></i> Order History</a></li>
        <li><a href="buyerAddress.php"><i class='bx bx-map'></i> Address</a></li>
        <li><a href="buyer.settings.php"><i class='bx bx-cog'></i> Settings</a></li>
        <li id="logout-btn" style="cursor: pointer;"><i class='bx bx-log-out'></i> Log-out</li>
      </ul>
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

<main class="content">
    <div class="account-wrapper">
      <div class="profile-address">
        <!-- Profile Card with refresh functionality -->
        <div class="card">
          <div class="card-header">
            <div>
              <h3>Profile</h3>
              <?php if (isset($user['updated_at'])): ?>
                <div class="last-updated">Last updated: <?= date("M d, Y g:i A", strtotime($user['updated_at'])) ?></div>
              <?php endif; ?>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
              </button>
              <a href="buyer.settings.php">Edit</a>
            </div>
          </div>
          <div class="profile-info">
            <div class="info-pair">
              <span class="label">Username:</span>
              <span class="value">@<?= htmlspecialchars($user['username'] ?? 'Unknown') ?></span>
            </div>
            <div class="info-pair">
              <span class="label">Name:</span>
              <span class="value"><?= htmlspecialchars($user['full_name'] ?? 'Not set') ?></span>
            </div>
            <div class="info-pair">
              <span class="label">Email:</span>
              <span class="value"><?= htmlspecialchars($user['EmailAdd'] ?? 'Not set') ?></span>
            </div>
          </div>
        </div>

      <?php
// Example: Fetch the default address
$stmt = $pdo->prepare("SELECT * FROM buyer_addresses WHERE buyer_id = ? AND is_default = 1 LIMIT 1");
$stmt->execute([$buyer_id]);
$default_address = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- Address Card -->
<div class="card">
    <div class="card-header">
        <h3>Address</h3>
        <a href="buyerAddress.php">Edit</a>
    </div>

<?php if ($default_address): ?>
    <p><strong>Name:</strong> <?= htmlspecialchars($default_address['full_name']) ?></p>
    <p><strong>Phone Number:</strong> (+63) <?= htmlspecialchars($default_address['phone_number']) ?></p>

    <p>
        <strong>Address:</strong>
        <?= htmlspecialchars($default_address['street_address']) ?>, 
        <?= htmlspecialchars($default_address['barangay']) ?>, 
        <?= htmlspecialchars($default_address['province']) ?>, 
        <?= htmlspecialchars($default_address['region']) ?>
    </p>

    <p><strong>Postal Code:</strong> <?= htmlspecialchars($default_address['postal_code']) ?></p>
<?php else: ?>
    <p>No default address set.</p>
<?php endif; ?>

</div>

    </div>

    <!-- Order History Card -->
    <div class="card">
      <div class="card-header">
        <h3>Recent Order History</h3>
        <a href="orderhistory.php" class="view-all">View All</a>
      </div>
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
          <td class="status <?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
            <?= strtoupper(htmlspecialchars($order['status'])) ?>
          </td>
          <td><?= date("M d, Y", strtotime($order['order_date'])) ?></td>
          <td>$<?= number_format($order['total_price'], 2) ?></td>
          <td>
            <a href="buyerorderdetails.php?order_id=<?= urlencode($order['order_id']) ?>" class="view-link">View Details</a>
          </td>
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
  </div>


    <!--Footer-->
  <footer class="footer">
    <div class="footer-top">
      <div class="footer-logo">
        <img src="../5. pictures/white logo.png" alt="CartIT Logo">
        <p>CartIT – Where Smart Shopping Begins. Discover quality finds, hot deals, and fast delivery—all in one cart.</p>
        <div class="social-icons">
          <i class='bx bxl-facebook'></i>
          <i class='bx bxl-instagram'></i>
          <i class='bx bxl-twitter'></i>
        </div>
      </div>
      <div class="footer-links">
        <h3>My Account</h3>
        <a href="../Buyer Dashboard/dashboard.html">My Account</a>
        <a href="../buyer order history/index.html">Order History</a>
        <a href="../Buyer Shopping Cart/buyershoppingcart.html">Shopping Cart</a>
      </div>
      <div class="footer-links">
        <h3>Helps</h3>
        <a href="../Header Contact Us/ContactUs.html">Contact</a>
        <a href="../Footer  Buyer Terms And Condition/">Terms & Condition</a>
        <a href="../Footer  Buyer Privacy Policy/">Privacy Policy</a>
      </div>
      <div class="footer-links">
        <h3>Proxy</h3>
        <a href="../Buyer About Us/aboutus.html">About Us</a>
        <a href="../Buyer Feautre Products/featureproducts.html">Browse All Product</a>
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
      <div class="footer-payments">
        <div class="payment-icon">
          <i class='bx bx-lock'></i>
          <span>
            Secure<br>Payment
          </span>
        </div>
        <div class="payment-icons">
          <i class='bx bxl-visa'></i>
          <i class='bx bxl-mastercard'></i>
        </div>
      </div>
    </div>
  </footer>

  <script>
// Show success message if page was accessed after update
<?php if ($show_update_success): ?>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Success!',
        text: 'Your profile has been updated successfully.',
        icon: 'success',
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
});
<?php endif; ?>

// Auto-refresh mechanism - check for updates every 30 seconds
let lastUpdateCheck = <?= time() ?>;

function checkForUpdates() {
    fetch('check_profile_updates.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            last_check: lastUpdateCheck,
            buyer_id: <?= json_encode($buyer_id) ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.updated) {
            // Show notification that data was updated
            Swal.fire({
                title: 'Profile Updated',
                text: 'Your profile information has been updated. Refreshing...',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Refresh the page to show new data
                window.location.href = 'buyerdashboard.php?updated=true';
            });
        }
        lastUpdateCheck = Math.floor(Date.now() / 1000);
    })
    .catch(error => {
        console.error('Error checking for updates:', error);
    });
}

// Check for updates every 30 seconds
setInterval(checkForUpdates, 30000);

// Force refresh profile data function
function refreshProfileData() {
    Swal.fire({
        title: 'Refreshing...',
        text: 'Getting the latest information',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Add a small delay then refresh
    setTimeout(() => {
        window.location.href = 'buyerdashboard.php?refresh=' + Date.now();
    }, 1000);
}
</script>

 <script>
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
</body>
</html>
