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
include 'showindivmainproduct.php';

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($product['product_name']) ?></title>
  <link rel="stylesheet" href="view7.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
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
        <a href="<?= protected_link('../buyer.settings.php') ?>">
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


    <!--Main-->
<form action="buynow.php" method="POST">
  <section class="main">
<div class="thumbnails">
  <?php foreach ($thumbnails as $index => $thumb): ?>
    <img src="<?= htmlspecialchars($thumb) ?>" data-index="<?= $index ?>" />
  <?php endforeach; ?>
</div>

<!-- Hidden arrows that show on hover -->
<button class="hover-arrow left" id="hoverPrev">&#8592;</button>
<button class="hover-arrow right" id="hoverNext">&#8594;</button>

<div class="main-image-wrapper" id="mainImageWrapper">
  <img src="<?= htmlspecialchars($mainImage) ?>" id="mainImg" />
  <input type="hidden" name="main_image" id="mainImageInput" value="<?= htmlspecialchars($mainImage) ?>">
</div>
 <div class="right-column">
  <div class="product-detail">
    <h1><?= htmlspecialchars($name) ?></h1>
    <p class="moreinfo"><?= htmlspecialchars($bought) ?> bought · <?= htmlspecialchars($stocks) ?> in stock</p>
    <p class="price" style="font-size: 2rem;">₱<?= htmlspecialchars($price) ?></p>
      <input type="hidden" name="product_id" value="<?= $productId ?>">
      <input type="hidden" name="product_name" value="<?= htmlspecialchars($name) ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($price) ?>">
      <input type="hidden" name="variation_size" id="selectedSize">
<input type="hidden" name="variation_color" id="selectedColor" value="<?= htmlspecialchars($c ?? '') ?>">
      <!-- Color Selection -->
 <?php if (!empty($colorOptions)): ?>
  <div class="type-container">
    <label for="color" class="color-label">Color</label>
  <div class="colors">
  <?php foreach ($colorOptions as $c): ?>
<span class="color-option"
 style="background: <?= htmlspecialchars($c) ?>;"
      data-color="<?= htmlspecialchars($c) ?>"
      data-image="<?= htmlspecialchars($variationImagesByColor[$c][0] ?? $mainImage) ?>"
      onclick="selectColorImage(this)">
</span>

  <?php endforeach; ?>
</div>
  </div>
<?php endif; ?>

      <!-- Size Selection -->
      <?php if (!empty($sizeOptions)): ?>
      <div class="type-container">
        <label for="size" class="size-label">Size</label>
        <div class="sizes">
          <?php foreach ($sizeOptions as $size): ?>
            <span class="size-option" onclick="selectSize(this, '<?= htmlspecialchars($size) ?>')">
              <?= htmlspecialchars($size) ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>


<!-- Quantity Selection -->
<div class="quantity-container">
    <label for="quantity-input">Quantity</label>
    <div class="quantity">
        <button type="button" class="minus">-</button>
        <input type="number" name="buy_quantity" id="quantity-input" value="1" min="1" max="<?= $stocks ?>" />
        <button type="button" class="plus">+</button>
    </div>
</div>

<!-- Button Row (both forms inside flex container) -->
<div class="button-row">

    <!-- Buy Now Form -->
    <form action="buynow.php" method="POST" onsubmit="return validateSelection();">
        <input type="hidden" name="product_id" value="<?= $productId ?>">
        <input type="hidden" name="product_name" value="<?= htmlspecialchars($name) ?>">
        <input type="hidden" name="price" value="<?= htmlspecialchars($price) ?>">
        <input type="hidden" name="main_image" id="mainImageInput" value="<?= htmlspecialchars($mainImage) ?>">
        <input type="hidden" name="variation_size" id="selectedSize">
        <input type="hidden" name="variation_color" id="selectedColor" value="<?= htmlspecialchars($c ?? '') ?>">
        <input type="hidden" name="buy_quantity" id="quantity-input-buy" value="1" min="1" max="<?= $stocks ?>" />

        <button type="submit" class="buy-now">Buy Now</button>
    </form>

    <!-- Add to Cart Form -->
    <form action="buyershoppingcart.php" method="POST" onsubmit="return validateSelection();">
        <input type="hidden" name="product_id" value="<?= $productId ?>">
        <input type="hidden" name="product_name" value="<?= htmlspecialchars($name) ?>">
        <input type="hidden" name="price" value="<?= htmlspecialchars($price) ?>">
        <input type="hidden" name="main_image" id="mainImageInputCart" value="<?= htmlspecialchars($mainImage) ?>">
        <input type="hidden" name="variation_size" id="selectedSizeCart">
        <input type="hidden" name="variation_color" id="selectedColorCart" value="<?= htmlspecialchars($c ?? '') ?>">
        <input type="hidden" name="buy_quantity" id="quantity-input-cart" value="1" min="1" max="<?= $stocks ?>" />

        <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
    </form>

