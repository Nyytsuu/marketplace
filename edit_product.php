<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    header("Location: sellerproductss.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    die("Seller not logged in.");
}

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: sellerproductss.php");
    exit();
}

// Fetch product variations
$varStmt = $conn->prepare("SELECT * FROM product_variations WHERE product_id = ?");
$varStmt->bind_param("i", $product_id);
$varStmt->execute();
$variations = $varStmt->get_result();
$variationsList = [];
while ($variation = $variations->fetch_assoc()) {
    $variationsList[] = $variation;
}

// Fetch product images - with error handling for missing columns
$imgStmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id DESC");
$imgStmt->bind_param("i", $product_id);
$imgStmt->execute();
$images = $imgStmt->get_result();
$imagesList = [];
while ($image = $images->fetch_assoc()) {
    // Add default values for missing columns
    if (!isset($image['is_main'])) {
        $image['is_main'] = 0;
    }
    $imagesList[] = $image;
}

$stmt->close();
$varStmt->close();
$imgStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <link rel="stylesheet" href="edit_product.css">
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
        <div class="breadcrumb">
          <a href="sellerproductss.php">← Back to Products</a>
        </div>
        <h1 class="profile-title">Edit Product</h1>
        <form id="editForm" action="update_product.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
          
          <div class="profile-box">
            <h2 class="section-title">Basic Information</h2>
            <div class="profile-data">
              <div class="form-row">
                <label for="product_name">Product Name</label>
                <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
              </div>
              
              <div class="form-row">
                <label for="product_description">Product Description</label>
                <textarea id="product_description" name="product_description" rows="3" oninput="autoResize(this)" required><?php echo htmlspecialchars($product['description'] ?? $product['product_description'] ?? ''); ?></textarea>
              </div>

              <div class="form-row">
                <label>Current Product Images</label>
                <div class="current-images">
                  <?php foreach ($imagesList as $image): ?>
                    <div class="image-item" data-image-id="<?php echo $image['id']; ?>">
                      <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Product Image">
                      <button type="button" class="remove-image" onclick="removeImage(<?php echo $image['id']; ?>)">
                        <i class="bx bx-x"></i>
                      </button>
                      <?php if (isset($image['is_main']) && $image['is_main']): ?>
                        <span class="main-badge">Main</span>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="form-row">
                <label for="product_image">Add New Images</label>
                <div class="img-column">
                  <div class="chooseimg-container">
                    <label for="product_image" class="chooseimg">
                      Choose Images
                    </label>
                    <input type="file" name="product_image[]" id="product_image" accept="image/*" onchange="productVariationImages(this)" style="display: none;" multiple>
                  </div>
                </div>
                <div class="variation-image-preview-container">
                  <div class="image-slider" id="productPreviewSlider"></div>
                </div>
              </div>

              <div class="form-row">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
              </div>
              
              <div class="form-row">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
              </div>
              
              <div class="form-row">
                <label for="stocks">Stocks</label>
                <input type="number" id="stocks" name="stocks" value="<?php echo $product['stocks']; ?>" required>
              </div>
            </div>
          </div>

          <div class="shipping-address">
            <h2 class="section-title">Product Variations</h2>
            
            <!-- Existing Variations -->
            <div id="existing-variations">
              <?php foreach ($variationsList as $index => $variation): ?>
                <div class="variation-block existing-variation" data-variation-id="<?php echo $variation['variation_id']; ?>">
                  <div class="variation-header">
                    <h3>Existing Variation <?php echo $index + 1; ?></h3>
                    <button type="button" class="remove-variation" onclick="removeVariation(<?php echo $variation['variation_id']; ?>, this)">
                      <i class="bx bx-trash"></i> Remove
                    </button>
                  </div>
                  
                  <input type="hidden" name="existing_variation_id[]" value="<?php echo $variation['variation_id']; ?>">
                  
                  <div class="address-grid">
                    <label>Variation Name</label>
                    <input type="text" name="existing_variation_name[]" value="<?php echo htmlspecialchars($variation['variation_name'] ?? ''); ?>">

                    <label>Sub Categories</label>
                    <input type="text" name="existing_sub_category[]" value="<?php echo htmlspecialchars($variation['sub_category'] ?? ''); ?>" required>

                    <label>Additional Description</label>
                    <textarea name="existing_additional_information[]" rows="3" oninput="autoResize(this)"><?php echo htmlspecialchars($variation['additional_information'] ?? ''); ?></textarea>

                    <label>Variation Price</label>
                    <input type="number" name="existing_variation_price[]" value="<?php echo $variation['variation_price'] ?? ''; ?>" step="0.01">

                    <label>Variation Stock</label>
                    <input type="number" name="existing_variation_stock[]" value="<?php echo $variation['variation_stock'] ?? ''; ?>" min="0">

                    <label>Variation Color</label>
                    <input type="text" name="existing_variation_color[]" value="<?php echo htmlspecialchars($variation['variation_color'] ?? ''); ?>">

                    <label>Variation Sizes</label>
                    <input type="text" name="existing_variation_size[]" value="<?php echo htmlspecialchars($variation['variation_size'] ?? ''); ?>">
                  </div>

                  <?php if (!empty($variation['variation_image'])): ?>
                    <div class="current-variation-image">
                      <label>Current Variation Image:</label>
                      <img src="<?php echo htmlspecialchars($variation['variation_image']); ?>" alt="Variation Image" class="variation-preview">
                    </div>
                  <?php endif; ?>

                  <div class="variation-image-preview-container">
                    <div class="variation-image-label-row">
                      <label>Update Variation Image:</label>
                      <label for="existing_variation_image_<?php echo $variation['variation_id']; ?>" class="chooseimg add-image-button">
                        Update Image
                      </label>
                      <input type="file" id="existing_variation_image_<?php echo $variation['variation_id']; ?>" name="existing_variation_image_<?php echo $variation['variation_id']; ?>" accept="image/*" style="display: none;" onchange="previewFile(this, <?php echo $variation['variation_id']; ?>)">
                      <img id="previewImage_<?php echo $variation['variation_id']; ?>" class="imgprev" style="display: none;" alt="Preview">
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- New Variations Container -->
            <div id="variation-container"></div>
            
            <button type="button" class="chooseimg add-image-buttons" onclick="addVariationRow()">Add New Variation</button>
          </div>

          <div class="button-row">
            <button type="button" class="edit-btn cancel-btn" onclick="window.location.href='sellerproductss.php'">Cancel</button>
            <button type="submit" id="update-button" class="edit-btn save-btn">Update Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Global variables
