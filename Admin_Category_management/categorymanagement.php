<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the admin is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: ../Admin_Dashboard/dashboard.php");
    exit();
}

$Username = $_SESSION['Username'];

// Get admin info
$query = "SELECT * FROM admin_signin WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $Username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    die("Database error: " . mysqli_error($conn));
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_category':
            $category_name = trim($_POST['category_name']);
            $status = $_POST['status'];
            
            $query = "INSERT INTO categories (category_name, category_status) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $category_name, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Category added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding category']);
            }
            mysqli_stmt_close($stmt);
            exit();
            
        case 'edit_category':
            $category_id = $_POST['category_id'];
            $category_name = trim($_POST['category_name']);
            $status = $_POST['status'];
            
            $query = "UPDATE categories SET category_name = ?, category_status = ? WHERE category_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $category_name, $status, $category_id);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating category']);
            }
            mysqli_stmt_close($stmt);
            exit();
            
        case 'delete_category':
            $category_id = $_POST['category_id'];
            
            $query = "DELETE FROM categories WHERE category_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $category_id);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting category']);
            }
            mysqli_stmt_close($stmt);
            exit();
            
        case 'add_subcategory':
            $category_id = $_POST['category_id'];
            $subcategory_name = trim($_POST['subcategory_name']);
            $status = $_POST['status'];
            
            $query = "INSERT INTO subcategories (category_id, subcategory_name, subcategory_status) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iss", $category_id, $subcategory_name, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Subcategory added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding subcategory']);
            }
            mysqli_stmt_close($stmt);
            exit();
            
        case 'edit_subcategory':
            $subcategory_id = $_POST['subcategory_id'];
            $subcategory_name = trim($_POST['subcategory_name']);
            $status = $_POST['status'];
            
            $query = "UPDATE subcategories SET subcategory_name = ?, subcategory_status = ? WHERE subcategory_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $subcategory_name, $status, $subcategory_id);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Subcategory updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating subcategory']);
            }
            mysqli_stmt_close($stmt);
            exit();
            
        case 'delete_subcategory':
            $subcategory_id = $_POST['subcategory_id'];
            
            $query = "DELETE FROM subcategories WHERE subcategory_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $subcategory_id);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Subcategory deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting subcategory']);
            }
            mysqli_stmt_close($stmt);
            exit();
    }
}

// Get categories with subcategories and product counts
$query = "SELECT c.*, 
          COUNT(DISTINCT s.subcategory_id) as subcategory_count,
          COUNT(DISTINCT p.product_id) as product_count
          FROM categories c 
          LEFT JOIN subcategories s ON c.category_id = s.category_id 
          LEFT JOIN products p ON c.category_name = p.category
          GROUP BY c.category_id 
          ORDER BY c.category_name";
$categories_result = mysqli_query($conn, $query);

// Get all subcategories
$subcategories_query = "SELECT s.*, c.category_name 
                        FROM subcategories s 
                        JOIN categories c ON s.category_id = c.category_id 
                        ORDER BY c.category_name, s.subcategory_name";
