<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
<title>Add Address</title>
   <link rel="stylesheet" href="edit-add.css">
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
      <i class='bx bx-user'></i>
      <a href="#">@LEBRONNNN</a>
    </div>
</header>
  <header class="main-header">
    <div class="container header-container">
      <a href="#" class="logo">
        <img src="cartit.png" alt="CartIT Logo">
      </a>
      <form action="#" method="get" class="search-bar">
        <input type="text" name="search" placeholder="Search for products" required>
        <i class="bx bx-search"></i> 
      </form>
      <div class="nav-right">
        <i class='bx bx-cart'></i>
        <div class="cart-info">
          <span>Shopping cart</span>
          <span style="color: blue;">$0.00</span>
        </div>
      </div>
    </div>
  </header>
  <header class="second-header">
    <div class="second-container">
      <nav class="nav-links">
  <div class="dropdown-wrapper">
  <a href="#" class="categoryToggle">All Categories </a>
  <i class='bx bx-chevron-down' id="categoryToggle" style="cursor: pointer; font-size: 24px; color: white;"></i>
  <div class="dropdown" id=categoryMenu>
    <a href="#">Women's Fashion</a>
    <a href="#">Men's Fashion</a>
    <a href="#">Beauty & Fragrance</a>
    <a href="#">Sports & Activewear</a>
    <a href="#">Home & Living</a>
    <a href="#">Mobile & Gadgets</a>
    <a href="#">Gaming & Tech Accessories</a>
    <a href="#">Toys & Kid</a>
    <a href="#">Audio</a>
    <a href="#">Health & Personal Care</a>
    <a href="#">Automotive & Motor Accessories</a>
    <a href="#">Pet Care</a>
    <a href="#">Home Appliances</a>
  </div>
</div>
  <a href="#">Home</a>
  <a href="#">About Us</a>
  <a href="#">Contact Us</a>
</nav>
    </div>
  </header>

<body>

  <main class="dashboard">
  <div class="main-container">
    <aside class="sidebar" aria-label="Account Navigation">
      <h2>Navigation</h2>
    <nav>
      <ul class="nav-list">
        <li><a href="buyerdashboard.php"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
        <li><a href="orderhistory.php"><i class='bx bx-history'></i> Order History</a></li>
        <li class="active"><a href="buyerAddress.php"><i class='bx bx-map'></i> Address</a></li>
        <li><a href="buyer.settings.php"><i class='bx bx-cog'></i> Settings</a></li>
        <li id="logout-btn" style="cursor: pointer;"><i class='bx bx-log-out'></i> Log-out</li>
    </nav>
    </aside>
    
<div class="order-history">
  <form class="edit-address" method="POST" action="save_address.php">
    <h2>Add Address</h2>
    <div class="form-group">
      <div class="input-box">
        <label for="full-name">Full Name</label>
        <input type="text" name="full_name" id="full-name" placeholder="Enter your full name" required>
      </div>
      <div class="input-box">
        <label for="barangay">Barangay</label>
        <input type="text" name="barangay" id="barangay" placeholder="Enter your barangay" required>
      </div>
      <div class="input-box">
        <label for="phone">Phone Number</label>
        <input type="tel" name="phone" id="phone" placeholder="Enter your phone number" required>
      </div>
      <div class="input-box">
        <label for="street">Street Name, Building, House No.</label>
        <input type="text" name="street_address" id="street" placeholder="Enter street name, building, house no." required>
      </div>
      <div class="input-box">
        <label for="region">Region</label>
        <select name="region" id="region" required>
          <option value="">Choose a Region</option>
          <option value="region1">Region 1</option>
          <option value="region2">Region 2</option>
          <option value="region3">Region 3</option>
          <option value="region4-a">Region 4-A</option>
          <option value="mimaropa">MIMAROPA</option>
          <option value="region5">Region 5</option>
          <option value="region6">Region 6</option>
          <option value="region7">Region 7</option>
          <option value="region8">Region 8</option>
          <option value="region9">Region 9</option>
          <option value="region10">Region 10</option>
          <option value="region11">Region 11</option>
          <option value="region12">Region 12</option>
          <option value="region13">Region 13</option>
          <option value="ncr">NCR</option>
          <option value="barmm">BARMM</option>
        </select>
      </div>
      <div class="input-box">
        <label for="postal">Postal</label>
        <input type="text" name="postal_code" id="postal" placeholder="Enter your postal code" required>
      </div>
      <div class="input-box">
        <label for="province">Province</label>
        <select name="province" id="province" required>
          <option value="">Choose a Province</option>
        </select>
      </div>
    </div>

    <div class="button-group">
      <a href="buyerAddress.html"><button type="button" class="cancel-btn">Cancel</button></a>
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

document.getElementById('region').onchange = function() {
  const region = this.value;
  const provinceSelect = document.getElementById('province');
  provinceSelect.innerHTML = '<option value="">Select Province</option>';
  if (province[region]) {
    province  [region].forEach(function(province) {
      const opt = document.createElement('option');
      opt.value = province;
      opt.innerHTML = province;
      provinceSelect.appendChild(opt);
    });
  }
};
</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    </html>