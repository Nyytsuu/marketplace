<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products</title>
    <link rel="stylesheet" href="sellerproducts10.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            gap: 5px;
        }
        
        .pagination a {
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #007bff;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background-color: #e9ecef;
        }
        
        .pagination .active-page {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .pagination .nav-button {
            font-weight: bold;
        }
        
        .pagination-info {
            text-align: center;
            margin: 15px 0;
            color: #666;
            font-size: 14px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .products-table th,
        .products-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .products-table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        
        .products-table img {
            border-radius: 4px;
            object-fit: cover;
        }
        
        .edit, .delete {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .edit {
            background-color: #28a745;
            color: white;
        }
        
        .edit:hover {
            background-color: #218838;
        }
        
        .delete {
            background-color: #dc3545;
            color: white;
        }
        
        .delete:hover {
            background-color: #c82333;
        }
        
        .product-code {
            font-weight: 600;
            color: #007bff;
        }
        
        .no-products {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-products i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ccc;
        }

        /* Custom SweetAlert2 styling */
        .swal2-popup {
            border-radius: 15px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .swal2-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .swal2-content {
            color: #6b7280;
            font-size: 1rem;
        }
        
        .swal2-confirm {
            background-color: #dc3545 !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            padding: 12px 24px !important;
            font-size: 0.875rem !important;
        }
        
        .swal2-cancel {
            background-color: #6b7280 !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            padding: 12px 24px !important;
            font-size: 0.875rem !important;
        }
        
        .swal2-actions {
            gap: 10px !important;
        }
    </style>
</head>
<body>
    <div class="container"> 
        <div class="grid-container">   
            <div class="header-container">
                <!-- Search bar in the middle -->
                <div class="search-bar">
                    <form action="#" method="get">
                        <input type="text" name="search" placeholder="Search for products">
                        <i class="bx bx-search"></i>    
                    </form>
                </div>
                
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
                        <?php 
                        try {
                            include 'get_profile_image.php';
                        } catch (Exception $e) {
                            echo '<img src="pics/default-avatar.png" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%;">';
                        }
                        ?>
                    </div>
                    <div class="user-info">
                        <?php 
                        try {
                            include 'show-user-info.php';
                        } catch (Exception $e) {
                            echo '<span>User</span>';
                        }
                        ?>
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
            
            <section class="products-section">
                <h2>My Products</h2>
                <p class="sub-text">View and manage your listed items here. You can edit product details, update stocks, or remove products anytime.</p>

                <div class="tabs-bar">
                    <div class="tabs">
                        <span class="active">All Products</span>
                    </div>
                    <button type="button" class="add-product" onclick="window.location.href='newproduct.php'">+ Add a New Product</button>
                </div>
                
                <a name="products-table"></a>
                <table class="products-table">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th>Product ID</th>
                            <th>Product Image</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Stocks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        try {
                            // Check if seller is logged in
                            $seller_id = $_SESSION['user_id'] ?? null;
                            
                            if (!$seller_id) {
                                echo '<tr><td colspan="6" class="no-products">';
                                echo '<div><i class="bx bx-user-x"></i><br>';
                                echo 'Please log in to view your products.</div>';
                                echo '</td></tr>';
                            } else {
                                // Pagination setup
                                $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                                $limit = 10; // Number of products per page
                                $offset = ($page - 1) * $limit;
                                
                                // Database connection
                                $conn = new mysqli("localhost", "root", "", "marketplace");
                                if ($conn->connect_error) {
                                    throw new Exception("Connection failed: " . $conn->connect_error);
                                }
                                
                                // Count total products for pagination
                                $countSql = "SELECT COUNT(*) AS total FROM products WHERE seller_id = ?";
                                $countStmt = $conn->prepare($countSql);
                                
                                if (!$countStmt) {
                                    throw new Exception("Count prepare failed: " . $conn->error);
                                }
                                
                                $countStmt->bind_param("i", $seller_id);
                                $countStmt->execute();
                                $countResult = $countStmt->get_result();
                                $totalProducts = $countResult->fetch_assoc()['total'];
                                $totalPages = ceil($totalProducts / $limit);
                                $countStmt->close();
                                
                                // Get products with pagination using LIMIT with variables (not prepared statement)
                                $sql = "SELECT * FROM products WHERE seller_id = ? ORDER BY product_id DESC LIMIT $limit OFFSET $offset";
                                $stmt = $conn->prepare($sql);
                                
                                if (!$stmt) {
                                    throw new Exception("Products prepare failed: " . $conn->error);
                                }
                                
                                $stmt->bind_param("i", $seller_id);
                                
                                if (!$stmt->execute()) {
                                    throw new Exception("Execute failed: " . $stmt->error);
                                }
                                
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows === 0) {
                                    echo '<tr><td colspan="6" class="no-products">';
                                    echo '<div><i class="bx bx-package"></i><br>';
                                    echo 'No products found. <a href="newproduct.php">Add your first product</a></div>';
                                    echo '</td></tr>';
                                } else {
                                    while ($row = $result->fetch_assoc()) {
                                        // Initialize with default image
                                        $imagePath = 'pics/default.jpg';
                                        
                                        // Try to get main image from product_images table first
                                        $imageQuery = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
                                        if ($imageQuery) {
                                            $imageQuery->bind_param("i", $row['product_id']);
                                            $imageQuery->execute();
                                            $imageResult = $imageQuery->get_result();
                                            $imageRow = $imageResult->fetch_assoc();
                                            
                                            if ($imageRow && !empty($imageRow['image_path'])) {
                                                // Try different possible paths
                                                $possiblePaths = [
                                                    $imageRow['image_path'], // Raw path from DB
                                                    'uploads/' . $imageRow['image_path'], // With uploads prefix
                                                    'uploads/' . basename($imageRow['image_path']), // Just filename in uploads
                                                    'pics/' . basename($imageRow['image_path']) // In pics folder
                                                ];
                                                
                                                foreach ($possiblePaths as $testPath) {
                                                    if (file_exists($testPath)) {
                                                        $imagePath = $testPath;
                                                        break;
                                                    }
                                                }
                                            }
                                            $imageQuery->close();
                                        }
                                        
                                        // If still no image found, try main_image column from products table
                                        if ($imagePath === 'pics/default.jpg' && !empty($row['main_image'])) {
                                            $possiblePaths = [
                                                $row['main_image'], // Raw path from DB
                                                'uploads/' . $row['main_image'], // With uploads prefix
                                                'uploads/' . basename($row['main_image']), // Just filename in uploads
                                                'pics/' . basename($row['main_image']) // In pics folder
                                            ];

                                            foreach ($possiblePaths as $testPath) {
                                                if (file_exists($testPath)) {
                                                    $imagePath = $testPath;
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        echo "<tr>";
                                        echo '<td class="product-code">P' . str_pad($row['product_id'], 3, '0', STR_PAD_LEFT) . '</td>';
                                        echo '<td><img src="' . htmlspecialchars($imagePath) . '" width="80" height="60" style="object-fit:cover; border-radius: 4px;" onerror="this.src=\'pics/default.jpg\'"></td>';
                                        echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                                        echo '<td>₱' . number_format($row['price'], 2) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['stocks']) . '</td>';
                                        echo '<td>';
                                        echo '<a href="edit_product.php?id=' . $row['product_id'] . '" class="edit">';
                                        echo '<i class="bx bx-pencil"></i> Edit';
                                        echo '</a>';
                                        // Updated delete button to use SweetAlert
                                        echo '<button type="button" class="delete" onclick="confirmDelete(' . $row['product_id'] . ', \'' . htmlspecialchars($row['product_name'], ENT_QUOTES) . '\')">';
                                        echo '<i class="bx bx-trash-alt"></i> Delete';
                                        echo '</button>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                                
                                $stmt->close();
                                $conn->close();
                                
                                // Display pagination info and controls outside the table
                                if ($totalProducts > 0) {
                                    echo '</tbody></table>';
                                    
                                    // Pagination info
                                    $startItem = ($page - 1) * $limit + 1;
                                    $endItem = min($page * $limit, $totalProducts);
                                    echo '<p class="pagination-info">Showing ' . $startItem . '-' . $endItem . ' of ' . $totalProducts . ' products</p>';
                                    
                                    // Pagination controls
                                    if ($totalPages > 1) {
                                        echo '<div class="pagination">';
                                        
                                        // Previous button
                                        if ($page > 1) {
                                            echo '<a href="?page=' . ($page - 1) . '#products-table" class="nav-button">&laquo; Previous</a>';
                                        }
                                        
                                        // Page numbers
                                        $visiblePages = 5;
                                        $startPage = max(1, $page - floor($visiblePages / 2));
                                        $endPage = min($totalPages, $startPage + $visiblePages - 1);
                                        
                                        // Adjust start page if we're near the end
                                        if ($endPage - $startPage + 1 < $visiblePages) {
                                            $startPage = max(1, $endPage - $visiblePages + 1);
                                        }
                                        
                                        // First page link
                                        if ($startPage > 1) {
                                            echo '<a href="?page=1#products-table">1</a>';
                                            if ($startPage > 2) {
                                                echo '<span>...</span>';
                                            }
                                        }
                                        
                                        // Page number links
                                        for ($i = $startPage; $i <= $endPage; $i++) {
                                            $class = ($i == $page) ? 'active-page' : '';
                                            echo '<a href="?page=' . $i . '#products-table" class="' . $class . '">' . $i . '</a>';
                                        }
                                        
                                        // Last page link
                                        if ($endPage < $totalPages) {
                                            if ($endPage < $totalPages - 1) {
                                                echo '<span>...</span>';
                                            }
                                            echo '<a href="?page=' . $totalPages . '#products-table">' . $totalPages . '</a>';
                                        }
                                        
                                        // Next button
                                        if ($page < $totalPages) {
                                            echo '<a href="?page=' . ($page + 1) . '#products-table" class="nav-button">Next &raquo;</a>';
                                        }
                                        
                                        echo '</div>';
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            echo '<tr><td colspan="6" class="error-message">';
                            echo 'Error loading products: ' . htmlspecialchars($e->getMessage());
                            echo '</td></tr>';
                            error_log("Seller products error: " . $e->getMessage());
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <script>
        let hideTimeout;

        function toggleDropdown() {
            const menu = document.getElementById("dropdownMenu");
            if (menu) {
                menu.classList.toggle("show");
            }
        }

        function showDropdown() {
            clearTimeout(hideTimeout);
            const menu = document.getElementById("dropdownMenu");
            if (menu) {
                menu.classList.add("show");
            }
        }

        function hideDropdownDelayed() {
            hideTimeout = setTimeout(() => {
                const menu = document.getElementById("dropdownMenu");
                if (menu) {
                    menu.classList.remove("show");
                }
            }, 500);
        }

        function clearHideTimeout() {
            clearTimeout(hideTimeout);
        }

        window.onclick = function(event) {
            if (!event.target.closest('.dropdown')) {
                const menu = document.getElementById("dropdownMenu");
                if (menu) {
                    menu.classList.remove("show");
                }
            }
        };

        // Smooth scroll to products table when pagination links are clicked
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            if (hash === '#products-table') {
                const element = document.querySelector(hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });

        // Enhanced SweetAlert2 delete confirmation function
        function confirmDelete(productId, productName) {
            Swal.fire({
                title: 'Delete Product?',
                html: `Are you sure you want to delete <strong>"${productName}"</strong>?<br><br>This action cannot be undone and will permanently remove the product from your inventory.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bx bx-trash-alt"></i> Yes, Delete It!',
                cancelButtonText: '<i class="bx bx-x"></i> Cancel',
                reverseButtons: true,
                focusCancel: true,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp animate__faster'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting Product...',
                        html: 'Please wait while we remove your product from the inventory.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        showCancelButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Redirect to delete script after a short delay for better UX
                    setTimeout(() => {
                        window.location.href = 'deleteproduct.php?product_id=' + productId;
                    }, 1000);
                }
            });
        }

        // Success notification for when page loads after successful deletion
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's a success parameter in the URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('deleted') === 'success') {
                Swal.fire({
                    title: 'Product Deleted!',
                    text: 'The product has been successfully removed from your inventory.',
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'OK',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
                    }
                }).then(() => {
                    // Clean up the URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            }
        });
    </script>
</body>
</html>