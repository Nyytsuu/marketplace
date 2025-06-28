<?php
session_start();
include('db_connection.php');

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
if (!isset($_SESSION['AccountID'])) {
    echo "Login required.";
    exit();
}

$buyer_id = $_SESSION['AccountID'];

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "Order ID is missing.";
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order
$orderQuery = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$orderStmt = $pdo->prepare($orderQuery);
$orderStmt->execute([$order_id, $buyer_id]);
$order = $orderStmt->fetch();

if (!$order) {
    echo "Order not found or doesn't belong to this user.";
    exit();
}

// Fetch address
$addressQuery = "SELECT * FROM buyer_addresses WHERE address_id = ?";
$addressStmt = $pdo->prepare($addressQuery);
$addressStmt->execute([$order['delivery_address_id']]);
$address = $addressStmt->fetch();

if (!$address) {
    echo "Delivery address not found.";
    exit();
}

// Fetch order items
$itemQuery = "
    SELECT oi.*, p.product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
";
$itemStmt = $pdo->prepare($itemQuery);
$itemStmt->execute([$order_id]);
$order_items = $itemStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Buyer Order Details</title>
  <link rel="stylesheet" href="buyerorderdetails.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
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

/* Cancel Button Styles */
.order-actions {
  margin-top: 20px;
  display: flex;
  gap: 15px;
  align-items: center;
}

.order-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.cancel-section {
  margin-top: auto;
  padding: 20px;
  text-align: center;
  background-color: #fff;
}
.cancel-btn {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.cancel-btn i {
  font-size: 20px;
}

.cancel-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 71, 87, 0.6);
  background: linear-gradient(to right, #ff4e50, #f83a4c);
}

.cancel-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

.order-status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
  text-transform: capitalize;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #d1ecf1; color: #0c5460; }