let hideTimeout;
let variationIndex = 1;
let removedImages = [];
let removedVariations = [];

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    autoResizeAllTextareas();
});

// Initialize event listeners
function initializeEventListeners() {
    // Form submission
    const updateButton = document.getElementById('update-button');
    if (updateButton) {
        updateButton.addEventListener('click', handleFormSubmission);
    }

    // Auto-resize existing textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            autoResize(this);
        });
    });
}

// Handle form submission
function handleFormSubmission(e) {
    e.preventDefault();

    const form = document.getElementById('editForm');
    
    // Form validity check
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Show confirmation dialog
    Swal.fire({
        title: 'Update Product?',
        text: 'Are you sure you want to update this product?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#004AAD',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Update',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Add removed items to form
            addRemovedItemsToForm();
            
            // Show loading state
            showLoadingState();
            
            // Submit form
            form.submit();
        }
    });
}

// Add removed items to form as hidden inputs
function addRemovedItemsToForm() {
    const form = document.getElementById('editForm');
    
    // Add removed images
    removedImages.forEach(imageId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'removed_images[]';
        input.value = imageId;
        form.appendChild(input);
    });

    // Add removed variations
    removedVariations.forEach(variationId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'removed_variations[]';
        input.value = variationId;
        form.appendChild(input);
    });
}

// Show loading state
function showLoadingState() {
    const updateButton = document.getElementById('update-button');
    updateButton.disabled = true;
    updateButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Updating...';
    
    document.body.classList.add('loading');
}

// Dropdown functions
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
    }, 500);
}

function clearHideTimeout() {
    clearTimeout(hideTimeout);
}

// Close dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.closest('.dropdown')) {
        document.getElementById("dropdownMenu").classList.remove("show");
    }
};

// Auto-resize textarea function
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

// Auto-resize all textareas on page load
function autoResizeAllTextareas() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(autoResize);
}