</div> <!-- end of button-row -->


</section>


<script>
const variationImagesMap = <?= json_encode($variationImagesByColor ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

window.selectColorImage = function (element) {
  const color = element.getAttribute('data-color');
  const images = variationImagesMap[color] || [];
const selectedColorCartInput = document.getElementById('selectedColorCart');
if (selectedColorCartInput) selectedColorCartInput.value = color;
  if (images.length === 0) {
    const fallbackImage = element.getAttribute('data-image');
    if (fallbackImage) {
      images.push(fallbackImage);
    }
  }

  const mainImg = document.getElementById('mainImg');
  const mainImageInput = document.getElementById('mainImageInput');
  const selectedColorInput = document.getElementById('selectedColor');

  if (!mainImg || !mainImageInput || !selectedColorInput || images.length === 0) {
    console.warn("Missing image or input elements, or no images available.");
    return;
  }

  const newMainSrc = images[0];
  mainImg.classList.add('fade-out');
  setTimeout(() => {
    mainImg.src = newMainSrc;
    mainImageInput.value = newMainSrc;
    mainImg.onload = () => {
      mainImg.classList.remove('fade-out');
      mainImg.classList.add('fade-in');
      setTimeout(() => mainImg.classList.remove('fade-in'), 400);
    };
  }, 200);

  selectedColorInput.value = color;

  document.querySelectorAll('.colors span').forEach(s => s.classList.remove('selected'));
  element.classList.add('selected');
};

window.selectSize = function (element, sizeValue) {
  document.querySelectorAll('.size-option').forEach(el => el.classList.remove('selected'));
  element.classList.add('selected');

  const selectedSize = document.getElementById('selectedSize');
  const selectedSizeCart = document.getElementById('selectedSizeCart');

  if (selectedSize) selectedSize.value = sizeValue;
  if (selectedSizeCart) selectedSizeCart.value = sizeValue;
};

function changeMainImage(newSrc) {
  const mainImg = document.getElementById('mainImg');
  const mainImageInput = document.getElementById('mainImageInput');
  if (!mainImg) return;

  mainImg.classList.add('fade-out');
  setTimeout(() => {
    mainImg.src = newSrc;
    if (mainImageInput) mainImageInput.value = newSrc;
    mainImg.onload = () => {
      mainImg.classList.remove('fade-out');
      mainImg.classList.add('fade-in');
      setTimeout(() => mainImg.classList.remove('fade-in'), 400);
    };
  }, 200);
}

document.addEventListener("DOMContentLoaded", function () {
  const thumbnails = document.querySelectorAll('.thumbnails img');
  const mainImg = document.getElementById('mainImg');
  let imageList = Array.from(thumbnails).map(img => img.src);
  let currentIndex = 0;

  thumbnails.forEach((thumb, index) => {
    thumb.addEventListener('click', () => {
      currentIndex = index;
      changeMainImage(thumb.src);
    });
  });

  document.getElementById('hoverPrev').addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + imageList.length) % imageList.length;
    changeMainImage(imageList[currentIndex]);
  });

  document.getElementById('hoverNext').addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % imageList.length;
    changeMainImage(imageList[currentIndex]);
  });

  document.querySelector('.minus').addEventListener('click', () => adjustQuantity(-1));
  document.querySelector('.plus').addEventListener('click', () => adjustQuantity(1));
  document.getElementById('quantity-input').addEventListener('input', (e) => {
    const val = e.target.value;
    const inputCart = document.getElementById('quantity-input-cart');
    if (inputCart) inputCart.value = val;
  });

  const aboutTexts = document.querySelectorAll('.about-text, .desc-text');
  aboutTexts.forEach(aboutText => {
    const showMoreBtn = document.createElement('span');
    showMoreBtn.classList.add('show-more');
    showMoreBtn.textContent = 'Read More';
    aboutText.parentNode.appendChild(showMoreBtn);
    showMoreBtn.addEventListener('click', function () {
      aboutText.classList.toggle('expanded');
      showMoreBtn.textContent = aboutText.classList.contains('expanded') ? 'Read Less' : 'Read More';
    });
  });
});

