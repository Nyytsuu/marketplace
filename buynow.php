<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cart_count = 0;
$cart_total = 0.00;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
        $cart_total += $item['price'] * $item['quantity'];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? 0;
    $name = $_POST['product_name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $c = $_POST['variation_color'] ?? 'N/A';
    $quantity = $_POST['buy_quantity'] ?? 1;
    $main_image = $_POST['main_image'] ?? '';
    $size = $_POST['variation_size'] ?? 'N/A'; // default if size is optional
$username = $_SESSION['Username'] ?? 'Guest';
$billing = $_SESSION['billing_info'] ?? [];

    // Connect to DB (adjust your connection params)
    $conn = new mysqli("localhost", "root", "", "marketplace");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $variationImage = $main_image; // default fallback to main product image
    $variationColor = $c;      // default fallback to submitted color or 'N/A'

    // Only try fetching variation if a color is selected (and product_id is valid)
    if ($product_id && $c !== 'N/A') {
        $stmt = $conn->prepare("SELECT variation_image, variation_color FROM product_variations WHERE product_id = ? AND variation_color = ? LIMIT 1");
        $stmt->bind_param("is", $product_id, $c);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            // Use variation data if found
            $variationImage = !empty($row['variation_image']) ? $row['variation_image'] : $main_image;
            $variationColor = !empty($row['variation_color']) ? $row['variation_color'] : $c;
            file_put_contents("debug_variation.txt", "Color: $variationColor, Image: $variationImage\n", FILE_APPEND);

        }
        $stmt->close();
    }

    $conn->close();

    $subtotal = $price * $quantity;
    $shipping = 0; // Flat shipping, or adjust if needed
    $total = $subtotal + $shipping;

} else {
    // Redirect if accessed without POST
    header("Location: homepage.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>buynow</title>
  <link rel="stylesheet" href="buynow2.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
</head>
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
      <a href="#">Be a Rider</a>
      <div class="divider">|</div>
      <a href="#">Be a Seller</a>
      <div class="divider">|</div>
      <i class='bx bx-user'></i>
       <span class="username">@<?= htmlspecialchars($username) ?></span>
    </div>
</header>
  <header class="main-header">
    <div class="container header-container">
      <a href="#" class="logo">
        <img src="../pictures/logo.png" alt="CartIT Logo">
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
        $<?= number_format($cart_total, 2) ?>
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
  <a href="#" class="categoryToggle">All Categories </a>
  <i class='bx bx-chevron-down' id="categoryToggle" style="cursor: pointer; font-size: 24px; color: white;"></i>
  <div class="dropdown" id=categoryMenu>
    <a href="#">Women's Fashion</a>
    <a href="#">Men's Fashion</a>
    <a href="#">Beauty & Fragrance</a>
    <a href="#">Sports & Activewear</a>
    <a href="#">Home & Living</a>
    <a href="#">Mobile & Gadgets</a>
    <a href="#">Gaming & Tech Accessories</a>
    <a href="#">Toys & Kid</a>
    <a href="#">Audio</a>
    <a href="#">Health & Personal Care</a>
    <a href="#">Automotive & Motor Accessories</a>
    <a href="#">Pet Care</a>
    <a href="#">Home Appliances</a>
  </div>
</div>
  <a href="#">Home</a>
  <a href="#">About Us</a>
  <a href="#">Contact Us</a>
</nav>
    </div>
  </header>

<form action="place_order.php" method="POST">
  <input type="hidden" name="status" value="buy_now">

<div class="checkout-page">
  <!-- Billing Information -->
  <section class="billing-info">
    <h2>Billing Information</h2>

    <div class="row">
      <div class="col">
        <label>First Name</label>
        <input type="text" name="first_name" placeholder="Enter First Name" value="<?= htmlspecialchars($billing['first_name'] ?? '') ?>" required>
      </div>
      <div class="col">
        <label>Last Name</label>
        <input type="text" name="last_name" placeholder="Enter Last Name" value="<?= htmlspecialchars($billing['last_name'] ?? '') ?>" required>
      </div>
    </div>

    <div class="row full">
      <label>Street Name, Building, House No.</label>
      <input type="text" name="address" placeholder="Enter Your Address" value="<?= htmlspecialchars($billing['address'] ?? '') ?>" required>

    </div>

    <div class="row">
      <div class="col">
        <label>Region</label>
        <input type="text" name="region" placeholder="Enter Your Region"  value="<?= htmlspecialchars($billing['region'] ?? '') ?>" required>

      </div>
      <div class="col">
        <label>Province</label>
        <input type="text" name="province" placeholder="Enter Your Province" value="<?= htmlspecialchars($billing['province'] ?? '') ?>" required>
      </div>
      <div class="col">
        <label>Zip Code</label>
           <input type="number" name="zip_code" placeholder="Enter Your Zip Code" value="<?= htmlspecialchars($billing['zip_code'] ?? '') ?>" required>
    </div>
</div>
    <div class="row">
      <div class="col">
        <label>Phone Number</label>
           <input type="text" name="phone" placeholder="Enter Your Phone Number" value="<?= htmlspecialchars($billing['phone'] ?? '') ?>" required>
      </div>
      <div class="col">
        <label>Email</label>
           <input type="text" name="email" placeholder="Enter Your Email" value="<?= htmlspecialchars($billing['email'] ?? '') ?>" required>
      </div>
    </div>
    <label>
    <input type="checkbox" name="remember_billing" />Remember my billing info</label>
  </section>

  <!-- Order Summary -->

  <aside class="order-summary">
    <h2>Order Summary</h2>

    <div class="item">
      <div class="thumb">
        <?php if (!empty($variationImage)): ?>
          <img src="<?= htmlspecialchars($variationImage) ?>" alt="<?= htmlspecialchars($name) ?>" style="width: 60px;">
        <?php endif; ?>
      </div>
      <div class="details">
        <p class="name"><?= htmlspecialchars($name) ?> (<?= htmlspecialchars($variationColor) ?>)</p>

        <p class="qty">x<?= htmlspecialchars($quantity) ?></p>
      </div>
      <div class="price">₱<?= number_format($subtotal, 2) ?></div>
    </div>

    <div class="summary-line">
      <span>Sub-total</span>
      <span>₱<?= number_format($subtotal, 2) ?></span>
    </div>
    <div class="summary-line">
      <span>Shipping</span>
      <span>Free</span>
    </div>

    <div class="summary-line total">
      <strong>Total</strong>
      <strong>₱<?= number_format($total, 2) ?></strong>
    </div>

   

    <!-- Hidden fields to pass data to order handler -->
    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
    <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
    <input type="hidden" name="price" value="<?= htmlspecialchars($price) ?>">
    <input type="hidden" name="color" value="<?= htmlspecialchars($variationColor) ?>">
    <input type="hidden" name="size" value="<?= htmlspecialchars($size) ?>">
    <input type="hidden" name="quantity" value="<?= htmlspecialchars($quantity) ?>">
    <input type="hidden" name="main_image" value="<?= htmlspecialchars($variationImage) ?>">
    <input type="hidden" name="total_price" value="<?= htmlspecialchars($total) ?>">

 <div class="payment">
      <p>Payment Method</p>
      <label><input type="radio" name="payment_method" value="cod" required /> Cash on Delivery</label>
      <label><input type="radio" name="payment_method" value="card" required /> Credit Card</label>
    </div>
   <div id="cardFormContainer" style="display: none;">
  <h3>Credit Card Payment</h3>

  <label>Name on Card</label>
  <input type="text" name="card_name" id="card_name">

  <label>Card Number</label>
  <input type="text" name="card_number" id="card_number" maxlength="16">

  <label>Expiration</label>
  <div style="display: flex; gap: 10px;">
    <select name="exp_month" id="exp_month">
      <option value="">Month</option>
      <?php for ($m = 1; $m <= 12; $m++) echo "<option>$m</option>"; ?>
    </select>
    <select name="exp_year" id="exp_year">
      <option value="">Year</option>
      <?php for ($y = date("Y"); $y <= date("Y")+5; $y++) echo "<option>$y</option>"; ?>
    </select>
  </div>

  <label>CVV</label>
  <input type="text" name="cvv" id="cvv" maxlength="4">

  <label>Amount</label>
  <input type="number" name="amount" id="amount" value="<?= htmlspecialchars($total) ?>" readonly>
</div>

    <button type="submit" class="place-order">Place Order</button>
  </aside>
</form>
</div>
  <!--Footer-->
  <footer class="footer">
    <div class="footer-top container">
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
        <a href="#">My Account</a>
        <a href="#">Order History</a>
        <a href="#">Shopping Cart</a>
      </div>
      <div class="footer-links">
        <h3>Helps</h3>
        <a href="#">Contact</a>
        <a href="#">Terms & Condition</a>
        <a href="#">Privacy Policy</a>
      </div>
      <div class="footer-links">
        <h3>Proxy</h3>
        <a href="#">About Us</a>
        <a href="#">Browse All Product</a>
      </div>
      <div class="footer-contact">
        <h3>Customer Supports:</h3>
        <p>(63+) 000 0000 000</p>
        <h3>Contact Us</h3>
        <p>info@cartit.com</p>
      </div>
    </div>
  
    <div class="footer-bottom container">
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
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Show/hide card form based on radio selection
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
  radio.addEventListener('change', () => {
    const isCard = radio.value === 'card';
    document.getElementById('cardFormContainer').style.display = isCard ? 'block' : 'none';

    // Toggle required on card fields
    ['card_name','card_number','exp_month','exp_year','cvv']
      .forEach(id => {
        const el = document.getElementById(id);
        if (el) el.required = isCard;
      });
  });
});

    document.getElementById('orderForm').addEventListener('submit', async function(e) {
  const method = document.querySelector('input[name="payment_method"]:checked').value;

  if (method === 'card') {
    e.preventDefault();

    // Gather payment fields
    const formData = new FormData();
    ['card_name','card_number','exp_month','exp_year','cvv','amount']
      .forEach(name => formData.append(name, document.getElementById(name).value));

    const res = await fetch('process_payment.php', {
      method: 'POST', body: formData
    });
    const result = await res.json();

    if (result.status === 'success') {
      Swal.fire('Payment Successful', result.message, 'success')
        .then(() => this.submit());
    } else {
      Swal.fire('Payment Failed', result.message, 'error');
    }
  }
  // COD => form submits to place_order.php immediately
});
  const toggleBtn = document.getElementById('categoryToggle');
  const dropdownMenu = document.getElementById('categoryMenu');

toggleBtn.addEventListener('click', function (e) {
  e.preventDefault();
  const isVisible = dropdownMenu.style.display === 'flex';
  dropdownMenu.style.display = isVisible ? 'none' : 'flex';
});
document.addEventListener("DOMContentLoaded", function() {
  const aboutText = document.querySelector(".about-text");
  const readMore = document.createElement("span");
  readMore.className = "read-more";
  readMore.textContent = "Read more...";

  if (aboutText.scrollHeight > aboutText.clientHeight + 1) {
    aboutText.after(readMore);
  }

  readMore.addEventListener("click", function() {
    aboutText.classList.toggle("expanded");
    if (aboutText.classList.contains("expanded")) {
      readMore.textContent = "Show less";
    } else {
      readMore.textContent = "Read more...";
    }
  });
});
</script>
</body>
</html>
