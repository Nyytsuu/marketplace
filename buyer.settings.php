<?php
session_start();
include('db_connection.php');

$username = $_SESSION['Username'] ?? null;

if (!$username) {
    die("You must be logged in to update your account.");
}

$stmt = $pdo->prepare("SELECT AccountID FROM buyers WHERE Username = ?");
$stmt->execute([$username]);
$buyer = $stmt->fetch(PDO::FETCH_ASSOC);

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
  <meta charset="UTF-8">
  <title>Account Settings</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="buyer.settings.css">
  <!-- SweetAlert2 CDN animation pop up-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" />

  <!--extras-->
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
   <a href="#" class="categoryToggle">All Categories </a>
  <i class='bx bx-chevron-down' id="categoryToggle" style="cursor: pointer; font-size: 24px; color: white;"></i>
  <div class="dropdown" id=categoryMenu>
   <a href="feature-page.php?category=Women's Fashion">Women's Fashion</a>
    <a href="feature-page.php?category=Men's Fashion">Men's Fashion</a>
    <a href="feature-page.php?category=Beauty & Fragrance">Beauty & Fragrance</a>
    <a href="feature-page.php?category=Sports">Sports & Activewear</a>
    <a href="feature-page.php?category=Home & Living">Home & Living</a>
    <a href="feature-page.php?category=Mobile & Gadgets">Mobile & Gadgets</a>
    <a href="feature-page.php?category=Gaming & Tech Accessories">Gaming & Tech Accessories</a>
    <a href="feature-page.php?category=Toys & Kid">Toys & Kid</a>
    <a href="feature-page.php?category=Audio">Audio</a>
    <a href="feature-page.php?category=Health & Personal Care">Health & Personal Care</a>
    <a href="feature-page.php?category=Automotive & Motor Accessories">Automotive & Motor Accessories</a>
    <a href="feature-page.php?category=Pet Care">Pet Care</a>
    <a href="feature-page.php?category=Home Appliances">Home Appliances</a>
  </div>
</div>
            <a href="homepage.php">Home</a>
            <a href="Header_About_Us/aboutus.php">About</a>
            <a href="Header_Contact_Us/contactus.php">Contact</a>
</nav>
    </div>
</header>
  

<div class="layout-wrapper">
  <!-- Sidebar -->
  <aside class="sidebar" aria-label="Account Navigation">
    <h2>Navigation</h2>
    <nav>
      <ul class="nav-list">
        <li><a href="buyerdashboard.php"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
        <li><a href="orderhistory.php"><i class='bx bx-history'></i> Order History</a></li>
        <li><a href="buyerAddress.php"><i class='bx bx-map'></i> Address</a></li>
        <li class="active"><a href="buyer.settings.php"><i class='bx bx-cog'></i> Settings</a></li>
        <li id="logout-btn" style="cursor: pointer;"><i class='bx bx-log-out'></i> Log-out</li>
      </ul>
    </nav>
  </aside>

<main class="content">   
  <div class="content">

<!-- Account Settings -->
<section class="account-section">

<h2 class="h2-title">Account Settings</h2>
<form id="accountForm" method="POST" action="update_buyer_account.php">
  <div class="flex flex-wrap gap-6">
    <div class="flex-1 space-y-4">
      <input type="text" name="first_name" placeholder="Enter your first name" class="w-full p-2 border rounded" required />
      <input type="text" name="last_name" placeholder="Enter your last name" class="w-full p-2 border rounded" required />
      <input type="email" name="email" placeholder="Enter your email" class="w-full p-2 border rounded" required />
      <input type="text" name="phone_number" placeholder="Enter your phone number" class="w-full p-2 border rounded" required />
<button type="button" id="confirmSave" class="bg-blue-800 text-white px-4 py-2 rounded hover:bg-blue-900">
  Save Changes
</button>
    </div>
  </div>
</form>

  <div class="profile-upload">
   <div class="profile-preview" id="profilePreview"><?php include 'get_buyer_image.php'; ?></div>
                
<form id="uploadForm" action="upload_buyerimg.php" method="post" enctype="multipart/form-data">

  <label for="Upload" style="cursor: pointer; background-color: #ffffff; color: #004AAD; border: 1px solid #E6E6E6;  padding: 8px 16px; border-radius: 5px;">
    Choose Image
  </label>  
  <input type="file" name="image" id="Upload" style="display: none;" required>
</form>
 </div>
   </div>
      </section>

