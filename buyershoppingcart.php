
<?php
session_start();

// Handle AJAX requests for cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'remove_item':
            $key = (int)$_POST['key'];
            if (isset($_SESSION['cart'][$key])) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex array
            }
            echo json_encode(['success' => true]);
            exit;
            
        case 'update_quantity':
            $key = (int)$_POST['key'];
            $quantity = (int)$_POST['quantity'];
            if (isset($_SESSION['cart'][$key]) && $quantity > 0) {
                $_SESSION['cart'][$key]['quantity'] = $quantity;
            }
            echo json_encode(['success' => true]);
            exit;
    }
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

function formatPeso($amount) {
    return '₱' . number_format($amount, 2);
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$shipping = 0;

// Handle regular form submissions (add to cart)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    // ... keep existing code (collect data from POST, fetch variation info, save item to cart)
    $product_id = $_POST['product_id'] ?? 0;
    $name = $_POST['product_name'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $c = $_POST['variation_color'] ?? 'N/A';
    $quantity = (int)($_POST['buy_quantity'] ?? 1);
    $main_image = $_POST['main_image'] ?? '';
    $size = $_POST['size'] ?? 'N/A';

    $conn = new mysqli("localhost", "root", "", "marketplace");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $variationImage = $main_image;
    $variationColor = $c;

    if ($product_id && $c !== 'N/A') {
        $stmt = $conn->prepare("SELECT variation_image, variation_color FROM product_variations WHERE product_id = ? AND variation_color = ? LIMIT 1");
        $stmt->bind_param("is", $product_id, $c);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $variationImage = $row['variation_image'] ?: $main_image;
            $variationColor = $row['variation_color'] ?: $c;
        }
        $stmt->close();
    }

    $conn->close();

    $cart[] = [
        'product_id' => $product_id,
        'product_name' => $name,
        'price' => $price,
        'variation_color' => $variationColor,
        'quantity' => $quantity,
        'main_image' => $variationImage,
        'size' => $size
    ];
    $_SESSION['cart'] = $cart;
}

