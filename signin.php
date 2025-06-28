<?php
session_start();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
<title>Seller Sign In</title>
   <link rel="stylesheet" href="signinseller.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<header class="top-bar">
  <div class="container">
    <span style="font-size: 13px;">Follow us:</span>
    <div class="social-icon">
      <i class='bx bxl-facebook'></i>
      <i class='bx bxl-instagram'></i>
      <i class='bx bxl-twitter'></i>
    </div>
    <div class="divider">|</div>
    <a href="User_Driver_SignIn/driver.php">Be a Rider</a>
    <div class="divider">|</div>
    <a href="signup.php">Be a Seller</a>
    <div class="divider">|</div>
    <a href="homepage.php">Start Shopping</a>
  </div>
</header>
<header class="main-header">
  <div class="container header-container">

        <nav class="nav-left">
            <a href="homepage.php">Home</a>
            <a href="Header_About_Us/aboutus.php">About</a>
            <a href="#" class="logo">
                <img src="5. pictures/sellerblue.png" alt="CartIT Logo" />
            </a>
            <a href="../Header_Contact_Us/contactus.php">Contact</a>
            <a href="https://www.facebook.com/people/Bsit-2A/61556396265842/">Follow Us</a>
        </nav>
  </div>
</header>


<main class ="signup-section">
    <div class="signup-bg-overlay"></div>   
    <div class="signup-container">
        <div class="marketplace-info">
          <h2>CartIT Marketplace</h2>
          <h3>Grow your business and Sell more</h3>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.  
            Curabitur nec sapien ut orci malesuada pretium.</p>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.  
            Curabitur nec sapien ut orci malesuada pretium.</p>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.  
            Curabitur nec sapien ut orci malesuada pretium.</p>
        </div>
        <form id="signinForm" class="signup" action="sellersignin.php" method="POST">
        <h1>Sign In</h1>
        <label>Username</label>
        <div class="input user">
            <input type="text" name="username" placeholder="Username" required>
            
        </div>
        <label>Password</label>
        <div class="input pass">
            <input type="password" name="password" placeholder="Password" required>
        </div>
          <div class="submit button">
            <button type="submit" name="signin">Sign in</button>
          </div>
          <div class="signin-link">
            <p>New to CartIT? <a href="signup.php">Sign Up</a></p>
          </div>
        </form>
</main>


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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('signinForm');

  if (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      const formData = new FormData(form);

      try {
        const response = await fetch('sellersignin.php', {
          method: 'POST',
          body: formData
        });

        const responseText = await response.text();
        const result = JSON.parse(responseText);

        if (result.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Signin successful',
            confirmButtonText: 'Continue'
          }).then(() => {
            window.location.href = 'sellerdashboard.php'; // or wherever your seller goes next
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Sign In Failed',
            text: result.message || 'Incorrect username or password',
          });
        }
      } catch (error) {
        console.error('Fetch error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Something went wrong. Please try again later.',
        });
      }
    });
  }
});
</script>
</body> 
</html>