.status-shipped { background: #d4edda; color: #155724; }
.status-delivered { background: #d1ecf1; color: #0c5460; }
.status-cancelled { background: #f8d7da; color: #721c24; }

/* Modal Styles */
#cancelModal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
}

.modal-content {
  background-color: #fefefe;
  margin: 10% auto;
  padding: 30px;
  border-radius: 15px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.modal-header h3 {
  margin: 0;
  color: #333;
  font-size: 24px;
}

.close {
  color: #aaa;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
  line-height: 1;
}

.close:hover {
  color: #000;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  font-family: inherit;
  resize: vertical;
  min-height: 100px;
}

.form-group textarea:focus {
  outline: none;
  border-color: #004AAD;
  box-shadow: 0 0 0 3px rgba(0, 74, 173, 0.1);
}

.modal-buttons {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
}

.btn-secondary {
  background: #6c757d;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-danger {
  background: linear-gradient(135deg, #ff4757, #ff3742);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-danger:hover {
  background: linear-gradient(135deg, #ff3742, #ff2d3a);
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
    <a href="feature-page.php?category=Women's Fashion">Women's Fashion</a>
    <a href="feature-page.php?category=Men's Fashion">Men's Fashion</a>
    <a href="feature-page.php?category=Beauty+%26+Fragrance">Beauty & Fragrance</a>
    <a href="feature-page.php?category=Sports+%26+Activewear ">Sports & Activewear</a>
    <a href="feature-page.php?category=Home appliances">Home & Living</a>
    <a href="feature-page.php?category=Mobile+and+Gadget">Mobile & Gadgets</a>
    <a href="feature-page.php?category=Gaming & Tech Accessories">Gaming & Tech Accessories</a>
    <a href="feature-page.php?category=Games+And+Toys">Toys & Kid</a>
    <a href="feature-page.php?category=Audio">Audio</a>
    <a href="feature-page.php?category=Health+%26+Personal+Care">Health & Personal Care</a>
    <a href="feature-page.php?category=Automotive+%26+Motor+Accessories">Automotive & Motor Accessories</a>
    <a href="feature-page.php?category=Pet Care">Pet Care</a>
    <a href="feature-page.php?category=Home appliances">Home Appliances</a>
  </div>
            </div>
            <a href="homepage.php">Home</a>
            <a href="Header_About_Us/aboutus.php">About Us</a>
            <a href="Header_Contact_Us/contactus.php">Contact Us</a>
        </nav>
        </nav>
    </div>
</header>

<main class="main-container">
  <aside class="sidebar">
      <h2>Navigation</h2>
    <nav>
        <ul class="nav-list">
        <li><a href="buyerdashboard.php"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
        <li class="active"><a href="orderhistory.php"><i class='bx bx-history'></i> Order History</a></li>
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

    <section class="content-area">
  <article class="order-details">
    <header class="orderdetail-header">
     <h2>Order Details</h2>
  <p>Date: <?= date("F j, Y", strtotime($order['order_date'])) ?> | Products: <?= count($order_items) ?> | Status: <span class="order-status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
    </header>

<!-- Order Actions Section -->
<div class="order-actions">
  <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
    <button class="cancel-btn" onclick="openCancelModal()">
      <i class='bx bx-x-circle'></i>
      Cancel Order
    </button>
      </div>

  <?php endif; ?>

  <?php if ($order['status'] === 'cancelled' && !empty($order['cancel_reason'])): ?>
    <div style="background: #f8d7da; padding: 10px; border-radius: 8px; color: #721c24;">
      <strong>Cancellation Reason:</strong> <?= htmlspecialchars($order['cancel_reason']) ?>
    </div>
  <?php endif; ?>
</div>
  
        <section class="order-info-grid">
      <div class="shipping-box">
        <h3>Shipping Address</h3>
  <p><strong><?= htmlspecialchars($address['full_name']) ?></strong></p>
  <p><?= htmlspecialchars($address['street_address']) ?>, <?= htmlspecialchars($address['barangay']) ?><br>
  <?= htmlspecialchars($address['province']) ?>, <?= htmlspecialchars($address['region']) ?> <?= htmlspecialchars($address['postal_code']) ?></p>
  <p><strong>Phone:</strong> <?= htmlspecialchars($address['phone_number']) ?></p>
        </div>
      </div>

      <div class="summary-box">
        <div class="summary-row header">
          <p><strong>Order ID:</strong> #<?= htmlspecialchars($order['order_id']) ?></p>
        </div>
        <div class="summary-row header">
          <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
        </div>
        <div class="summary-row">
         <p><strong>Total:</strong> ₱<?= number_format($order['total_price'], 2) ?></p>
        </div>
        <div class="summary-row">
          <div class="label"><strong>Shipping</strong></div>
          <div class="value">Free</div>
        </div>
        <div class="summary-row total">
           <p><strong>Total:</strong> ₱<?= number_format($order['total_price'], 2) ?></p>
        </div>
      </div>
    </section>
   
    
    <?php
function getStepClass($currentStatus, $stepStatus, $statusOrder) {
    // Special handling for cancelled
    if ($currentStatus === 'cancelled') {
        return ($stepStatus === 'pending') ? 'step cancelled current' : 'step cancelled';
    }

    $currentIndex = array_search($currentStatus, $statusOrder);
    $stepIndex = array_search($stepStatus, $statusOrder);

    if ($stepIndex < $currentIndex) return 'step completed';
    if ($stepIndex == $currentIndex) return 'step current';
    return 'step';
}

$statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
$currentStatus = $order['status']; // from your database
?>

<section class="progress-tracker">
  <div class="<?= getStepClass($currentStatus, 'pending', $statusOrder) ?>">
    <span>01</span>
    <div>Pending</div>
  </div>
  <div class="<?= getStepClass($currentStatus, 'processing', $statusOrder) ?>">
    <span>02</span>
    <div>Processing</div>
  </div>
  <div class="<?= getStepClass($currentStatus, 'shipped', $statusOrder) ?>">
    <span>03</span>
    <div>Shipped</div>
  </div>
  <div class="<?= getStepClass($currentStatus, 'delivered', $statusOrder) ?>">
    <span>04</span>
    <div>Delivered</div>
  </div>
</section>

     <section class="product-list">
  <h3>Ordered Items</h3>
  <table border="1">
    <thead>
      <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Sub-Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($order_items as $item): ?>
      <tr>
       <td><img src="<?= htmlspecialchars($item['main_image']) ?>" width="50"> <?= htmlspecialchars($item['product_name']) ?></td>

        <td>₱<?= number_format($item['price'], 2) ?></td>
        <td><?= $item['quantity'] ?></td>
        <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
    </article>
</section>
    </main>

<!-- Cancel Order Modal -->
<div id="cancelModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Cancel Order</h3>
      <span class="close" onclick="closeCancelModal()">&times;</span>
    </div>
    <form id="cancelForm" method="post" action="cancel_order.php">
      <div class="form-group">
        <label for="cancel_reason">Please tell us why you're cancelling this order:</label>
        <textarea id="cancel_reason" name="cancel_reason" placeholder="e.g., Changed my mind, Found a better price, No longer needed..." required></textarea>
      </div>
      <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
      <div class="modal-buttons">
        <button type="button" class="btn-secondary" onclick="closeCancelModal()">Close</button>
        <button type="submit" class="btn-danger">Submit Cancellation</button>
      </div>
    </form>
  </div>
</div>

<script>

// Modal functions
function openCancelModal() {
  document.getElementById('cancelModal').style.display = 'block';
}

function closeCancelModal() {
  document.getElementById('cancelModal').style.display = 'none';
  document.getElementById('cancel_reason').value = '';
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('cancelModal');
  if (event.target == modal) {
    closeCancelModal();
  }
}

// Handle cancel form submission
document.getElementById('cancelForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const cancelReason = document.getElementById('cancel_reason').value.trim();
  
  if (!cancelReason) {
    Swal.fire({
      icon: 'error',
      title: 'Required Field',
      text: 'Please provide a reason for cancellation.'
    });
    return;
  }

  // Show confirmation dialog
  Swal.fire({
    title: 'Are you sure?',
    text: "This action cannot be undone. Your order will be cancelled.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ff4757',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, cancel order',
    cancelButtonText: 'No, keep order'
  }).then((result) => {
    if (result.isConfirmed) {
      // Submit cancellation
      const formData = new FormData();
      formData.append('order_id', '<?= $order_id ?>');
      formData.append('cancel_reason', cancelReason);
      
      fetch('cancel_order.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Order Cancelled',
            text: data.message,
            confirmButtonColor: '#004AAD'
          }).then(() => {
            // Reload page to show updated status
            window.location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Cancellation Failed',
            text: data.message
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An unexpected error occurred. Please try again.'
        });
      });
      
      closeCancelModal();
    }
  });
});
</script>

  <!--Footer-->

  <footer class="footer">
    <div class="footer-top">
      <div class="footer-logo">
        <img src="../Online Marketing System/5. pictures/white logo.png" alt="CartIT Logo">
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

</body>
</html>