<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get seller ID from session
$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    die("Seller not logged in.");
}

// Get products for this specific seller
$products = include 'showproductimage.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Shop</title>
  <link rel="stylesheet" href="sellershop4.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
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
            <a href="sellerdashboard.php" class="menu-item">
              <i class="bx bx-home"></i> 
              <span>Dashboard</span>
            </a>
          </li>
          <li class="sidebar-list-item">
            <a href="sellershop.php" class="menu-item active">
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

    <!-- Main Content -->
    <main class="main-container">
      <section class="shop-section">
        <h2>Your Shop</h2>
        <p>Manage your store — view and update your shop information.</p>
        <button type="button" class="edit-shop-btn" onclick="window.location.href='editshopprofile.php'">Edit Your Shop</button>

        <!-- Flex container for left and right columns -->
        <div class="shop-main-info">
          <!-- LEFT COLUMN -->
          <div class="shop-left">
            <div class="card-section shop-profile">
              <div class="shop-info">
                <div class="shop-avatar">
                  <?php include 'get_profile_image.php';?>
                </div>
                <div>
                  <div class="seller-info" style="font-weight: bold;">
                    <?php include 'show-user-info.php'; ?>
                  </div>
                  <p><span class="stars">★★★★★</span></p>
                  <p><i class='bx bx-phone'></i> <?php include 'showsellernumber.php';?> </p>
                  <p><i class='bx bx-envelope'></i> <?php include 'show-seller-email.php';?></p>
                </div>
              </div>
            </div>

            <!-- ABOUT SECTION BELOW PROFILE -->
            <div class="about">
              <div class="card-section shop-about">
                <h3>About</h3>
                <p><?php include 'show-seller-about.php';?></p>
                <div class="shop-stats">
                  <div><span>Products:</span> <?php include 'show-number-products.php' ?></div>
                  <div><span>Ratings:</span> <a href="#">4.8/5</a></div>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN - PRODUCTS -->
          <div class="shop-right">
            <div class="card-section shop-products">
              <div class="product-header">
                <h3 class="products-heading">All Products</h3>
              </div>
              <div class="product-list">
                <div class="product-container">
                  <?php
                  if (!empty($products) && is_array($products)) {
                      foreach ($products as $row) {
                          // Ensure we only show products from the current seller
                          if (isset($row['seller_id']) && $row['seller_id'] == $seller_id) {
                              $imagePath = !empty($row['main_image']) ? htmlspecialchars($row['main_image']) : 'pics/default.jpg';
                              $productName = htmlspecialchars($row['product_name'] ?? 'Unknown Product');
                              $price = number_format($row['price'] ?? 0, 2);
                              $productId = htmlspecialchars($row['product_id'] ?? '');
                              
                              echo "<div class='product-item'>";
                              echo "<img src='" . $imagePath . "' alt='" . $productName . "' class='product-image'>";
                              echo "<h4 class='product-name'>" . $productName . "</h4>";
                              echo "<p class='product-price'>₱" . $price . "</p>";
                              echo "</div>";
                          }
                      }
                  } else {
                      echo "<div class='no-products'>";
                      echo "<i class='bx bx-package'></i>";
                      echo "<p>No products found. Start by adding your first product!</p>";
                      echo "<button type='button' class='add-product-btn' onclick=\"window.location.href='sellerproductss.php'\">Add Product</button>";
                      echo "</div>";
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</div>

<script>
// Dropdown functionality
function toggleDropdown() {
    const dropdown = document.getElementById("dropdownMenu");
    dropdown.classList.toggle("show");
}

// Close dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.matches('.dropdown-btn') && !event.target.matches('.bx-chevron-down')) {
        const dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            const openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}
</script>
</body>
</html>