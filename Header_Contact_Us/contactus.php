<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
<title>Contact Us</title>
   <link rel="stylesheet" href="ContactUs.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
   <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
</head>
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
      <a href="#">Be a Rider</a>
      <div class="divider">|</div>
      <a href="#">Be a Seller</a>
      <div class="divider">|</div>
         <a href="#">Sign In</a>
      <span>/</span>
           <a href="#">Sign Up</a>
    </div>
 </header>
    <header class="main-header">
            <div class="containerss header-container">
                <nav class="nav-links">
  <a href="../Buyer Homepage/homepage.html">Home</a>
  <a href="../Buyer About Us/aboutus.html">About Us</a>
  <a href="../Header Contact Us/ContactUs.html">Contact Us</a>
                </nav>
                <a href="#" class="logo">
                    <img src="cartit.png" alt="CartIT Logo">
                </a>       
                   <div class="nav-right">
                     
                      <i class='bx bx-cart'></i>| 
                     <div class="cart-info">
                              <span>Shopping cart</span>
                              <span style="color: blue;">$0.00</span>
                    </div>
                 </div>
            </div>
      </header>
<body>


  <section class="contact-us">
  <div class="containers">
    <h2 style="margin-top:5px;">Contact Us</h2>
    <p>Have a question or need support? We’re here to help! Whether you're a buyer, seller, or just browsing, feel free to reach out to us with any questions or concerns.</p>
  </div>
</section>

<section class="contact-section">
    <div class="left-corner"></div>
  <div class="right-corner"></div>
  <div class="contact-container"> 
    <div class="contact-info">

      <h1 style="font-size: 50px;">Get In Touch</h1>
      <p style="font-size: 18px; margin-bottom: 40px;">
        For support, business inquiries, or feedback, you can contact our team using the form below or via the details provided.
      </p>
      <div class="contact-item">
        <i class="bx bxs-phone-call"></i> (+63) 09090909090
      </div>
      <div class="contact-item">
        <i class="bx bxs-envelope"></i> cartIT.supportemail@gmail.com
      </div>
      <hr class="user-info-divider">
      <div class="social-iconss">
        <p style="margin-top: 0px;">Social Media</p>
        <i class="bx bxl-facebook"></i>
        <i class="bx bxl-instagram"></i>
        <i class="bx bxl-twitter"></i>
      </div>
    </div>
    <div class="contact-form">
      <div class="form-row">
        <input type="email" placeholder="Email">
        <input type="text" placeholder="Name">
      </div>
      <input type="tel" placeholder="Phone Number">
      <textarea rows="10" placeholder="Message"></textarea>
      <button type="submit">Send Message</button>
    </div>
  </div>
</section>


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
        <a href="../Buyer Dashboard/dashboard.html">My Account</a>
        <a href="../buyer order history/index.html">Order History</a>
        <a href="../Buyer Shopping Cart/buyershoppingcart.html">Shopping Cart</a>
      </div>
      <div class="footer-links">
        <h3>Helps</h3>
        <a href="../Header Contact Us/ContactUs.html">Contact</a>
        <a href="../Footer  Buyer Terms And Condition/">Terms & Condition</a>
        <a href="../Footer  Buyer Privacy Policy/">Privacy Policy</a>
      </div>
      <div class="footer-links">
        <h3>Proxy</h3>
        <a href="../Buyer About Us/aboutus.html">About Us</a>
        <a href="../Buyer Feautre Products/featureproducts.html">Browse All Product</a>
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
</script>
</body>
</html>