// Remove image function with AJAX
function removeImage(imageId) {
    Swal.fire({
        title: 'Remove Image?',
        text: 'This image will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Removing Image...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send AJAX request to remove image
            const formData = new FormData();
            formData.append('image_id', imageId);

            fetch('remove_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the image element from DOM
                    const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
                    if (imageItem) {
                        imageItem.style.transition = 'all 0.3s ease';
                        imageItem.style.opacity = '0';
                        imageItem.style.transform = 'scale(0.8)';
                        
                        setTimeout(() => {
                            imageItem.remove();
                        }, 300);
                    }
                    
                    Swal.fire({
                        title: 'Removed!',
                        text: 'Image has been deleted successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to remove image.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while removing the image.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

// Remove variation function
function removeVariation(variationId, button) {
    Swal.fire({
        title: 'Remove Variation?',
        text: 'This variation will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const variationBlock = button.closest('.variation-block');
            if (variationBlock) {
                variationBlock.style.opacity = '0.5';
                variationBlock.style.background = '#f8d7da';
                
                // Disable all inputs in this variation
                const inputs = variationBlock.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.disabled = true;
                });
                
                // Update button text
                button.innerHTML = '<i class="bx bx-check"></i> Marked for Removal';
                button.style.background = '#28a745';
                button.disabled = true;
                
                // Add to removed variations array
                removedVariations.push(variationId);
                
                Swal.fire({
                    title: 'Marked for Removal',
                    text: 'Variation will be deleted when you update the product.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    });
}

// Add new variation row
function addVariationRow() {
    const container = document.getElementById('variation-container');
    const newBlock = document.createElement('div');
    newBlock.classList.add('variation-block');
    newBlock.style.marginBottom = '20px';
    
    newBlock.innerHTML = `
        <div class="variation-header">
            <h3>New Variation ${variationIndex}</h3>
            <button type="button" class="remove-variation" onclick="removeNewVariation(this)">
                <i class="bx bx-trash"></i> Remove
            </button>
        </div>
        
        <div class="address-grid">
            <div>
                <label>Variation Name (optional)</label>
                <input type="text" name="variation_name[]">
            </div>

            <div>
                <label>Sub Categories</label>
                <input type="text" name="sub_category[]" required>
            </div>

            <div>
                <label>Additional Description (optional)</label>
                <textarea name="additional_information[]" rows="3" oninput="autoResize(this)"></textarea>
            </div>

            <div>
                <label>Variation Price (optional)</label>
                <input type="number" name="variation_price[]" step="0.01">
            </div>

            <div>
                <label>Variation Stock (optional)</label>
                <input type="number" name="variation_stock[]" min="0">
            </div>

            <div>
                <label>Variation Color (optional)</label>
                <input type="text" name="variation_color[]">
            </div>

            <div>
                <label>Variation Sizes (optional)</label>
                <input type="text" name="variation_size[]">
            </div>
        </div>

        <div class="variation-image-preview-container">
            <div class="variation-image-label-row">
                <label>Variation Image (optional):</label>
                <label for="variation_image_${variationIndex}" class="chooseimg add-image-button">
                    + Add Variation Image
                </label>
                <input type="file" id="variation_image_${variationIndex}" name="variation_image[]" accept="image/*" style="display: none;" onchange="previewNewVariationFile(this, ${variationIndex})">
                <img id="previewImage_new_${variationIndex}" class="imgprev" style="display: none;" alt="Preview">
            </div>
        </div>
    `;

    container.appendChild(newBlock);
    
    // Add animation
    newBlock.style.opacity = '0';
    newBlock.style.transform = 'translateY(20px)';
    setTimeout(() => {
        newBlock.style.transition = 'all 0.3s ease';
        newBlock.style.opacity = '1';
        newBlock.style.transform = 'translateY(0)';
    }, 100);
    
    variationIndex++;
}

// Remove new variation (not saved to database yet)
function removeNewVariation(button) {
    const variationBlock = button.closest('.variation-block');
    
    Swal.fire({
        title: 'Remove New Variation?',
        text: 'This variation will be removed from the form.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            variationBlock.style.transition = 'all 0.3s ease';
            variationBlock.style.opacity = '0';
            variationBlock.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                variationBlock.remove();
            }, 300);
        }
    });
}

// Preview file for existing variations
function previewFile(input, variationId) {
    const preview = document.getElementById('previewImage_' + variationId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }
}

// Preview file for new variations
function previewNewVariationFile(input, index) {
    const preview = document.getElementById('previewImage_new_' + index);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }
}

// Preview product images
function productVariationImages(input) {
    const previewContainer = document.getElementById('productPreviewSlider');
    previewContainer.innerHTML = ''; // Clear previous previews

    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgWrapper = document.createElement('div');
                imgWrapper.classList.add('slide');

                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Product Image';
                img.classList.add('slide-image');

                imgWrapper.appendChild(img);
                previewContainer.appendChild(imgWrapper);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Utility functions for better UX
function showSuccessMessage(message) {
    Swal.fire({
        title: 'Success!',
        text: message,
        icon: 'success',
        confirmButtonColor: '#004AAD',
        timer: 3000,
        showConfirmButton: false
    });
}

function showErrorMessage(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonColor: '#dc3545'
    });
}

// Form validation helpers
function validateForm() {
    const form = document.getElementById('editForm');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            field.style.borderColor = '#e9ecef';
        }
    });
    
    return isValid;
}

// Handle network errors
window.addEventListener('offline', function() {
    showErrorMessage('You are offline. Changes will be saved when connection is restored.');
});

window.addEventListener('online', function() {
    showSuccessMessage('Connection restored. You can now save changes.');
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('update-button').click();
    }
    
    // Escape to cancel
    if (e.key === 'Escape') {
        const dropdownMenu = document.getElementById('dropdownMenu');
        if (dropdownMenu.classList.contains('show')) {
            dropdownMenu.classList.remove('show');
        }
    }
});

// Auto-save draft functionality (optional)
let autoSaveTimer;
function autoSaveDraft() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        const formData = new FormData(document.getElementById('editForm'));
        // Here you could implement auto-save to localStorage or server
        console.log('Auto-saving draft...');
    }, 30000); // Auto-save every 30 seconds
}

// Initialize auto-save on form changes
document.getElementById('editForm').addEventListener('input', autoSaveDraft);
document.getElementById('editForm').addEventListener('change', autoSaveDraft);
  </script>>
</body>
</html>