function protected_link($target) {
    global $username;
    return ($username === 'Guest') ? 'finalslogin.php' : $target;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Buyer Shopping Cart</title>
  <link rel="stylesheet" href="buyershoppingcart.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
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
<form action="search-results.php" method="get" class="search-bar">
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


  <header class="shopping-header">
      <h1>My Shopping Cart</h1>
  </header>


<section class="cart-container">
  <!-- Shopping Cart Table -->
  <section class="cart-table">
    <header class="shoppingcart-header">
      <h2>My Shopping Cart</h2>
    </header>
    <header class="cart-row cart-header">
      <span></span>
      <span>Product</span>
      <span>Price</span>
      <span>Quantity</span>
      <span>Sub-Total</span>
      <span></span>
    </header>

    <?php if (empty($cart)): ?>
      <p>Your cart is empty.</p>
    <?php else: ?>
      <?php foreach ($cart as $key => $item): 
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
      ?>
<article class="cart-row" data-key="<?= htmlspecialchars($key) ?>">
  <input type="checkbox" checked />
  <div class="product-info">
    <img src="<?= htmlspecialchars($item['main_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" />
    <span><?= htmlspecialchars($item['product_name']) ?></span>
  </div>
  <span class="unit-price" data-price="<?= htmlspecialchars($item['price']) ?>"><?= formatPeso($item['price']) ?></span>
  <div class="quantity-controls">
    <button type="button" class="decrease" aria-label="Decrease quantity">–</button>
    <span class="quantity"><?= $item['quantity'] ?></span>
    <button type="button" class="increase" aria-label="Increase quantity">+</button>
    <!-- keep your hidden inputs -->
    <input type="hidden" name="quantities[]" class="quantity-input" value="<?= $item['quantity'] ?>">
    <input type="hidden" name="product_ids[]" value="<?= $item['product_id'] ?>">
  </div>
  <span class="subtotal"><?= formatPeso($subtotal) ?></span>
  <button type="button" class="remove-btn" aria-label="Remove item">&times;</button>
</article>

      <?php endforeach; ?>
    <?php endif; ?>

    <button class="return-btn" onclick="window.location.href='homepage.php'">Return to shop</button>
  </section>

  <!-- Checkout Box -->
   <form action ="place_order.php" method="POST" id="orderForm"> 
    <input type="hidden" name="order_type" value="cart">
  
  <aside class="checkout-box">
    <h3>Checkout</h3>
    <div class="checkout-row">
      <span>Sub-total</span>
      <span class="checkout-subtotal"><?= formatPeso($total) ?></span>
    </div>
    <div class="checkout-row">
      <span>Shipping</span>
      <span>Free</span>
    </div>
    <div class="checkout-row total">
      <span>Total</span>
      <span class="checkout-total"><?= formatPeso($total + $shipping) ?></span>
    </div>
   <button class="checkout-btn" type="submit">Place Order</button>
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

</aside>
</section>


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
document.addEventListener("DOMContentLoaded", () => {
  
  function updateCartTotal() {
    let total = 0;
    const cartRows = document.querySelectorAll(".cart-row:not(.cart-header)");

    cartRows.forEach(row => {
      const unitPriceElem = row.querySelector(".unit-price");
      const quantityElem = row.querySelector(".quantity");
      const subtotalElem = row.querySelector(".subtotal");

      if (!unitPriceElem || !quantityElem || !subtotalElem) return;

      const unitPrice = parseFloat(unitPriceElem.dataset.price);
      const quantity = parseInt(quantityElem.textContent);
      const subtotal = unitPrice * quantity;

      subtotalElem.textContent = `₱${subtotal.toFixed(2)}`;
      total += subtotal;
    });

    const subtotalElement = document.querySelector(".checkout-subtotal");
    const totalElement = document.querySelector(".checkout-total");
    
    if (subtotalElement) subtotalElement.textContent = `₱${total.toFixed(2)}`;
    if (totalElement) totalElement.textContent = `₱${total.toFixed(2)}`;
  }

  // Handle quantity changes and removal
  document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('decrease')) {
      const row = e.target.closest('.cart-row');
      const quantityElem = row.querySelector('.quantity');
      const quantityInput = row.querySelector('.quantity-input');
      const key = row.dataset.key;
      
      let qty = parseInt(quantityElem.textContent);
      if (qty > 1) {
        qty--;
        quantityElem.textContent = qty;
        quantityInput.value = qty;
        
        // Update server
        await updateQuantityOnServer(key, qty);
        updateCartTotal();
      }
    }
    
    if (e.target.classList.contains('increase')) {
      const row = e.target.closest('.cart-row');
      const quantityElem = row.querySelector('.quantity');
      const quantityInput = row.querySelector('.quantity-input');
      const key = row.dataset.key;
      
      let qty = parseInt(quantityElem.textContent);
      qty++;
      quantityElem.textContent = qty;
      quantityInput.value = qty;
      
      // Update server
      await updateQuantityOnServer(key, qty);
      updateCartTotal();
    }
    
    if (e.target.classList.contains('remove-btn')) {
      const row = e.target.closest('.cart-row');
      const key = row.dataset.key;
      
      // Remove from server
      const success = await removeItemFromServer(key);
      if (success) {
        row.remove();
        updateCartTotal();
        
        // Check if cart is empty
        const remainingRows = document.querySelectorAll('.cart-row:not(.cart-header)');
        if (remainingRows.length === 0) {
          location.reload(); // Refresh to show empty cart message
        }
      }
    }
  });

  async function updateQuantityOnServer(key, quantity) {
    try {
      const response = await fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_quantity&key=${key}&quantity=${quantity}`
      });
      
      const result = await response.json();
      return result.success;
    } catch (error) {
      console.error('Error updating quantity:', error);
      return false;
    }
  }

  async function removeItemFromServer(key) {
    try {
      const response = await fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove_item&key=${key}`
      });
      
      const result = await response.json();
      return result.success;
    } catch (error) {
      console.error('Error removing item:', error);
      return false;
    }
  }

  // Payment method toggle
  document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
    input.addEventListener('change', function () {
      const cardForm = document.getElementById('cardFormContainer');
      if (cardForm) {
        cardForm.style.display = this.value === 'card' ? 'block' : 'none';
      }
    });
  });

  // Order form submission
  document.getElementById('orderForm').addEventListener('submit', async function(e) {
    const paymentMethodInput = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethodInput) return;
    
    const paymentMethod = paymentMethodInput.value;

    if (paymentMethod === 'card') {
      e.preventDefault();
      
      // Handle card payment validation here if needed
      alert('Card payment processing would go here');
    }
  });

  // Initial total calculation
  updateCartTotal();
});
</script>
</body>
</html>