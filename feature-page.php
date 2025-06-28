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

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$category = $_GET['category'] ?? '';
$subcategory = $_GET['sub_category'] ?? '';
$sort = $_GET['sort'] ?? 'latest';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$priceRange = $_GET['price_range'] ?? '';

$limit = 20;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = "";
$join = "";

// Start query
// Build base SQL depending on filters
if (empty($category) && empty($subcategory) && empty($priceRange)) {
    // Show random products
    $sql = "SELECT * FROM products ORDER BY RAND() LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count total products
    $totalProducts = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
    $totalPages = max(1, ceil($totalProducts / $limit));
} else {
    // Filters applied: build query
    $sql = "SELECT DISTINCT p.* FROM products p";
    $join = "";
    $where = [];
    $params = [];
    $types = "";

    if (!empty($subcategory)) {
        $join = " INNER JOIN product_variations pv ON p.product_id = pv.product_id";
        $where[] = "pv.sub_category = ?";
        $params[] = $subcategory;
        $types .= "s";
    }

    if (!empty($category)) {
        $where[] = "p.category = ?";
        $params[] = $category;
        $types .= "s";
    }

    if (!empty($priceRange)) {
        switch ($priceRange) {
            case 'under200': $where[] = "p.price < 200"; break;
            case '300to500': $where[] = "p.price BETWEEN 300 AND 500"; break;
            case '600to1000': $where[] = "p.price BETWEEN 600 AND 1000"; break;
            case '1300to2000': $where[] = "p.price BETWEEN 1300 AND 2000"; break;
            case '2300to5000': $where[] = "p.price BETWEEN 2300 AND 5000"; break;
            case '5300to10000': $where[] = "p.price BETWEEN 5300 AND 10000"; break;
        }
    }

    $sql .= $join;

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // Sorting
    switch ($sort) {
        case "price_asc": $sql .= " ORDER BY p.price ASC"; break;
        case "price_desc": $sql .= " ORDER BY p.price DESC"; break;
        case "top_sales": $sql .= " ORDER BY p.sales DESC"; break;
        default: $sql .= " ORDER BY p.created_at DESC"; break;
    }

    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Prepare failed: " . $conn->error);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count query
    $countSql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM products p" . $join;
    if (!empty($where)) {
        $countSql .= " WHERE " . implode(" AND ", $where);
    }

    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) die("Count prepare failed: " . $conn->error);
    $countTypes = substr($types, 0, -2); // exclude ii
    $countParams = array_slice($params, 0, -2);
    if (!empty($countTypes)) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
    $countStmt->execute();
    $totalProducts = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;
    $totalPages = max(1, ceil($totalProducts / $limit));
    $countStmt->close();
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Features Product</title>
  <link rel="stylesheet" href="feature2.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://kit.fontawesome.com/38db01bbb3.js" crossorigin="anonymous"></script>
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


  <main>
    <div class="content-container">
<aside class="sidebar">
  <div class="sidebar-header">
    <form method="GET" id="filterForm" action="feature-page.php">

      <div class="filter-section">
        <h4 class="section-title">Category</h4>
        <div class="category-group">
          <label><input type="radio" name="category" value="" <?= $category === '' ? 'checked' : '' ?>> All</label>
          <?php
          $catResult = $conn->query("SELECT DISTINCT category FROM products");
          while ($row = $catResult->fetch_assoc()) {
              $checked = ($category === $row['category']) ? 'checked' : '';
              echo '<label><input type="radio" name="category" value="' . htmlspecialchars($row['category']) . '" ' . $checked . '> ' . htmlspecialchars($row['category']) . '</label>';
          }
          ?>
        </div>
      </div>

      <?php if (!empty($category)): ?>
        <div class="filter-section sub-category">
          <h4 class="section-title">Subcategory</h4>
          <div class="filter-group">
            <?php
            $stmt = $conn->prepare("
                SELECT DISTINCT pv.sub_category 
                FROM product_variations pv
                JOIN products p ON pv.product_id = p.product_id
                WHERE p.category = ?
            ");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $subResult = $stmt->get_result();

            if ($subResult && $subResult->num_rows > 0) {
                while ($row = $subResult->fetch_assoc()) {
                    $checked = ($subcategory === $row['sub_category']) ? 'checked' : '';
                    echo '<label><input type="radio" name="sub_category" value="' . htmlspecialchars($row['sub_category']) . '" ' . $checked . '> ' . htmlspecialchars($row['sub_category']) . '</label>';
                }
            }
            ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="filter-section">
        <h4 class="section-title">Price Range</h4>
        <div class="filter-group">
          <label><input type="radio" name="price_range" value="" <?= empty($_GET['price_range']) ? 'checked' : '' ?>> All prices</label>
          <label><input type="radio" name="price_range" value="under200" <?= (isset($_GET['price_range']) && $_GET['price_range'] === 'under200') ? 'checked' : '' ?>> Under 200</label>
          <label><input type="radio" name="price_range" value="300to500" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '300to500') ? 'checked' : '' ?>> 300 - 500</label>
          <label><input type="radio" name="price_range" value="600to1000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '600to1000') ? 'checked' : '' ?>> 600 - 1000</label>
          <label><input type="radio" name="price_range" value="1300to2000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '1300to2000') ? 'checked' : '' ?>> 1300 - 2000</label>
          <label><input type="radio" name="price_range" value="2300to5000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '2300to5000') ? 'checked' : '' ?>> 2300 - 5000</label>
          <label><input type="radio" name="price_range" value="5300to10000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '5300to10000') ? 'checked' : '' ?>> 5300 - 10000</label>
        </div>
      </div><?php if (!empty($category) || !empty($subcategory) || !empty($priceRange)): ?>
        <button type="button" class="apply-btn" onclick="window.location.href='feature-page.php'">Clear Filters</button>
      <?php endif; ?>
    </div>
</aside>
<div class="section featured-products">
  <div class="sort-container">
    <h4 class="sort-label">Sort By</h4> <!-- Changed from 'section-label' to match CSS -->
    <select name="sort" id="sortSelect" class="sort-button" onchange="document.getElementById('filterForm').submit()">
      <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest</option>
      <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
      <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
      <option value="top_sales" <?= $sort === 'top_sales' ? 'selected' : '' ?>>Top Sales</option>
    </select>
  </div>

    <div class="products-grid">
      <?php include 'feature-products.php'; ?>
    </div>

  <div class="pagination-circle">
    <?php
    $baseUrl = "?category=" . urlencode($category) . "&sub_category=" . urlencode($subcategory) . "&sort=" . urlencode($sort) . "&page=";
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i === $page ? 'active' : '';
        echo "<a href='{$baseUrl}{$i}' class='circle-btn $activeClass'>$i</a> ";
    }
    ?>
  </div>
</div>
</div>
</main>


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
const toggleBtn = document.getElementById('categoryToggle');
const dropdownMenu = document.getElementById('categoryMenu');

if (toggleBtn && dropdownMenu) {
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
}

document.querySelectorAll('#filterForm input[type="radio"], #sort').forEach(el => {
  el.addEventListener('change', () => {
    document.getElementById('filterForm').submit();
  });
});
document.querySelectorAll('input[name="price_range"]').forEach(el => {
  el.addEventListener('change', () => {
    document.getElementById('filterForm').submit();
  });
});
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
