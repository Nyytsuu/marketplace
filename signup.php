<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
<title>Sign Up</title>
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
    <a href="../User_Driver_SignIn/driver.php">Be a Rider</a>
    <div class="divider">|</div>
    <a href="../signup.php">Be a Seller</a>
    <div class="divider">|</div>
    <a href="../homepage.php">Start Shopping</a>
  </div>
</header>
<header class="main-header">
  <div class="container header-container">
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
       <form id="signupForm" class="signup" action="sellersignup.php" method="POST">
        <h1>Sign Up</h1>
        <label>Username</label>
        <div class="input user">
            <input type="text" name="username" placeholder="Username" required>
            
        </div>
        <label>Email</label>
        <div class="input email">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <label>Password</label>
        <div class="input pass">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div class="checkbox-container">
            <input type="checkbox" id="agree" required>
            <label for="agree">
              <label></label>
              <p>By signing up, you agree to CartIT
              <a href="#">Terms of Condition</a> and <a href="#">Privacy Policy</a>
            </label></p></label>
          </div>
          <div class="submit button">
            <button type="submit" name="signup">Sign Up</button>
          </div>
          <div class="signin-link">
            <p>Have an account? <a href="signin.php">Sign In</a></p>
          </div>
        </form>
</main>
<section class="why-sell">
    <h2>Why Sell With Us</h2>
    <div class="reasons">
        <div class="reason">
            <img src="pics/5876428b-aea0-4972-a89b-086b82724d08-removebg-preview.png" alt="More Customers">
            <h3>More Customers</h3>
            <p>Reach more people and grow your sales.</p>
        </div>
        <div class="reason">
            <img src="pics/3b353be4-d103-4f39-883d-29f7a37db744-removebg-preview.png" alt="Easy to Use">
            <h3>Easy to Use</h3>
            <p>Simple tools to help you manage your products and orders.</p>
        </div>
        <div class="reason">
            <img src="pics/50651c34-f0e7-4956-9a8d-2fb367d8276f-removebg-preview.png" alt="Safe and Secure">
            <h3>Safe and Secure</h3>
            <p>We protect your shop and your buyers.</p>
        </div>
    </div>
</section>

<section class="steps">
    <h2>Simple and Easy Steps to Start Selling</h2>
    <div class="step-box">
        <div class="step">1. Sign Up</div>
        <div class="step">2. Set up your shop</div>
        <div class="step">3. Add Products</div>
        <div class="step">4. Start Selling</div>
    </div>
</section>
<nav class="breadcrumb" aria-label="breadcrumb">
    <div class="container"> 
    </div>
</nav> 

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
  const form = document.getElementById('signupForm');

  if (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      console.log("Form submit intercepted, sending data...");

      const formData = new FormData(form);

      try {
        const response = await fetch('sellersignup.php', {
          method: 'POST',
          body: formData
        });

        console.log("Response received, parsing JSON...");
        const responseText = await response.text();
        console.log("Raw response:", responseText);

        const result = JSON.parse(responseText);
        console.log("Parsed JSON:", result);

        if (result.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Signup successful',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'signin.php';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: result.message || 'Something went wrong',
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