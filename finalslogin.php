<?php
// Add your database connection here
$conn = mysqli_connect("localhost", "root", "", "marketplace");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Set default session variables    
$username = $_SESSION['Username'] ?? 'Guest';
$cart_count = 0;
$cart_total = 0.00;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
        $cart_total += $item['price'] * $item['quantity'];
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    session_start();
    $_SESSION['Username'] = 'Guest';
    header('Location: finalslogin.php');
    exit;
}

// Now redirect if already logged in (ONLY after Sign In, not Sign Up)
if (isset($_SESSION['Username']) && $_SESSION['Username'] !== 'Guest' && !isset($_SESSION['signup_success'])) {
    header('Location: homepage.php');
    exit;
}

// Handle Sign In
if (isset($_POST['signin'])) {
    $Username = $_POST['Username'] ?? '';
    $Password = $_POST['Password'] ?? '';

    // Validate input
    if (empty($Username) || empty($Password)) {
        $_SESSION['signin_error'] = "Username and Password are required.";
        $_SESSION['form_mode'] = 'signin';
    } else {
        // Check if username exists and get AccountID
        $query = "SELECT AccountID, Password FROM buyers WHERE Username = ?";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $Username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $AccountID, $hashedPasswordFromDB);
            mysqli_stmt_fetch($stmt);

            // Verify password
                if ($Password === $hashedPasswordFromDB) {
                // Set all necessary session variables
                $_SESSION['AccountID'] = $AccountID;
                $_SESSION['Username'] = $Username;
                $_SESSION['username'] = $Username; // Some pages might use lowercase
                unset($_SESSION['signup_success']); // clear sign-up success message if any

                mysqli_stmt_close($stmt);
                header('Location: homepage.php');
                exit;
            } else {
                $_SESSION['signin_error'] = "Invalid Username or Password.";
                $_SESSION['form_mode'] = 'signin';
            }
        } else {
            $_SESSION['signin_error'] = "Invalid Username or Password.";
            $_SESSION['form_mode'] = 'signin';
        }

        mysqli_stmt_close($stmt);
    }
}