function adjustQuantity(change) {
  const input = document.getElementById('quantity-input');
  const inputCart = document.getElementById('quantity-input-cart');
  if (!input) return;

  let current = parseInt(input.value) || 1;
  const min = parseInt(input.min) || 1;
  const max = parseInt(input.max) || 999;

  current = Math.min(Math.max(current + change, min), max);
  input.value = current;

  if (inputCart) inputCart.value = current;
}

function validateSelection() {
  syncFormInputs(); // <-- Add this line

  const colorInput = document.getElementById('selectedColor');
  const sizeInput = document.getElementById('selectedSize');
  const hasColorOptions = document.querySelectorAll('.color-option').length > 0;
  const hasSizeOptions = document.querySelectorAll('.size-option').length > 0;

  if (hasColorOptions && !colorInput.value) {
    alert("Please select a color before proceeding.");
    return false;
  }
  if (hasSizeOptions && !sizeInput.value) {
    alert("Please select a size before proceeding.");
    return false;
  }

  const quantityInput = document.getElementById('quantity-input');
  if (!quantityInput || quantityInput.value < 1) {
    alert("Please enter a valid quantity.");
    return false;
  }

  return true;
}


function syncFormInputs() {
  const size = document.getElementById('selectedSize').value;
  const color = document.getElementById('selectedColor').value;
  const image = document.getElementById('mainImageInput').value;
  const quantity = document.getElementById('quantity-input').value;

  document.querySelectorAll('form').forEach(form => {
    const sizeInput = form.querySelector('input[name="size"]');
    const colorInput = form.querySelector('input[name="variation_color"]');
    const imageInput = form.querySelector('input[name="main_image"]');
    const qtyInput = form.querySelector('input[name="buy_quantity"]');

    if (sizeInput) sizeInput.value = size;
    if (colorInput) colorInput.value = color;
    if (imageInput) imageInput.value = image;
    if (qtyInput) qtyInput.value = quantity;
  });
}

function switchTab(tabElement, tabId) {
  // Remove active class from all tabs
  document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
  // Add active class to clicked tab
  tabElement.classList.add('active');

  // Hide all tab contents
  document.querySelectorAll('.tab-content').forEach(content => {
    content.style.display = 'none';
    content.classList.remove('fade-in');
  });

  // Show selected tab content with fade-in effect
  const activeContent = document.getElementById(tabId);
  if (activeContent) {
    activeContent.style.display = 'block';
    activeContent.classList.add('fade-in');
  }
}

</script>

   <!--description-->
<section class="product-description">
    <div class="tabs">
      <div class="tab active" onclick="switchTab(this, 'desc')">Description</div>
      <div class="tab" onclick="switchTab(this, 'add-info')">Additional Information</div>
    </div>

    <div class="tab-content" id="desc">
         <div class="desc-container">
        <p class="desc-text">
        <?php include 'show-product-desc.php' ?>
      </p>
    </div>
    </div>
    
<!--additional info-->
<div class="tab-content" id="add-info" style="display: none;">
    <div class="desc-container">
        <p class="desc-text">
            <?php include 'show-product-additional-info.php' ?>
        </p>
    </div>
</div>
   </section>
  <!--you may also like-->
<div class="section featured-products">
    <div class="products-grid">
        <h2 style="color:#474747;">You may also like</h2>
            <?php include 'product-load-productview.php';?>
        </div>
</div>

  <!--Footer-->

  <footer class="footer">
    <div class="footer-top">
      <div class="footer-logo">
        <img src="../pictures/white logo.png" alt="CartIT Logo">
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
