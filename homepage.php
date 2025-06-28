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
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="homepage1.css" />
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet' />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <title>Homepage</title>
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


<!--main content-->

<!--Slider-->
<section class="container">
    <div class="slider-wrapper">
        <div class="slider">
            <img id="slide1" src="pics/advertistment big big.png" alt="perfume" />
            <img id="slide2" src="pics/advertistment big big 2.png" alt="phone" />
            <img id="slide3" src="pics/advertistment big big 3.png" alt="nike" />
        </div>

        <div class="slider-dots">
            <button onclick="goToSlide(0)" class="active"></button>
            <button onclick="goToSlide(1)"></button>
            <button onclick="goToSlide(2)"></button>
        </div>
    </div>
</section>

<script>
const slider = document.querySelector('.slider');
const slides = document.querySelectorAll('.slider img');
const dots = document.querySelectorAll('.slider-dots button');

function goToSlide(index) {
    const slideWidth = slides[0].clientWidth;
    slider.scrollTo({
        left: slideWidth * index,
        behavior: 'smooth'
    });

    dots.forEach(dot => dot.classList.remove('active'));
    dots[index].classList.add('active');
}

slider.addEventListener('scroll', () => {
    const index = Math.round(slider.scrollLeft / slides[0].clientWidth);
    dots.forEach(dot => dot.classList.remove('active'));
    if (dots[index]) dots[index].classList.add('active');
});
</script>

<!--features-->
<section class="features-wrapper">
    <div class="features">
        <div>
            <span class="material-symbols-outlined">local_shipping</span>
            <p class="header">Free Shipping</p>
            <p>Free shipping with discount</p>
        </div>

        <div>
            <span class="material-symbols-outlined">lock</span>
            <p class="header">Secure Payment</p>
            <p>We ensure your money is safe</p>
        </div>

        <div>
            <span class="material-symbols-outlined">headset_mic</span>
            <p class="header">Customer Support 24/7</p>
            <p>Instant access to support</p>
        </div>

        <div>
            <span class="material-symbols-outlined">local_mall</span>
            <p class="header">Fast Delivery</p>
            <p>Speedy and secure delivery</p>
        </div>
    </div>
</section>

<!--popular categories-->
<section class="popular-categories">
    <h2 style="color:#474747; font-size: 30px;">Popular Categories</h2>
    <div class="category-grid">
        <div class="category-card">
         <a href="feature-page.php?category=Women's Fashion"> <img src="pics/women shoes.png" alt="Women's Fashion" />
            <p>Women's Fashion</p>
            </a>
        </div>
        <div class="category-card">
           <a href="feature-page.php?category=Men's Fashion"> <img src="pics/menfashion.png" alt="Men's Fashion" />
            <p>Men's Fashion</p>
            </a>
        </div>
        <div class="category-card">
            <a href="feature-page.php?category=Sports"><img src="pics/makeup.png" alt="Beauty" />
            <p>Beauty & Fragrances</p></a>
        </div>
        <div class="category-card">
            <a href="feature-page.php?category=Beauty & Fragrance"><img src="pics/Personal Care.png" alt="Personal Care" />
            <p>Personal Care</p></a>
        </div>
        <div class="category-card">
            <a href="feature-page.php?category=Home & Living"></a><img src="pics/Home & Living.png" alt="Home & Living" />
            <p>Home & Living</p></a>
        </div>
        <div class="category-card">
            <a href="feature-page.php?category=Gaming & Tech Accessories"><img src="pics/gaming tech.png" alt="Gadget Accessories" />
            <p>Gadget Accessories</p></a>
        </div>
    </div>
</section>

<!-- Accessories Grid -->
<div class="section accessories-grid">
    <div class="accessories-main">
        <img src="pics/1.png" alt="Accessories Watch" />
    </div>

    <div class="accessories-categories">
        <div class="accessory-box">
            <img src="pics/2.png" alt="Self Care" />
        </div>

        <div class="accessory-box">
            <img src="pics/4.png" alt="Music Instruments" />
        </div>

        <div class="accessory-box">
            <img src="pics/5.png" alt="Gaming Gear" />
        </div>

        <div class="accessory-box">
            <img src="pics/3.png" alt="Speakers" />
        </div>
    </div>
</div>

<!--Featured Products-->
<div class="section featured-products">
    <div class="products-grid">
        <h2 style="color:#474747;">Featured Products</h2>
        <?php include 'load_products.php'; ?>
    </div>
</div>

<div class="see-more">
    <button onclick="window.location.href='<?= protected_link('feature-page.php') ?>'"> See more </button>
</div>



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