$subcategories_result = mysqli_query($conn, $subcategories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="../Admin_Category_management/categorymanagement.css" rel="stylesheet">
    <title>Category Management</title>
</head>
<body class="bg-gray-100 font-poppins">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
      <div class="sidebar-title">
        <div class="sidebar-brand">
          <img src="../5. pictures/admin.png" alt="Logo" class="logo">
        </div>
      </div>

      <ul class="sidebar-list">
        <div class="sidebar-line"></div>
<li class="sidebar-list-item">
  <a href="../Admin_Dashboard/dashboard.php">
    <span class="material-icons-outlined">dashboard</span>
    Dashboard
  </a>
</li>

<li class="sidebar-list-item active">
  <a href="../Admin_User_management/user-management.php">
    <span class="material-icons-outlined">inventory_2</span>
    User Management
  </a>
</li>

<li class="sidebar-list-item">
  <a href="../Admin_order_managment/ordermanagement.php">
    <span class="material-icons-outlined">add_shopping_cart</span>
    Order Management
  </a>
</li>

<li class="sidebar-list-item dashboard-item">
      <a href="../Admin_Category_management/categorymanagement.php">
    <span class="material-icons-outlined">shopping_cart</span>
    Category Management
  </a>
</li>
<li class="sidebar-list-item">
  <a href="../Admin_Withdrawal/withdrawal.php">
    <span class="material-icons-outlined">poll</span>
    Withdrawal Management
  </a>
</li>
<li class="sidebar-list-item">
  <a href="../Admin_Delivery_Management/delivery_man.php">
    <span class="material-icons-outlined">local_shipping</span>
    Delivery Management
  </a>
</li>
      </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
    <!-- Header -->
    <header class="header">


      <div class="relative flex items-center space-x-4">
        <i class='bx bxs-user-circle text-3xl'></i>
        <div class="text-sm text-right">
          <p class="font-medium">@<?php echo htmlspecialchars($admin['Username']); ?></p>
          <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($admin['AdminID']); ?></p>
        </div>
        <i class='bx bx-chevron-down cursor-pointer text-xl' id="dropdownToggle"></i>
        <div class="absolute top-12 right-0 bg-white shadow rounded hidden" id="logoutPopup">
          <button class="block px-4 py-2 text-sm hover:bg-gray-100" id="logoutBtn">Logout</button>
        </div>
      </div>
    </header>

        <!-- Content -->
        <main class="content">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Category Management</h1>
                <p class="text-gray-600">Organize products into specific groups to improve sales, customer experience, and inventory management.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <?php
                $total_categories = mysqli_num_rows($categories_result);
                mysqli_data_seek($categories_result, 0);
                
                $active_categories = 0;
                $total_products = 0;
                while ($cat = mysqli_fetch_assoc($categories_result)) {
                    if ($cat['category_status'] == 'active') $active_categories++;
                    $total_products += $cat['product_count'];
                }
                mysqli_data_seek($categories_result, 0);
                
                $subcategories_count = mysqli_num_rows($subcategories_result);
                ?>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Categories</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $total_categories; ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="material-icons-outlined text-blue-600">category</i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Active Categories</p>
                            <p class="text-3xl font-bold text-green-600"><?php echo $active_categories; ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="material-icons-outlined text-green-600">check_circle</i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Subcategories</p>
                            <p class="text-3xl font-bold text-purple-600"><?php echo $subcategories_count; ?></p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="material-icons-outlined text-purple-600">list</i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Products</p>
                            <p class="text-3xl font-bold text-orange-600"><?php echo $total_products; ?></p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="material-icons-outlined text-orange-600">inventory</i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="text-xl font-semibold text-gray-900">Categories & Subcategories</h2>
                    <div class="flex gap-3">
                        <button class="btn-primary" onclick="openModal('categoryModal')">
                            <i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">add</i>
                            Add Category
                        </button>
                        <button class="btn-primary" onclick="openModal('subcategoryModal')">
                            <i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">add</i>
                            Add Subcategory
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subcategories</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <tr class="expandable-row hover:bg-gray-50" onclick="toggleSubcategories(<?php echo $category['category_id']; ?>)">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="material-icons-outlined mr-2 text-gray-400">expand_more</i>
                                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($category['category_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge <?php echo $category['category_status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($category['category_status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $category['subcategory_count']; ?> subcategories
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $category['product_count']; ?> products
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="action-buttons" onclick="event.stopPropagation();">
                                        <button class="btn-edit" onclick="editCategory(<?php echo $category['category_id']; ?>, '<?php echo addslashes($category['category_name']); ?>', '<?php echo $category['category_status']; ?>')">
                                            Edit
                                        </button>
                                        <button class="btn-delete" onclick="deleteCategory(<?php echo $category['category_id']; ?>)">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <?php
                            // Get subcategories for this category
                            $subcat_query = "SELECT * FROM subcategories WHERE category_id = ? ORDER BY subcategory_name";
                            $subcat_stmt = mysqli_prepare($conn, $subcat_query);
                            mysqli_stmt_bind_param($subcat_stmt, "i", $category['category_id']);
                            mysqli_stmt_execute($subcat_stmt);
                            $subcat_result = mysqli_stmt_get_result($subcat_stmt);
                            
                            while ($subcategory = mysqli_fetch_assoc($subcat_result)):
                            ?>
                            <tr class="subcategory-row subcategory-<?php echo $category['category_id']; ?>">
                                <td class="px-6 py-3 whitespace-nowrap" style="padding-left: 60px !important;">
                                    <span class="text-gray-700"><?php echo htmlspecialchars($subcategory['subcategory_name']); ?></span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <span class="status-badge <?php echo $subcategory['subcategory_status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($subcategory['subcategory_status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                    Subcategory
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                    -
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium">
                                    <div class="action-buttons">
                                        <button class="btn-edit" onclick="editSubcategory(<?php echo $subcategory['subcategory_id']; ?>, '<?php echo addslashes($subcategory['subcategory_name']); ?>', '<?php echo $subcategory['subcategory_status']; ?>')">
                                            Edit
                                        </button>
                                        <button class="btn-delete" onclick="deleteSubcategory(<?php echo $subcategory['subcategory_id']; ?>)">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                            endwhile;
                            mysqli_stmt_close($subcat_stmt);
                            ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('categoryModal')">&times;</span>
            <h2 id="categoryModalTitle" class="text-2xl font-bold mb-6">Add Category</h2>
            <form id="categoryForm">
                <input type="hidden" id="categoryId" name="category_id">
                <div class="form-group">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" name="category_name" required>
                </div>
                <div class="form-group">
                    <label for="categoryStatus">Status</label>
                    <select id="categoryStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50" onclick="closeModal('categoryModal')">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subcategory Modal -->
    <div id="subcategoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('subcategoryModal')">&times;</span>
            <h2 id="subcategoryModalTitle" class="text-2xl font-bold mb-6">Add Subcategory</h2>
            <form id="subcategoryForm">
                <input type="hidden" id="subcategoryId" name="subcategory_id">
                <div class="form-group">
                    <label for="parentCategory">Parent Category</label>
                    <select id="parentCategory" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php
                        mysqli_data_seek($categories_result, 0);
                        while ($cat = mysqli_fetch_assoc($categories_result)):
                        ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subcategoryName">Subcategory Name</label>
                    <input type="text" id="subcategoryName" name="subcategory_name" required>
                </div>
                <div class="form-group">
                    <label for="subcategoryStatus">Status</label>
                    <select id="subcategoryStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50" onclick="closeModal('subcategoryModal')">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        Save Subcategory
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'categoryModal') {
                document.getElementById('categoryForm').reset();
                document.getElementById('categoryId').value = '';
                document.getElementById('categoryModalTitle').textContent = 'Add Category';
            } else if (modalId === 'subcategoryModal') {
                document.getElementById('subcategoryForm').reset();
                document.getElementById('subcategoryId').value = '';
                document.getElementById('subcategoryModalTitle').textContent = 'Add Subcategory';
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Toggle subcategories
        function toggleSubcategories(categoryId) {
            const subcategories = document.querySelectorAll('.subcategory-' + categoryId);
            const icon = event.currentTarget.querySelector('.material-icons-outlined');
            
            subcategories.forEach(row => {
                if (row.style.display === 'none' || !row.style.display) {
                    row.style.display = 'table-row';
                    icon.textContent = 'expand_less';
                } else {
                    row.style.display = 'none';
                    icon.textContent = 'expand_more';
                }
            });
        }

        // Category functions
        function editCategory(id, name, status) {
            document.getElementById('categoryId').value = id;
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryStatus').value = status;
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            openModal('categoryModal');
        }

        function deleteCategory(id) {
            if (confirm('Are you sure you want to delete this category? This will also delete all subcategories.')) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete_category&category_id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        // Subcategory functions
        function editSubcategory(id, name, status) {
            document.getElementById('subcategoryId').value = id;
            document.getElementById('subcategoryName').value = name;
            document.getElementById('subcategoryStatus').value = status;
            document.getElementById('subcategoryModalTitle').textContent = 'Edit Subcategory';
            openModal('subcategoryModal');
        }

        function deleteSubcategory(id) {
            if (confirm('Are you sure you want to delete this subcategory?')) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete_subcategory&subcategory_id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        // Form submissions
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const categoryId = document.getElementById('categoryId').value;
            
            formData.append('action', categoryId ? 'edit_category' : 'add_category');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        document.getElementById('subcategoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const subcategoryId = document.getElementById('subcategoryId').value;
            
            formData.append('action', subcategoryId ? 'edit_subcategory' : 'add_subcategory');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr:not(.subcategory-row)');
            
            rows.forEach(row => {
                const categoryName = row.querySelector('td:first-child span').textContent.toLowerCase();
                if (categoryName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Dropdown toggle
        const toggleBtn = document.getElementById('dropdownToggle');
        const popup = document.getElementById('logoutPopup');

        toggleBtn.addEventListener('click', () => {
            popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
        });

        window.addEventListener('click', (e) => {
            if (!toggleBtn.contains(e.target) && !popup.contains(e.target)) {
                popup.style.display = 'none';
            }
        });

        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../Admin_Dashboard/logout.php';
            }
        });
    </script>
</body>
</html>