<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="editshopprofile3.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
  
</head>
<body>
  <div class="container"> 
  
  <div class="grid-container">   
    <div class="header-container">
            <!-- Search bar in the middle -->
        
      <form action="#" method="get" class="search-bar">
        <input type="text" name="search" placeholder="Search for products">
        <i class="bx bx-search"></i>    
      </form>

      <!-- Icons moved to the left -->
      <div class="icons">
        <button type="button" class="profile-btn" onclick="window.location.href='sellerprofilenew.php'">
          <i class="bx bx-user"></i>
        </button>
        <span class="icon-separator">|</span>
        <button type="button" class="edit-dp-btn" onclick="window.location.href='editprofile.php'">
          <i class="bx bx-cog"></i>
        </button>
      </div>

      <!-- Profile section on the right -->
      <div class="profile-wrapper">
        <div class="dp-container">
          <?php include 'get_profile_image.php'; ?>
        </div>
        <div class="user-info">
          <?php include 'show-user-info.php'; ?>
        </div>
        <div class="dropdown">
          <button type="button" class="dropdown-btn" onclick="toggleDropdown()">
            <i class="bx bx-chevron-down"></i>
          </button>
          <div class="dropdown-content" id="dropdownMenu">
            <a href="signin.php">
              <i class="bx bx-log-out"></i>
              <span>Logout</span>
            </a>
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
                        <a href="sellershop.php" class="menu-item active">
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

<div class="main-container">
  <form id="shop-form" action="shop_about.php" method="POST" enctype="multipart/form-data">
    <h1>Edit Shop</h1>
    <div class="sub-container">
      <!-- Profile -->
      <div class="card">
        <h2>PROFILE</h2><br>
        <div class="form-group">
          <label for="storeName">Store Name</label><br>
          <div class="input-icon">
            <input type="text" id="fullname" name="fullname" placeholder="Enter your store name" required><br>
          </div>
        </div>
        <div class="form-group">
          <label for="email">Email</label><br>
          <div class="input-icon">
            <input type="email" id="email" name="email" placeholder="Enter your email" required><br>
          </div>
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label><br>
          <div class="input-icon">
            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required><br>
          </div>
        </div>
      </div>
      <!-- About -->
      <div class="card">
        <h2>ABOUT</h2><br>
        <div class="form-group">
          <div class="textarea-container">
          <textarea name="shop_about" placeholder="Enter your store description..." required></textarea></div>
        </div>
      </div>
    </div>
    <button type="submit" id="save-button" class="edit-btn">Save</button>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.getElementById('save-button').addEventListener('click', function (e) {
    e.preventDefault(); // Stop the form submission

    Swal.fire({
      title: 'Info has been Uploaded',
      icon: 'success',
      confirmButtonText: 'OK'
    }).then((result) => {
      if (result.isConfirmed) {
        // ✅ Submit the form after OK is clicked
        document.getElementById('shop-form').submit();
      }
    });
  });
let hideTimeout;

function toggleDropdown() {
  const menu = document.getElementById("dropdownMenu");
  menu.classList.toggle("show");
}

function showDropdown() {
  clearTimeout(hideTimeout);
  document.getElementById("dropdownMenu").classList.add("show");
}

function hideDropdownDelayed() {
  hideTimeout = setTimeout(() => {
    document.getElementById("dropdownMenu").classList.remove("show");
  }, 500); // Adjust delay as needed
}

function clearHideTimeout() {
  clearTimeout(hideTimeout);
}

window.onclick = function(event) {
  if (!event.target.closest('.dropdown')) {
    document.getElementById("dropdownMenu").classList.remove("show");
  }
};

      </script>
      
</body>
</html>