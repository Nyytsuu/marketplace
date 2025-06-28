<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error_message = "";
$signup_error = "";

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    // Handle driver sign-in
    if (isset($_POST['driver_signin'])) {
        $UsernameOrEmail = trim($_POST['Username']);
        $Password = $_POST['Password'];

        $query = "SELECT driver_id, Username, Password FROM drivers WHERE Username = ? OR EmailAdd = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);

        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ss", $UsernameOrEmail, $UsernameOrEmail);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $driver = mysqli_fetch_assoc($result);

        if ($driver) {
            if ($Password === $driver['Password']) { // Plaintext match (you can improve later using password_hash)
                $_SESSION['driver_id'] = $driver['driver_id'];
                $_SESSION['Username'] = $driver['Username'];
                $_SESSION['success_message'] = "Successfully signed in!";
                header("Location:driver_dashboard.php");
                exit();
            } else {
                $error_message = "Incorrect password.";
            }
        } else {
            $error_message = "Driver not found.";
        }

        mysqli_stmt_close($stmt);
    }

    // Handle driver sign-up
    if (isset($_POST['driver_signup'])) {
        $Username = trim($_POST['Username']);
        $EmailAdd = trim($_POST['EmailAdd']);
        $PhoneNum = trim($_POST['PhoneNum']);
        $Password = $_POST['Password'];

        $checkQuery = "SELECT driver_id FROM drivers WHERE Username = ? OR EmailAdd = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);

        if (!$checkStmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($checkStmt, "ss", $Username, $EmailAdd);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $signup_error = "Username or email already exists.";
            $_SESSION['form_mode'] = 'signup';
        } else {
            $insertQuery = "INSERT INTO drivers (Username, EmailAdd, PhoneNum, Password) VALUES (?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);

            if (!$insertStmt) {
                die("Prepare failed: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($insertStmt, "ssss", $Username, $EmailAdd, $PhoneNum, $Password);
            mysqli_stmt_execute($insertStmt);
            mysqli_stmt_close($insertStmt);

            $_SESSION['Username'] = $Username;
            $_SESSION['success_message'] = "Registration successful!";
            header("Location:../driver_dashboard.php");
            exit();
        }

        mysqli_stmt_close($checkStmt);
    }
}

// Handle toggle via GET
if (isset($_GET['mode']) && $_GET['mode'] === 'signup') {
    $_SESSION['form_mode'] = 'signup';
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CartIT Driver Sign Up</title>
  <link rel="stylesheet" href="dsignup.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
  <style>
    .error-message {
      color: red;
      font-size: 14px;
      margin-top: 5px;
    }
  </style>
<body>
<!-- Top Bar -->
<header class="top-bar">
  <div class="top-bar-container">
    <span>Follow us:</span>
    <div class="social-icon">
      <i class='bx bxl-facebook'></i>
      <i class='bx bxl-instagram'></i>
      <i class='bx bxl-twitter'></i>
    </div>
    <div class="divider">|</div>
<a href="../User_Driver_SignIn/driver.php?mode=signup">Be a Rider</a>
    <div class="divider">|</div>
    <a href="../signup.php">Be a Seller</a>
    <div class="divider">|</div>
    <a href="../homepage.php">Start Shopping</a>
  </div>
</header>

<!-- Main Header -->
<header class="main-header">
    <div class="main-header-container">
        <nav class="nav-left">
            <a href="../homepage.php">Home</a>
            <a href="../Header_About_Us/aboutus.php">About</a>
            <a href="#" class="logo">
                <img src="../5. pictures/sellerblue.png" alt="CartIT Logo" />
            </a>
            <a href="../Header_Contact_Us/contactus.php">Contact</a>
            <a href="https://www.facebook.com/people/Bsit-2A/61556396265842/">Follow Us</a>
        </nav>
    </div>
</header>

<!-- Main Content Wrapper Start -->
<div class="main-content">
  <!-- Main -->
  <div class="container main-flex">
    <!-- Left Side Content -->
    <div class="left">
      <h4>CartIT Marketplace</h4>
      <h1>Be a CartIT Delivery Buddy!</h1>
      <p>Got a bike, motor, or car? Join CartIT as a delivery partner and earn while helping people get what they need—fast and easy!</p>
      <ul>
        <li>✅ Flexible hours</li>
        <li>✅ Real income</li>
      </ul>
      <em>Start your journey today and deliver with purpose!</em>
    </div>

    <!-- Right Side Form Wrapper -->
    <div class="wrapper">
      <div class="form-box">
        <div class="toggle-buttons">
          <button id="toggle-signin" class="active">Sign In</button>
          <button id="toggle-signup">Sign Up</button>
        </div>

        <div class="form-container">
<!-- Sign In Form -->
<form id="signin-form" class="form active" method="POST" action="">
  <label>Name</label>
  <div class="input-box">
    <input type="text" name="Username" placeholder="Enter your Name" required>
  </div>

  <label>Password</label>
  <div class="input-box" style="position: relative;">
    <input type="password" id="signin-password" name="Password" placeholder="Enter your password" required />
    <button type="button" class="toggle-password" data-target="signin-password" 
      style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; color: #555;">
      <i class="fa fa-eye"></i>
    </button>
  </div>

<!-- Error message shown here -->
<?php if (!empty($error_message)): ?>
  <p style="color: red; font-size: 14px; margin-top: 5px;"><?php echo $error_message; ?></p>
<?php endif; ?>


  <button type="submit" name="driver_signin">Sign In</button>
</form>

<!-- Sign Up Form -->
<form id="signup-form" class="form" method="POST" action="">
<label>Username</label>
<div class="input-box">
  <input type="text" name="Username" placeholder="Enter your username" required>
</div>

<!-- ⛔ SHOW ERROR IF EXISTS -->
<?php if (!empty($signup_error)): ?>
  <p class="error-message"><?php echo $signup_error; ?></p>
<?php endif; ?>


  <label>Email Address</label>
  <div class="input-box">
    <input type="email" name="EmailAdd" placeholder="Enter your email address" required>
  </div>

  <label>Phone Number</label>
  <div class="input-box">
    <input type="text" name="PhoneNum" placeholder="Enter your phone number" required>
  </div>

  <label>Password</label>
  <div class="input-box" style="position: relative;">
    <input type="password" id="signup-password" name="Password" placeholder="Enter your password" required />
    <button type="button" class="toggle-password" data-target="signup-password" 
      style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; color: #555;">
      <i class="fa fa-eye"></i>
    </button>
  </div>

  <label class="terms">
    <input type="checkbox" required>
    I agree to CartIT's <a href="#">Terms of Condition</a> & <a href="#">Privacy Policy</a>
  </label>

<button type="submit" name="driver_signup">Sign Up</button>
</form>

        </div>
      </div>
    </div>
  </div>
</div>

</main>

  <!--Footer-->

  <footer class="footer">
    <div class="footer-top">
      <div class="footer-logo">
        <img src="../5. pictures/white logo.png" alt="CartIT Logo">
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
</body>
</html>

<script>

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


<!-- Your animation script here -->
  const formContainer = document.querySelector('.form-container');

  document.getElementById('toggle-signin').addEventListener('click', () => {
    document.getElementById('signin-form').classList.add('active');
    document.getElementById('signup-form').classList.remove('active');
    document.getElementById('toggle-signin').classList.add('active');
    document.getElementById('toggle-signup').classList.remove('active');

    formContainer.classList.add('signin-active');
    formContainer.classList.remove('signup-active');

    document.querySelector('.left h1').textContent = 'Hey, Delivery Champ!';
    document.querySelector('.left p').textContent = 'Glad to have you here. Log in to see what’s waiting for you today—your next delivery, your earnings, and more. Let’s hit the road and deliver with heart!';
    document.querySelector('.left ul').style.display = 'none';
    document.querySelector('.left em').style.display = 'none';
  });

  document.getElementById('toggle-signup').addEventListener('click', () => {
    document.getElementById('signup-form').classList.add('active');
    document.getElementById('signin-form').classList.remove('active');
    document.getElementById('toggle-signup').classList.add('active');
    document.getElementById('toggle-signin').classList.remove('active');

    formContainer.classList.add('signup-active');
    formContainer.classList.remove('signin-active');

    document.querySelector('.left h1').textContent = 'Be a CartIT Delivery Buddy!';
    document.querySelector('.left p').textContent = 'Got a bike, motor, or car? Join CartIT as a delivery partner and earn while helping people get what they need—fast and easy!';
    document.querySelector('.left ul').style.display = 'block';
    document.querySelector('.left em').style.display = 'block';
  });
</script>

<!-- ✅ Correctly inject script AFTER the animation code -->
<?php if (isset($_SESSION['form_mode']) && $_SESSION['form_mode'] === 'signup'): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      document.getElementById('toggle-signup').click();
    });
  </script>
  <?php unset($_SESSION['form_mode']); ?>
<?php endif; ?>
