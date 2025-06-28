<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add a Product</title>
  <link rel="stylesheet" href="newproductl12.css">
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
                        <a href="sellershop.php" class="menu-item">
                            <i class="bx bx-store"></i> 
                            <span>Shop</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="sellerproductss.php" class="menu-item active">
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
  <h1 class="profile-title">New Product</h1>
  <form id="form2" action="combinedproductupload.php" method="POST" enctype="multipart/form-data">
    <div class="profile-box" style="border: 1px solid #E6E6E6;">
      <h2 class="section-title">Basic Information</h2>
      <div class="profile-data">
        <div class="form-row">
          <label for="product_name">Product Name</label>
          <input type="text" id="product_name" name="product_name" required><br>
        </div>
       <div class="form-row">
   <label for="product_description">Product Description</label>
        <textarea id="product_description" name="product_description" rows="3" oninput="autoResize(this)" required></textarea>
      </div>
        <br>
        <label for="product_image" class="form-row">Product Image</label>
<div class="img-column">
  <div class="chooseimg-container">
    <label for="product_image" class="chooseimg" style="cursor: pointer; background-color: #ffffff; color: #004AAD; border: 1px solid #E6E6E6; padding: 10px 16px; border-radius: 10px; width:120px; margin-right: 590px; margin-top: -42px;">
      Choose Image
    </label>
    <input type="file" name="product_image[]" id="product_image" accept="image/*" onchange="productVariationImages(this)" style="display: none;" multiple>
    <br><br>
  </div>
</div>
        <div class="variation-image-preview-container">
          <div class="image-slider" id="productPreviewSlider"></div>
        </div>
        <br>
        <div class="form-row">
          <label for="category">Category</label>
          <input type="text" id="category" name="category" required><br>
        </div>
        <div class="form-row">
          <label for="price">Price</label>
          <input type="number" id="price" name="price" required><br>
        </div>
        <div class="form-row">
          <label for="stocks">Stocks</label>
          <input type="number" id="stocks" name="stocks" required><br>
        </div>
      </div>
    </div>

    <div class="shipping-address">
      <h2 class="section-title">Variations (Optional)</h2>
      <div id="variation-container">
        <script>
          window.onload = function () {
            addVariationRow(); // Automatically add the first variation row
          };
        </script>
      </div>
      <button type="button" class="chooseimg add-image-buttons" onclick="addVariationRow()">Add Another Variation</button>
      <br><br>
    </div>

    <div class="button-row">
      <button type="button" class="edit-btn cancel-btn" onclick="window.location.href='sellerproductss.php'" style="border: 1px solid #004AAD; color: #000000; background-color: white">Cancel</button>
      <button type="submit" id="save-button" class="edit-btn" style="color: #ffffff; background-color: blue">Save and Publish</button>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('save-button').addEventListener('click', function (e) {
  e.preventDefault();

  const form = document.getElementById('form2');
  const imageInput = document.getElementById('product_image');


  // Check if at least one image is selected
  if (!imageInput || imageInput.files.length === 0) {
    Swal.fire({
      title: 'Product Images Required',
      text: 'Please upload at least one product image before submitting.',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    return;
  }

  // Form validity check AFTER image check
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  Swal.fire({
    title: 'Info has been Uploaded',
    icon: 'success',
    confirmButtonText: 'OK'
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit();
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

window.onclick = function(event) {
  if (!event.target.closest('.dropdown')) {
    document.getElementById("dropdownMenu").classList.remove("show");
  }
};
function previewFile(input, index) {
    const preview = document.getElementById('previewImage_' + index);
    const file = input.files[0];
    const reader = new FileReader();

    reader.onloadend = function () {
        if (preview) {
            preview.src = reader.result;
        }
    }

    if (file) {
        reader.readAsDataURL(file);
    } else if (preview) {
        preview.src = "uploads/productimages/default.jpg";
    }
}

let variationIndex = 1; // Start from 1 because static block was 0, or set to 0 if you start fresh

function addVariationRow() {
  console.log("Adding variation row...");
  const container = document.getElementById('variation-container');
  const newBlock = document.createElement('div');
  newBlock.classList.add('variation-block'); // Optional CSS class if you have it
  newBlock.style.marginBottom = '20px';
  newBlock.innerHTML = `
    <div class="address-grid">
      <label>Variation Name (optional)</label>
      <input type="text" name="variation_name[]"><br>

      <label>Sub Categories</label>
      <input type="text" name="sub_category[]" required><br>

      <label>Additional Description (optional)</label>
      <textarea id="additional_information" name="additional_information[]" rows="3" oninput="autoResize(this)" required></textarea><br>

      <label>Variation Price (optional)</label>
      <input type="number" name="variation_price[]" step="0.01"><br>

      <label>Variation Stock (optional)</label>
      <input type="number" name="variation_stock[]" min="0"><br>

      <label>Variation Color (optional)</label>
      <input type="text" name="variation_color[]"><br>

      <label>Variation Sizes (optional)</label>
      <input type="text" name="variation_size[]"><br>
    </div>
<br>
    <div class="variation-image-preview-container">
      <div class="variation-image-label-row">
        <label style="margin-left: 35px;">Variation Image (optional):</label>
        <label for="variation_image_${variationIndex}" class="chooseimg add-image-button">
          + Add Variation Images
        </label>
        <input type="file" id="variation_image_${variationIndex}" name="variation_image[]" accept="image/*" style="display: none;" onchange="previewFile(this, ${variationIndex})">
        <img id="previewImage_${variationIndex}" class="imgprev" src="uploads/productimages/default.jpg" alt="Preview"><br>
      </div>
    </div>
  `;

  container.appendChild(newBlock);
  variationIndex++;
}

function previewVariationFile(input, index) {
  const previewContainer = document.getElementById(`PreviewSlider_${index}`);
  previewContainer.innerHTML = ''; // Clear previous previews

  if (input.files) {
    [...input.files].forEach((file) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const imgWrapper = document.createElement('div');
        imgWrapper.classList.add('slide');

        const img = document.createElement('img');
        img.src = e.target.result;
        img.alt = 'Variation Image';
        img.classList.add('slide-image');
        img.style.width = '100px';
        img.style.height = '100px';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '8px';
        img.style.margin = '5px';

        imgWrapper.appendChild(img);
        previewContainer.appendChild(imgWrapper);
      };
      reader.readAsDataURL(file);
    });
  }
}

function productVariationImages(input) {
  const previewContainer = document.getElementById('productPreviewSlider');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (input.files) {
    [...input.files].forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const imgWrapper = document.createElement('div');
        imgWrapper.classList.add('slide');

        const img = document.createElement('img');
        img.src = e.target.result;
        img.alt = 'Variation Image';
        img.classList.add('slide-image');

        imgWrapper.appendChild(img);
        previewContainer.appendChild(imgWrapper);
      };
      reader.readAsDataURL(file);
    })
  }
}
function previewvariationFile(input) {
  const previewContainer = document.getElementById('PreviewSlider');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (input.files) {
    [...input.files].forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const imgWrapper = document.createElement('div');
        imgWrapper.classList.add('slide');

        const img = document.createElement('img');
        img.src = e.target.result;
        img.alt = 'Variation Image';
        img.classList.add('slide-image');

        imgWrapper.appendChild(img);
        previewContainer.appendChild(imgWrapper);
      };
      reader.readAsDataURL(file);
    });
  }
}
 function autoResize(textarea) {
    textarea.style.height = 'auto'; // Reset height
    textarea.style.height = textarea.scrollHeight + 'px'; // Set new height
  }

</script>

</body>

</html>