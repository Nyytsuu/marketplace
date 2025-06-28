<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

// Helper function to generate protected links
function protected_link($target) {
    global $username;
    return ($username === 'Guest') ? 'finalslogin.php' : $target;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
<title>Complete</title>
   <link rel="stylesheet" href="complete.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
</head>
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
            <a href="User_Driver_SignIn/driver.php">Be a Rider</a>
            <div class="divider">|</div>
            <a href="signup.php">Be a Seller</a>
            <div class="divider">|</div>
        <a href="<?= protected_link('buyer.settings.php') ?>">
            <i class='bx bx-user'></i>
            <span class="username">@<?= htmlspecialchars($username) ?></span>
        </a>
    </div>
</header>
<header class="main-header">
    <div class="container header-container">
      <a href="#" class="logo">
        <img src="pics/logo.png" alt="CartIT Logo">
      </a>
      <form action="#" method="get" class="search-bar">
        <input type="text" name="search" placeholder="Search for products" required>
        <i class="bx bx-search"></i> 
      </form>
<a href="buyershoppingcart.php" style="position: relative; display: inline-block; text-decoration: none; color: inherit;">
  <div class="nav-right">
    <i class='bx bx-cart' style="font-size: 37px; position: relative;"></i>

    <?php if ($cart_count > 0): ?>
      <span style="
        position: absolute;
        top: -5px;
        right: 120px;
        background-color: red;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: bold;
        min-width: 20px;
        text-align: center;
        ">
        <?= $cart_count ?>
      </span>
    <?php endif; ?>

    <div class="cart-info" style="display: inline-block; margin-left: 10px;">
      <span>Shopping cart</span>
      <span style="color: blue; margin-left: 5px;">
        ₱<?= number_format($cart_total, 2) ?>
      </span>
    </div>
  </div>
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


   <script>
  function switchTab(tabElement, tabId) {
    // Remove 'active' class from all tabs
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    tabElement.classList.add('active');

    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
      content.classList.remove('active');
      content.style.display = 'none'; // Set to none immediately to reset transition
    });

    // Delay a bit to apply fade-in animation
    setTimeout(() => {
      const activeContent = document.getElementById(tabId);
      activeContent.style.display = 'block';
      setTimeout(() => {
        activeContent.classList.add('active');
      }, 10);
    }, 50);
  }
</script>


<body>
    <main class="main-content">
        <section class="complete-section">
            <div class="complete-container">
             <div class='bx bx-check'></div>
                <h1>Your order is successfully placed</h1>
                <p>Thank you for shopping with us! We’re processing your order and will update you once it’s on the way. You can view your order details in your account at any time.
                    Happy shopping!</p>
                <div class="order-details">
                <div class="actions">
                   <button class="btn" onclick="window.location.href='feature-page.php'">Continue Shopping</button>
                   <button class="btns" onclick="window.location.href='orderhistory.php'">View Order</button>
                </div>
              </div>
    </section>
</main>
</body>

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


<script src="homepage.js"></script>

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
