<?php
session_start();
include('db_connection.php');

// Debug: Check what's in the session
error_log("Session contents: " . print_r($_SESSION, true));

// Check if user is logged in - try multiple possible session variable names
$buyer_id = null;
$username = 'User';

if (isset($_SESSION['AccountID'])) {
    $buyer_id = $_SESSION['AccountID'];
} elseif (isset($_SESSION['user_id'])) {
    $buyer_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['id'])) {
    $buyer_id = $_SESSION['id'];
} elseif (isset($_SESSION['buyer_id'])) {
    $buyer_id = $_SESSION['buyer_id'];
}

// If no valid session found, redirect to login
if (!$buyer_id) {
    // Log the redirect for debugging
    error_log("No valid session found. Redirecting to login. Session data: " . print_r($_SESSION, true));
    header("Location: finalslogin.php");
    exit();
}

// Get username for display
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} elseif (isset($_SESSION['email'])) {
    $username = $_SESSION['email'];
}

try {
    $stmt = $pdo->prepare("SELECT * FROM buyer_addresses WHERE buyer_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->execute([$buyer_id]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $addresses = [];
    $error_message = "Error fetching addresses: " . $e->getMessage();
}

// Calculate cart info if exists in session
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartIT - My Addresses</title>
    <link rel="stylesheet" href="Buyer_Address1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <!-- Second Header -->
    <header class="second-header">
        <div class="second-container">
            <nav class="nav-links">
                <div class="dropdown-wrapper">
                    <a href="../Overall Categories/categories/categories.html" class="categoryToggle">All Categories </a>
                    <i class='bx bx-chevron-down' id="categoryToggle" style="cursor: pointer; font-size: 24px; color: white;"></i>
                    <div class="dropdown" id="categoryMenu">
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
            <a href="Header_About_Us/aboutus.php">About Us</a>
            <a href="Header_Contact_Us/contactus.php">Contact Us</a>
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
                    <li class="active"><a href="buyerAddress.php"><i class='bx bx-map'></i> Address</a></li>
                    <li><a href="buyer.settings.php"><i class='bx bx-cog'></i> Settings</a></li>
                    <li id="logout-btn" style="cursor: pointer;"><i class='bx bx-log-out'></i> Log-out</li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="content">    
            <section class="content-area">
                <header class="address-header">
                    <h2>My Address</h2>
                    <a href="add-address.php" class="add-btn">
                        <i class='bx bx-plus'></i> Add New Address
                    </a>
                </header>

                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($addresses)): ?>
                    <div class="no-addresses">
                        <h3>No addresses yet</h3>
                    </div>
                <?php else: ?>
                    <div class="addresses-container">
                        <?php foreach ($addresses as $address): ?>
                            <article class="address-card <?= $address['is_default'] ? 'default-address' : '' ?>">
                                <?php if ($address['is_default']): ?>
                                    <div class="default-badge">Default</div>
                                <?php endif; ?>
                                
                                <div class="address-body">
                                    <div class="address-header-info">
                                        <h3><?= htmlspecialchars($address['full_name']) ?></h3>
                                        <span class="phone-number">(+63) <?= htmlspecialchars($address['phone_number']) ?></span>
                                    </div>
                                    
                                    <div class="address-details">
                                        <p class="street-address">
                                            <i class='bx bx-map'></i>
                                            <?= htmlspecialchars($address['street_address']) ?>
                                        </p>
                                        <p class="location-details">
                                            <i class='bx bx-location-plus'></i>
                                            <?= htmlspecialchars($address['region']) ?> - 
                                            <?= htmlspecialchars($address['province']) ?> - 
                                            <?= htmlspecialchars($address['barangay']) ?> 
                                            <?= htmlspecialchars($address['postal_code']) ?>
                                        </p>
                                    </div>

                                    <footer class="address-actions">
                                        <?php if ($address['is_default']): ?>
                                            <button class="default-btn active" disabled>
                                                <i class='bx bx-check'></i> Default
                                            </button>
                                        <?php else: ?>
                                            <form method="post" action="set_default_address.php" style="display: inline;">
                                                <input type="hidden" name="address_id" value="<?= $address['address_id'] ?>">
                                                <button type="submit" class="default-btn">
                                                    <i class='bx bx-check-circle'></i> Set as Default
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                            <a href="../edit-add.php?address_id=<?= $address['address_id'] ?>" class="edit-btn">
                                            <i class='bx bx-edit'></i> Edit
                                        </a>
                                        
                                        <?php if (!$address['is_default']): ?>
                                            <button class="delete-btn" onclick="confirmDeleteAddress(<?= $address['address_id'] ?>)">
                                                <i class='bx bx-trash'></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </footer>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
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

    <script>
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

        // Delete address confirmation
        function confirmDeleteAddress(addressId) {
            Swal.fire({
                title: 'Delete Address?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_address.php';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'address_id';
                    input.value = addressId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Success/Error message handling from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');
        const type = urlParams.get('type');

        if (message && type) {
            const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';
            Swal.fire({
                title: type.charAt(0).toUpperCase() + type.slice(1),
                text: decodeURIComponent(message),
                icon: icon,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            
            // Clean up URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

function confirmDeleteAddress(addressId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This address will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'address_id=' + encodeURIComponent(addressId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload page to reflect deletion
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'Something went wrong.', 'error');
            });
        }
    });
}

    </script>
</body>
</html>