<!-- Change Password -->
<section class="password-section">

  <h2 class="h2-title">Change Password</h2>
  <form method="POST" action="change_password.php">
    <div class="grid md:grid-cols-2 gap-4">
      
      <div class="md:col-span-2 relative">
        <input type="password" id="current-password" name="current_password" placeholder="Current Password" class="p-2 border rounded w-full" required />
        <i class='bx bx-show eye-icon' onclick="togglePassword('current-password', this)"></i>
      </div>

      <div class="md:col-span-2 grid grid-cols-2 gap-4">
        <div class="relative">
          <input type="password" id="new-password" name="new_password" placeholder="New Password" class="p-2 border rounded w-full" required />
          <i class='bx bx-show eye-icon' onclick="togglePassword('new-password', this)"></i>
        </div>
        <div class="relative">
          <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password" class="p-2 border rounded w-full" required />
          <i class='bx bx-show eye-icon' onclick="togglePassword('confirm-password', this)"></i>
        </div>
      </div>
    </div>

    <button type="submit" class="mt-4 bg-blue-800 text-white px-4 py-2 rounded hover:bg-blue-900">
      Change Password
    </button>
  </form>
</section>

<!-- Danger Zone -->
      <section class="bg-white p-6 rounded shadow border border-red-300">
      
        <h2 class="text-red-600 font-semibold mb-2">Danger Zone</h2>
        <p class="text-sm mb-3 text-gray-700">Deleting your account is permanent and cannot be undone.</p>
        <ul class="list-disc list-inside text-sm text-red-600 mb-3">
          <li>Delete your order and payout history</li>
          <li>Prevent any future logins using this account</li>
        </ul>

<form method="POST" action="delete_buyer_account.php" onsubmit="return confirmDelete();">
  <button id="delete-btn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
    Delete My Account
  </button>
</form>
        <p class="mt-3 text-sm text-gray-600">Feel free to contact <a href="mailto:cartIt.support@gmail.com" class="text-blue-600">cartIt.support@gmail.com</a> with any questions.</p>
      </section>
  </div>
</main>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Handle URL parameters for success/error messages
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Handle success messages
    if (urlParams.get('success') === 'password_updated') {
        Swal.fire({
            icon: 'success',
            title: 'Password Updated',
            text: 'Your password has been changed successfully.',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // Handle error messages
    const errorParam = urlParams.get('error');
    if (errorParam) {
        let errorMessage = '';
        switch(errorParam) {
            case 'password_mismatch':
                errorMessage = 'New passwords do not match.';
                break;
            case 'incorrect_password':
                errorMessage = 'The current password you entered is incorrect.';
                break;
            case 'empty_fields':
                errorMessage = 'Please fill in all password fields.';
                break;
            case 'user_not_found':
                errorMessage = 'User not found. Please contact support.';
                break;
            case 'update_failed':
                errorMessage = 'Failed to update password. Please try again.';
                break;
            case 'database_error':
                errorMessage = 'A database error occurred. Please try again later.';
                break;
            case 'invalid_request':
                errorMessage = 'Invalid request method.';
                break;
            default:
                errorMessage = 'An unknown error occurred.';
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// Password toggle function
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    if (field.type === "password") {
      field.type = "text";
      icon.classList.replace("bx-show", "bx-hide");
    } else {
      field.type = "password";
      icon.classList.replace("bx-hide", "bx-show");
    }
}

// Logout functionality
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
      window.location.href = 'buyer_logout.php';
    }
  });
});

// Image upload functionality
const input = document.getElementById('Upload');
input.addEventListener('change', function () {
  if (this.files.length > 0) {
    alert("Uploading image...");
    document.getElementById('uploadForm').submit();
  }
});

// Delete account functionality
document.getElementById('delete-btn').addEventListener('click', function (e) {
  e.preventDefault();

  Swal.fire({
    title: 'Are you sure?',
    text: "Deleting your account is permanent and cannot be undone.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
    backdrop: true,
    showClass: {
      popup: 'animate__animated animate__fadeInDown'
    },
    hideClass: {
      popup: 'animate__animated animate__fadeOutUp'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      document.querySelector('form[action="delete_buyer_account.php"]').submit();
    }
  });
});

// Category dropdown functionality
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

document.getElementById('confirmSave').addEventListener('click', function (e) {
  Swal.fire({
    title: 'Are you sure?',
    text: "Do you want to save the changes to your account?",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#004AAD',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, save it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('accountForm').submit();
    }
  });
});

</script>
</body>
</html>