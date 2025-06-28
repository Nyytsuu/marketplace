
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Seller profile</title>
  <link rel="stylesheet" href="sellerprofilenew5.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
    <form action-="profileinfo.php" method="post">
  <form action="show-user-info.php" method="post">
  <form action="get_profile_image.php" method="post">
    

  
</head>
<body>
  <div class="container"> 
  
  <div class="grid-container">   
    <div class="header-container">
    <div class="search-bar">
    <form class="search-bar" action="#" method="get">
        <input type="text" name="search" placeholder="Search for products">
        <i class="bx bx-search"></i>    
      </form> 
     </div> 
     <div class="icons">
    <button type="button" class="profile-btn" onclick="goToSellerProfile()">
  <i class="bx bx-user"></i>
</button>
        <span class="icon-separator"> |</span>
        <button type="button" class="edit-dp-btn" onclick="window.location.href='editprofile.php'">
          <i class="bx bx-cog"></i>
        </button> 
      </div> 
      <div class="profile-wrapper">
      <div class="dp-container">
        <?php include 'get_profile_image.php'; ?>
      </div>
      <div class="user-info">
 <?php include 'show-user-info.php'; ?>
</div>
      <div class="dropdown">
  <!-- Button with icon -->
  <button type=button class="dropdown-btn" onclick="toggleDropdown()" onmouseover="showDropdown()" onmouseleave="hideDropdownDelayed()">
    <i class='bx bx-down-arrow'></i>
  </button>
  <div class="dropdown-content" id="dropdownMenu" onmouseover="clearHideTimeout()" onmouseleave="hideDropdownDelayed()">
    <a href="signin.php">Logout</a>
  </div>
</div>
    </div>
  </div>
  <aside class="sidebar">
            <div class="logo">
                <img src="pics/seller.png" alt="cartit-logo" width="205px">
            </div>
            <p class="menu-title">MAIN MENU</p>
            <nav class="menu">
                <ul class="sidebar-list">

                    <li class="sidebar-list-item">
                        <a href="sellerdashboard.php" class="menu-item">
                            <i class="bx bx-home"></i> 
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="sellershop.php" class="menu-item">
                            <i class="bx bx-store"></i> 
                            <span>Shop</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="sellerproductss.php" class="menu-item">
                            <i class="bx bx-cart-add"></i> 
                            <span>Products</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="seller.orders.php" class="menu-item">
                            <i class="bx bx-package"></i> 
                            <span>Orders</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="#" class="menu-item">
                            <i class="bx bx-credit-card"></i> 
                            <span>Transaction</span>
                        </a>
                    </li>

                </ul>
            </nav>
        </aside>

    <div class="profile-section">
      <div class="profile-box" style="border: 1px solid #E6E6E6;/* blue border */">
        <h2 class="section-title">Profile</h2>
        <button type="button" class="edit-btn" onclick="window.location.href='editprofile.php'" style="background-color: #004AAD;
color: #ffffff;"> <p><i class="bx bx-plus" style="font-size: 1.5em; font-weight: bold";></i> Edit Profile</p></button>
          <div class="profile-data">
            <div class="profile-pic-container">
  <?php include 'get_profile_image.php'; ?>
         </div>
            <div class="data-fields">
          <?php include 'profileinfo.php'; ?>
            </div>
          </div> 
      </div>

      <div class="shipping-address">
        <h2 class="section-title">Shipping Address</h2>
          <div class="address-grid">
            <?php include 'shippinginfo.php'; ?>
            
          </div> 
         
        </form>
      </div>

    </div>
  </div>

  <script>
    const input = document.getElementById('Upload');
  input.addEventListener('change', function () {
    if (this.files.length > 0) {
      alert("Uploading image...");
      document.getElementById('uploadForm').submit();
    }
  });
  document.getElementById('Upload').addEventListener('change', function () {
    if (this.files.length > 0) {
      document.getElementById('uploadForm').submit();
    }
  });
 let hideTimeout;

  function toggleDropdown() {
    document.getElementById("dropdownMenu").classList.toggle("show");
  }

  function showDropdown() {
    document.getElementById("dropdownMenu").classList.add("show");
  }

  function hideDropdownDelayed() {
    hideTimeout = setTimeout(() => {
      document.getElementById("dropdownMenu").classList.remove("show");
    }, 300); // Adjust delay as needed
  }

  function clearHideTimeout() {
    clearTimeout(hideTimeout);
  }

function goToSellerProfile() {
    // Normalize the path to avoid duplicate reload
    const current = window.location.pathname.replace(/^\/+/, '');
    if (current !== 'sellerprofilenew.php') {
      window.location.href = 'sellerprofilenew.php';
    }
  }
</script>
</body>
</html>