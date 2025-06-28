<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['AccountID'])) {
    die("Unauthorized access.");
}


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
$buyer_id = $_SESSION['AccountID'];

$regions = [
    "region1" => "Region 1",
    "region2" => "Region 2",
    "region3" => "Region 3",
    "region4-a" => "Region 4-A",
    "mimaropa" => "MIMAROPA",
    "region5" => "Region 5",
    "region6" => "Region 6",
    "region7" => "Region 7",
    "region8" => "Region 8",
    "region9" => "Region 9",
    "region10" => "Region 10",
    "region11" => "Region 11",
    "region12" => "Region 12",
    "region13" => "Region 13",
    "ncr" => "NCR",
    "barmm" => "BARMM"
];

// If form submitted, handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_id = $_POST['address_id'] ?? null;
    $full_name = $_POST['full_name'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $phone_number = $_POST['phone'] ?? '';
    $street_address = $_POST['street_address'] ?? '';
    $region = $_POST['region'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $province = $_POST['province'] ?? '';

    if (!$address_id) {
        die('Address ID missing.');
    }

    $stmt = $pdo->prepare("UPDATE buyer_addresses SET 
        full_name = ?, 
        phone_number = ?, 
        street_address = ?, 
        barangay = ?, 
        province = ?, 
        region = ?, 
        postal_code = ? 
        WHERE address_id = ? AND buyer_id = ?");

    $stmt->execute([
        $full_name,
        $phone_number,
        $street_address,
        $barangay,
        $province,
        $region,
        $postal_code,
        $address_id,
        $buyer_id
    ]);

    if ($stmt->rowCount() > 0) {
        // Redirect with success flag after update
        header("Location: buyerAddress.php?update=success");
        exit;
    } else {
        $error = "No changes made or update failed.";
    }
}

// If not POST (or after update attempt), load address to show in form
$address_id = $_GET['address_id'] ?? null;

if (!$address_id) {
    die("Address ID is required to edit address.");
}

$stmt = $pdo->prepare("SELECT * FROM buyer_addresses WHERE address_id = ? AND buyer_id = ?");
$stmt->execute([$address_id, $buyer_id]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$address) {
    die("Address not found or you do not have permission to edit it.");
}

$current_region = $address['region'] ?? '';

$username = $_SESSION['Username'] ?? 'Guest';
$cart_count = 0;
$cart_total = 0.00;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
<title>Edit Address</title>
   <link rel="stylesheet" href="edit-add.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
   <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
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


<body>
  <main class="dashboard">
  <div class="main-container">
  <!-- Sidebar -->
  <aside class="sidebar" aria-label="Account Navigation">
    <h2>Navigation</h2>
    <nav>
      <ul class="nav-list">
        <li><a href="buyerdashboard.php"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
        <li><a href="orderhistory.php"><i class='bx bx-history'></i> Order History</a></li>
        <li class="active"><a href="buyerAddress.php"><i class='bx bx-map'></i> Address</a></li>
        <li><a href="buyer.settings.php"><i class='bx bx-cog'></i> Settings</a></li>
        <li id="logout-btn" style="cursor: pointer;"><i class='bx bx-log-out'></i> Log-out</li>
      </ul>
    </nav>
  </aside>

  
    <div class="order-history">
  <form class="edit-address" method="POST" action="edit-add.php">
   <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($address['address_id'] ?? ''); ?>">
    <h2>Edit Address</h2>
    <div class="form-group">
      <div class="input-box">
        <label for="full-name">Full Name</label>
       <input type="text" name="full_name" id="full-name" placeholder="Enter your full name"
       value="<?php echo htmlspecialchars($address['full_name'] ?? ''); ?>" required>
      </div>
      <div class="input-box">
        <label for="barangay">Barangay</label>
        <input type="text" name="barangay" id="barangay" placeholder="Enter your barangay"
       value="<?php echo htmlspecialchars($address['barangay'] ?? ''); ?>" required>
      </div>
      <div class="input-box">
        <label for="phone">Phone Number</label>
        <input type="tel" name="phone" id="phone" placeholder="Enter your phone number"
       value="<?php echo htmlspecialchars($address['phone_number'] ?? ''); ?>" required>
      </div>
      <div class="input-box">
        <label for="street">Street Name, Building, House No.</label>
       <input type="text" name="street_address" id="street" placeholder="Enter street name, building, house no."
       value="<?php echo htmlspecialchars($address['street_address'] ?? ''); ?>" required>
      </div>
<div class="input-box">
  <label for="region">Region</label>
  <select name="region" id="region" required>
    <option value="">Choose a Region</option>
    <?php foreach ($regions as $code => $label): ?>
      <option value="<?= htmlspecialchars($code) ?>" <?= $current_region === $code ? 'selected' : '' ?>>
        <?= htmlspecialchars($label) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>
      <div class="input-box">
        <label for="postal">Postal</label>
        <input type="text" name="postal_code" id="postal" placeholder="Enter your postal code"
       value="<?php echo htmlspecialchars($address['postal_code'] ?? ''); ?>" required>
      </div>
      <div class="input-box">
        <label for="province">Province</label>
        <select name="province" id="province" required>
          <option value="">Choose a Province</option>
        </select>
      </div>
    </div>

    <div class="button-group">
      <a href="buyerAddress.php"><button type="button" class="cancel-btn">Cancel</button></a>
      <button type="submit" class="save-btn">Save Changes</button>
    </div>
  </form>
</div>

<script>
  document.querySelector('.save-btn').addEventListener('click', function () {
    Swal.fire({
      title: 'Saved Changes',
      icon: 'success',
      confirmButtonText: 'Back',
      customClass: {
        content: 'no-wrap-text'
      },
      showClass: {
        popup: 'animate__animated animate__fadeInDown'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      }
    });
  });
</script>
    </main>
    </body>
    <footer class="footer">
    <div class="footer-top">
      <div class="footer-logo">
        <img src="cartit.png" alt="CartIT Logo">
        <p>CartIT – Where Smart Shopping Begins. Discover quality finds, hot deals, and fast delivery—all in one cart.</p>
        <div class="social-icons">
          <i class='bx bxl-facebook'></i>
          <i class='bx bxl-instagram'></i>
          <i class='bx bxl-twitter'></i>
        </div>
      </div>
      <div class="footer-links">
        <h3>My Account</h3>
        <a href="dashboard.html">My Account</a>
        <a href="#">Order History</a>
        <a href="#">Shopping Cart</a>
      </div>
      <div class="footer-links">
        <h3>Helps</h3>
        <a href="#">Contact</a>
        <a href="#">Terms & Condition</a>
        <a href="Privacy Policy.html">Privacy Policy</a>
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
  // Define provinces for each region 
  const province = {
    "region1": ["Ilocos Norte", "Ilocos Sur", "La Union", "Pangasinan"],
    "region2": ["Cagayan", "Isabela", "Nueva Vizcaya", "Quirino"],
    "region3": ["Aurora", "Bataan", "Bulacan", "Nueva Ecija", "Pampanga", "Tarlac", "Zambales"],
    "region4-a": ["Batangas", "Cavite", "Laguna", "Quezon", "Rizal"],
    "mimaropa": ["Marinduque", "Occidental Mindoro", "Oriental Mindoro", "Palawan", "Romblon"],
    "region5": ["Albay", "Camarines Norte", "Camarines Sur", "Catanduanes", "Masbate", "Sorsogon"],
    "region6": ["Aklan", "Antique", "Capiz", "Iloilo", "Guimaras", "Negros Occidental"],
    "region7": ["Bohol", "Cebu", "Negros Oriental", "Siquijor"],
    "region8": ["Biliran", "Eastern Samar", "Leyte", "Northern Samar", "Western Samar", "Southern Leyte", "Tacloban"],
    "region9": ["Zamboanga del Norte", "Zamboanga del Sur", "Zamboanga Sibugay"],
    "region10": ["Bukidnon", "Camiguin", "Lanao del Norte", "Misamis Occidental", "Misamis Oriental"],
    "region11": ["Davao de Oro", "Davao del Norte", "Davao del Sur", "Davao Occidental", "Davao Oriental"],
    "region12": ["North Cotabato", "Sarangani", "South Cotabato", "Sultan Kudarat"],
    "region13": ["Agusan del Sur", "Agusan del Norte", "Dinagat Islands", "Surigao del Norte", "Surigao del Sur"],
    "barmm": ["Basilan", "Lanao del Sur", "Maguindanao del Norte", "Maguindanao del Sur", "Sulu", "Tawi-Tawi"],
    "ncr": ["Metro Manila"]
  };

  // On region change, update province dropdown
  document.getElementById('region').addEventListener('change', function() {
    const selectedRegion = this.value;
    const provinceSelect = document.getElementById('province');
    provinceSelect.innerHTML = '<option value="">Choose a Province</option>';
    if (province[selectedRegion]) {
      province[selectedRegion].forEach(function(prov) {
        const opt = document.createElement('option');
        opt.value = prov;
        opt.textContent = prov;
        provinceSelect.appendChild(opt);
      });
    }
  });

  // Prefill province dropdown and select saved province on page load
  window.addEventListener('DOMContentLoaded', () => {
    const savedRegion = '<?= addslashes($address['region'] ?? '') ?>';
    const savedProvince = '<?= addslashes($address['province'] ?? '') ?>';

    if (savedRegion) {
      const regionSelect = document.getElementById('region');
      const provinceSelect = document.getElementById('province');
      
      regionSelect.value = savedRegion;
      
      provinceSelect.innerHTML = '<option value="">Choose a Province</option>';
      if (province[savedRegion]) {
        province[savedRegion].forEach(function(prov) {
          const opt = document.createElement('option');
          opt.value = prov;
          opt.textContent = prov;
          if(prov === savedProvince) {
            opt.selected = true;
          }
          provinceSelect.appendChild(opt);
        });
      }
    }
  });

</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    </html>