// Handle Sign Up
if (isset($_POST['signup'])) {
    $Username = $_POST['Username'] ?? '';
    $EmailAdd = $_POST['EmailAdd'] ?? '';
    $Password = $_POST['Password'] ?? '';

    // Validate input
    if (empty($Username) || empty($EmailAdd) || empty($Password)) {
        $_SESSION['signup_error'] = "All fields are required.";
        $_SESSION['form_mode'] = 'signup';
    } else {
        // Check if username already exists
        $checkQuery = "SELECT Username FROM buyers WHERE Username = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        if (!$checkStmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($checkStmt, "s", $Username);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $_SESSION['signup_error'] = "Username already exists. Please choose a different username.";
            $_SESSION['form_mode'] = 'signup';
            mysqli_stmt_close($checkStmt);
        } else {
            mysqli_stmt_close($checkStmt);

            
            // Check if email already exists
            $checkEmailQuery = "SELECT EmailAdd FROM buyers WHERE EmailAdd = ?";
            $checkEmailStmt = mysqli_prepare($conn, $checkEmailQuery);
            if (!$checkEmailStmt) {
                die("Prepare failed: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($checkEmailStmt, "s", $EmailAdd);
            mysqli_stmt_execute($checkEmailStmt);
            mysqli_stmt_store_result($checkEmailStmt);

            if (mysqli_stmt_num_rows($checkEmailStmt) > 0) {
                $_SESSION['signup_error'] = "Email address already exists. Please use a different email.";
                $_SESSION['form_mode'] = 'signup';
                mysqli_stmt_close($checkEmailStmt);
            } else {
                mysqli_stmt_close($checkEmailStmt);

                // Hash the password
                    $hashedPassword = $Password; // store as plain text (insecure)

                // Insert new user into database
                $insertQuery = "INSERT INTO buyers (Username, EmailAdd, Password) VALUES (?, ?, ?)";
                $insertStmt = mysqli_prepare($conn, $insertQuery);
                if (!$insertStmt) {
                    die("Prepare failed: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($insertStmt, "sss", $Username, $EmailAdd, $hashedPassword);

                if (mysqli_stmt_execute($insertStmt)) {
                    // Get the new AccountID
                    $AccountID = mysqli_insert_id($conn);

                    // Do NOT log the user in after sign up — require sign in first
                    // $_SESSION['AccountID'] = $AccountID;
                    // $_SESSION['Username'] = $Username;
                    // $_SESSION['username'] = $Username;

                    $_SESSION['signup_success'] = "Registration successful! Please sign in.";
                    mysqli_stmt_close($insertStmt);
                    header('Location: finalslogin.php');
                    exit;
                } else {
                    $_SESSION['signup_error'] = "Registration failed. Please try again.";
                    $_SESSION['form_mode'] = 'signup';
                    mysqli_stmt_close($insertStmt);
                }
            }
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>CartIT - Login</title>

    <link rel="stylesheet" href="finalslogin1.css" />
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    </head>
    <body>

    <!-- Top bar -->
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
            <?php
            $profile_link = ($username === 'Guest') ? 'finalslogin.php' : 'buyerdashboard.php';
            ?>
            <a href="<?= $profile_link ?>">
                <i class='bx bx-user'></i>
                <span class="username">@<?= htmlspecialchars($username) ?></span>
            </a>
        </div>
    </header>

    <!-- Main header -->
    <header class="main-header">
        <div class="container header-container">
            <a href="#" class="logo">
                <img src="../5. pictures/logo.png" alt="CartIT Logo" />
            </a>
            <form action="#" method="get" class="search-bar">
                <input type="text" name="search" placeholder="Search for products" required />
                <i class="bx bx-search"></i>
            </form>
            <div class="nav-right">
                <i class='bx bx-cart'></i>
                <div class="cart-info">
                    <span>Shopping cart</span>
                    <span style="color: blue;">$<?= number_format($cart_total, 2) ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Second header -->
    <header class="second-header">
        <div class="second-container">
            <nav class="nav-links">
                <div class="dropdown-wrapper">
                    <a href="../Category_All/categories.php" class="categoryToggle">All Categories</a>
                    <i class='bx bx-chevron-down' id="categoryToggle" style="cursor: pointer; font-size: 24px; color: white;"></i>
                    <div class="dropdown" id="categoryMenu">
                        <a href="../">Women's Fashion</a>
                        <a href="../">Men's Fashion</a>
                        <a href="../">Beauty & Fragrance</a>
                        <!-- Add more categories as needed -->
                    </div>
                </div>
                <a href="../homepage.php">Home</a>
                <a href="../Online Marketing System/Header_About_Us/aboutus.php">About Us</a>
                <a href="../Online Marketing System/Header_Contact_Us/contactus.php">Contact Us</a>
            </nav>
        </div>
    </header>




    <!-- Main Content -->
    <div class="center-wrapper">
        <div class="wrapper">
            <div class="toggle-buttons">
                <button id="toggle-signin" class="active" onclick="showLogin()">Sign In</button>
                <button id="toggle-signup" onclick="showSignup()">Sign Up</button>
            </div>

            <div class="form-container signin-active" id="formWrapper">


                <!-- Login Form -->
                <form id="loginForm" action="" method="post" class="form active">
                    <label>Username</label>
                    <div class="input-box">
                        <input type="text" name="Username" placeholder="Enter your username" required />
                    </div>

                    <label>Password</label>
<div class="input-box">
  <input type="password" name="Password" id="signin-password" placeholder="Enter your password" required />
  <button type="button" class="toggle-password" data-target="signin-password">
    <i class="fa fa-eye"></i>
  </button>
  <?php if (!empty($_SESSION['signin_error'])): ?>
    <p class="error-message"><?= htmlspecialchars($_SESSION['signin_error']) ?></p>
  <?php endif; ?>
</div>


                    <button type="submit" name="signin">Sign In</button>
                </form>

                <!-- Signup Form -->
                <form id="signupForm" action="" method="post" onsubmit="return checkTerms()" class="form">
                    <label>Username</label>
                    <div class="input-box">
                        <input type="text" name="Username" placeholder="Enter your username" required />
                        <?php if (!empty($_SESSION['signup_error'])): ?>
                            <p class="error-message"><?= htmlspecialchars($_SESSION['signup_error']) ?></p>
                        <?php endif; ?>
                    </div>

                    <label>Email Address</label>
                    <div class="input-box">
                        <input type="email" name="EmailAdd" placeholder="Enter your email address" required />
                    </div>

                    <label>Password</label>
                    <div class="input-box" style="position: relative;">
                        <input type="password" name="Password" id="signup-password" placeholder="Enter your password" required />
                        <button type="button" class="toggle-password" data-target="signup-password">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>

                    <label class="terms">
                        <input type="checkbox" id="terms" name="terms" required />
                        I agree to CartIT's <a href="#">Terms of Service</a> & <a href="#">Privacy Policy</a>
                    </label>

                    <button type="submit" name="signup">Sign Up</button>
                </form>
            </div>
        </div>
    </div>


    <!-- Script -->
    <?php $form_mode = $_SESSION['form_mode'] ?? 'signin'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const formMode = '<?= $form_mode ?>';
            if (formMode === 'signin') {
                showLogin();
            } else if (formMode === 'signup') {
                showSignup();
            }
        });

        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (input.type === 'password') {
                    input.type = 'text';
                    button.innerHTML = '<i class="fa fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    button.innerHTML = '<i class="fa fa-eye"></i>';
                }
            });
        });

        function showLogin() {
            document.getElementById('loginForm').classList.add('active');
            document.getElementById('signupForm').classList.remove('active');
            document.getElementById('toggle-signin').classList.add('active');
            document.getElementById('toggle-signup').classList.remove('active');
        }

        function showSignup() {
            document.getElementById('signupForm').classList.add('active');
            document.getElementById('loginForm').classList.remove('active');
            document.getElementById('toggle-signup').classList.add('active');
            document.getElementById('toggle-signin').classList.remove('active');
        }

        function checkTerms() {
            if (!document.getElementById('terms').checked) {
                alert("You must agree to the Terms and Conditions.");
                return false;
            }
            return true;
        }
    </script>
    
    <?php
    // Clear session errors after displaying them
    unset($_SESSION['signin_error'], $_SESSION['signup_error'], $_SESSION['form_mode']);
    ?>


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
