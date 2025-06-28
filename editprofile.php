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
  <link rel="stylesheet" href="editprofilelnew1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
 
 
  <form action="show-user-info.php" method="post">
  <form action="get_profile_image.php" method="post">
  <form action="remove.php" method="post">    
  <form action="upload.php" method="post" enctype="multipart/form-data">
  
</head>
<body>
  <div class="container"> 
  
  <div class="grid-container">   
    <div class="header-container">
    <div class="search-bar">
    <form action="#" method="get">
        <input type="text" name="search" placeholder="Search for products">
        <i class="bx bx-search"></i>    
      </form> 
     </div> 
     <div class="icons">
     <button type=button class="profile-btn" onclick="window.location.href='sellerprofilenew.php'">
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
                        <a href="seller-transactions.php" class="menu-item">
                            <i class="bx bx-credit-card"></i> 
                            <span>Transaction</span>
                        </a>
                    </li>

                </ul>
            </nav>
        </aside>

    <div class="profile-section">
      <form id="postForm" action="updateprofile.php" method="post">
      <div class="profile-box" style="border: 1px solid #E6E6E6;/* blue border */">
        <h2 class="section-title">General Settings</h2>
          <div class="profile-data">
            <div class="profile-pic-container">
       
  <label>Avatar</label>
  <?php include 'get_profile_image.php'; ?>
<form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
  <label for="Upload" style="cursor: pointer; background-color: #ffffff; color: #004AAD; border: 1px solid #E6E6E6;  padding: 8px 16px; border-radius: 5px;">
    Upload
  </label>  
  <input type="file" name="image" id="Upload" style="display: none;" required>
</form>
<form id="uploadForm" action="remove.php" method="post"  >
    <label for="Remove" style="cursor: pointer; background-color: #ffffff; color: #004AAD; border: 1px solid #E6E6E6; padding: 8px 16px; border-radius: 5px;">
    Remove
  </label> 
  <button type="submit" name="remove" id="Remove" style="display: none;">Remove</button>
</form>
 
         </div>
            <div class="data-fields">
                <label for="username">Username:</label><br>
              <input type="text" id="fullname" name="fullname" placeholder="Shop name" required><br>
              <label for="email">Email:</label><br>
              <input type="email" id="email" name="email" placeholder="Email" required><br>
              <label for="phone">Phone:</label><br>
              <input type="tel" id="phone" name="phone" placeholder="Contact No." required><br>
              <label for="username">Address:</label><br>
              <input type="text" id="Address" name="Address" placeholder="Address" required><br>
            </div>
          </div> 
          <div class="buttons-row">
          <button type="button" class="edit-bttn cancel-btn" onclick="window.location.href='sellerprofilenew.php'" style="border: 1px solid #004AAD; /* blue border */
color: #000000; background-color: white">Cancel</button>
          <button type="submit" id="save-button" class="edit-bttn">Save</button>
</form>
          </div>
      </div>
      

      
      <div class="shipping-address">
        <h2 class="section-title">Shipping Address</h2>
        <form id="postForm2"action="updateaddress.php" method="post">
          <div class="address-grid">
            <label for="username">Name:</label><br>
              <input type="text" id="Name" name="shipname" placeholder="Enter your name" required><br>
              <label for="username">Street Address:</label><br>
              <input type="text" id="street" name="street" placeholder="Enter your street address" required><br>
              <label for="username">Region:</label><br>
              <input type="text" id="region" name="region" placeholder="Enter your region" required><br>
              <label for="username">City/Municipality:</label><br>
              <input type="text" id="city" name="city" placeholder="Enter your City/Municipality" required><br>
              <label for="username">Province:</label><br>
              <input type="text" id="province" name="province" placeholder="Enter your province" required><br>
              <label for="username">Phone Number:</label><br>
              <input type="tel" id="shipphone" name="shipphone" placeholder="Enter your phone number" required><br>
          </div> 
          <button type="button" class="edit-btn" onclick="window.location.href='sellerprofilenew.php'" style="border: 1px solid #004AAD; /* blue border */
color: #000000; background-color: white">Cancel</button>
          <button type="submit" id="save-button2" class="edit-btn">Save</button>
         
        </form>
      </div>

    </div>
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
        document.getElementById('postForm').submit();
      }
    });
  });
    document.getElementById('save-button2').addEventListener('click', function (e) {
    e.preventDefault(); // Stop the form submission

    Swal.fire({
      title: 'Info has been Uploaded',
      icon: 'success',
      confirmButtonText: 'OK'
    }).then((result) => {
      if (result.isConfirmed) {
        // ✅ Submit the form after OK is clicked
        document.getElementById('postForm2').submit();
      }
    });
  });

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