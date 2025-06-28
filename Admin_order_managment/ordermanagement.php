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

// Function to get proper image path
function getImagePath($imagePath) {
    if (empty($imagePath)) {
        return 'https://images.pexels.com/photos/230544/pexels-photo-230544.jpeg?auto=compress&cs=tinysrgb&w=100&h=100';
    }
    
    // If it's already a full URL, return as is
    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }
    
    // If it's a relative path, convert to absolute path from root
    if (strpos($imagePath, '../') === 0) {
        return $imagePath;
    }
    
    // If it's just a filename or relative path, prepend the correct path
    // Assuming images are in the root directory or a specific folder
    return '../' . ltrim($imagePath, '/');
}

// Handle AJAX request for order details first
if (isset($_GET['action']) && $_GET['action'] === 'get_order_details' && isset($_GET['order_id'])) {
    $orderId = mysqli_real_escape_string($conn, $_GET['order_id']);
    
    $detailQuery = "SELECT 
        o.order_id, 
        o.order_date AS created_at, 
        o.status, 
        o.payment_method, 
        o.total_price AS total_amount,
        ba.street_address,
        ba.barangay,
        ba.province,
        ba.region,
        ba.postal_code,
        ba.full_name as shipping_name,
        ba.phone_number as shipping_phone,
        b.Username as buyer_name, 
        b.EmailAdd as buyer_email, 
        b.phone_number as buyer_phone,
        s.username as seller_name, 
        s.email as seller_email, 
        s.phone as seller_phone,
        p.product_name,
        oi.main_image as product_image,
        oi.quantity,
        oi.price as unit_price
        FROM orders o 
        LEFT JOIN buyers b ON o.user_id = b.AccountID 
        LEFT JOIN order_items oi ON o.order_id = oi.order_id 
        LEFT JOIN products p ON oi.product_id = p.product_id 
        LEFT JOIN sellers s ON p.seller_id = s.id 
        LEFT JOIN buyer_addresses ba ON o.delivery_address_id = ba.address_id
        WHERE o.order_id = ?";
    
    $detailStmt = mysqli_prepare($conn, $detailQuery);
    if ($detailStmt) {
        mysqli_stmt_bind_param($detailStmt, "s", $orderId);
        mysqli_stmt_execute($detailStmt);
        $detailResult = mysqli_stmt_get_result($detailStmt);
        $orderDetails = mysqli_fetch_assoc($detailResult);
        
        // Fix image path before returning
        if ($orderDetails && isset($orderDetails['product_image'])) {
            $orderDetails['product_image'] = getImagePath($orderDetails['product_image']);
        }
        
        mysqli_stmt_close($detailStmt);
        
        header('Content-Type: application/json');
        echo json_encode($orderDetails);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit();
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Build the WHERE clause for filtering
$whereConditions = [];
$params = [];
$types = "";

if (!empty($search)) {
    $whereConditions[] = "(o.order_id LIKE ? OR b.Username LIKE ? OR s.username LIKE ? OR p.product_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    $types .= "ssss";
}

if (!empty($statusFilter)) {
    $whereConditions[] = "o.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Main query to fetch orders - Fixed to match actual database schema
$query = "SELECT 
    o.order_id, 
    o.order_date AS created_at, 
    o.status, 
    o.payment_method, 
    o.total_price AS total_amount, 
    b.Username AS buyer_name, 
    b.EmailAdd AS buyer_email, 
    s.username AS seller_name, 
    s.email AS seller_email,
    p.product_name, 
    oi.main_image AS product_image, 
    oi.quantity, 
    oi.price AS unit_price 
    FROM orders o 
    LEFT JOIN buyers b ON o.user_id = b.AccountID 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.product_id 
    LEFT JOIN sellers s ON p.seller_id = s.id 
    $whereClause
    ORDER BY o.order_date DESC 
    LIMIT ? OFFSET ?";

// Add pagination parameters
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn) . "<br>Query: $query");
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fix image paths for all orders
foreach ($orders as &$order) {
    $order['product_image'] = getImagePath($order['product_image']);
}

mysqli_stmt_close($stmt);

// Count total orders for pagination
$countQuery = "SELECT COUNT(DISTINCT o.order_id) as total 
               FROM orders o 
               LEFT JOIN buyers b ON o.user_id = b.AccountID 
               LEFT JOIN order_items oi ON o.order_id = oi.order_id
               LEFT JOIN products p ON oi.product_id = p.product_id
               LEFT JOIN sellers s ON p.seller_id = s.id
               $whereClause";

$countStmt = mysqli_prepare($conn, $countQuery);
if ($countStmt) {
    if (!empty($params) && count($params) > 2) {
        $countParams = array_slice($params, 0, -2); // Remove limit and offset
        $countTypes = substr($types, 0, -2); // Remove 'ii'
        if (!empty($countParams)) {
            mysqli_stmt_bind_param($countStmt, $countTypes, ...$countParams);
        }
    }
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $totalOrders = mysqli_fetch_assoc($countResult)['total'];
    mysqli_stmt_close($countStmt);
} else {
    $totalOrders = 0;
}

$totalPages = ceil($totalOrders / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../Admin_order_managment/ordermanagement.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <title>Admin Order Management</title>
</head>
<body class="bg-gray-100 font-poppins">
  <div class="grid-container">
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

<li class="sidebar-list-item dashboard-item">
  <a href="../Admin_order_managment/ordermanagement.php">
    <span class="material-icons-outlined">add_shopping_cart</span>
    Order Management
  </a>
</li>

<li class="sidebar-list-item">
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

    <!-- Main Content -->
    <main class="content">
      <section class="content">
        <h2>Order Management</h2>
        <p>Shows all orders placed on the platform along with their current statuses, such as pending, shipped, delivered, or canceled. Includes detailed order information.</p>
          
                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Orders</label>
                            <div class="relative">
                                <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search by order ID, buyer, seller, or product..." 
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="returned" <?php echo $statusFilter === 'returned' ? 'selected' : ''; ?>>Returned</option>
                            </select>
                        </div>
                        
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                <i class='bx bx-filter-alt mr-2'></i>Filter
                            </button>
                            <a href="ordermanagement.php" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                <i class='bx bx-refresh mr-2'></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Orders Table -->
                <div class="table-container hover-lift">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order Details</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Buyer</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Seller</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <i class='bx bx-package text-4xl mb-4 block text-gray-300'></i>
                                            <p class="text-lg font-medium">No orders found</p>
                                            <p class="text-sm">Try adjusting your search criteria</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div>
                                                    <p class="font-semibold text-gray-900">#<?php echo htmlspecialchars($order['order_id']); ?></p>
                                                    <p class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <i class='bx bx-user text-blue-600'></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['buyer_name'] ?? 'N/A'); ?></p>
                                                        <p class="text-sm text-gray-500">Buyer</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                        <i class='bx bx-store text-green-600'></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['seller_name'] ?? 'N/A'); ?></p>
                                                        <p class="text-sm text-gray-500">Seller</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                                                        <?php if (!empty($order['product_image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($order['product_image']); ?>" 
                                                                 alt="Product" 
                                                                 class="w-full h-full object-cover rounded-lg"
                                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'image-fallback w-full h-full rounded-lg\'>IMG</div>';">
                                                        <?php else: ?>
                                                            <div class="image-fallback w-full h-full rounded-lg">IMG</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?></p>
                                                        <p class="text-sm text-gray-500">Qty: <?php echo htmlspecialchars($order['quantity'] ?? 1); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="font-bold text-gray-900">₱<?php echo number_format($order['total_amount'] ?? 0, 2); ?></p>
                                                <?php if (!empty($order['unit_price'])): ?>
                                                    <p class="text-sm text-gray-500">₱<?php echo number_format($order['unit_price'], 2); ?> each</p>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="status-badge status-<?php echo strtolower($order['status'] ?? 'pending'); ?>">
                                                    <?php echo ucfirst($order['status'] ?? 'Pending'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <button onclick="viewOrder('<?php echo $order['order_id']; ?>')" 
                                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                                    <i class='bx bx-show mr-1'></i>
                                                    View Details
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $limit, $totalOrders); ?> of <?php echo $totalOrders; ?> results
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?>" 
                                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?>" 
                                   class="px-3 py-2 text-sm font-medium <?php echo $i === $page ? 'text-blue-600 bg-blue-50 border border-blue-300' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-lg">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?>" 
                                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content fade-in">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">Order Details</h3>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class='bx bx-x text-xl text-gray-500'></i>
                </button>
            </div>
            
            <div id="orderDetailsContent" class="p-6">
                <div class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Image path helper function
    function getImageSrc(imagePath) {
        if (!imagePath || imagePath.trim() === '') {
            return 'https://images.pexels.com/photos/230544/pexels-photo-230544.jpeg?auto=compress&cs=tinysrgb&w=100&h=100';
        }
        
        // If it's already a full URL, return as is
        if (imagePath.startsWith('http')) {
            return imagePath;
        }
        
        // If it's a relative path starting with ../, return as is
        if (imagePath.startsWith('../')) {
            return imagePath;
        }
        
        // Otherwise, prepend the correct path
        return '../' + imagePath.replace(/^\/+/, '');
    }

    // Dropdown functionality
    const toggleBtn = document.getElementById('dropdownToggle');
    const popup = document.getElementById('logoutPopup');

    toggleBtn.addEventListener('click', () => {
        popup.classList.toggle('hidden');
    });

    window.addEventListener('click', (e) => {
        if (!toggleBtn.contains(e.target) && !popup.contains(e.target)) {
            popup.classList.add('hidden');
        }
    });

    document.getElementById('logoutBtn').addEventListener('click', (e) => {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '../Admin_Dashboard/logout.php';
        }
    });

    // Modal functionality
    function viewOrder(orderId) {
        const modal = document.getElementById('orderModal');
        const content = document.getElementById('orderDetailsContent');
        
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Show loading state
        content.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-600">Loading order details...</span>
            </div>
        `;
        
        // Fetch order details
        fetch(`ordermanagement.php?action=get_order_details&order_id=${orderId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data && !data.error) {
                    displayOrderDetails(data);
                } else {
                    content.innerHTML = `
                        <div class="text-center py-12">
                            <i class='bx bx-error text-4xl text-red-500 mb-4'></i>
                            <p class="text-red-500 font-medium">Failed to load order details</p>
                            <p class="text-gray-500 text-sm mt-2">${data.error || 'Unknown error occurred'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="text-center py-12">
                        <i class='bx bx-error text-4xl text-red-500 mb-4'></i>
                        <p class="text-red-500 font-medium">An error occurred while loading order details</p>
                        <p class="text-gray-500 text-sm mt-2">Please try again later</p>
                    </div>
                `;
            });
    }

    function displayOrderDetails(order) {
        const content = document.getElementById('orderDetailsContent');
        
        // Build shipping address from individual fields
        let shippingAddress = '';
        if (order.street_address) {
            const addressParts = [
                order.street_address,
                order.barangay,
                order.province,
                order.region,
                order.postal_code
            ].filter(part => part && part.trim() !== '');
            shippingAddress = addressParts.join(', ');
        }
        
        // Handle image display with proper fallback
        const imageSrc = getImageSrc(order.product_image);
        const productImageHtml = `
            <img src="${imageSrc}" 
                 alt="Product" 
                 class="w-full h-full object-cover rounded-xl"
                 onerror="this.onerror=null; this.src='https://images.pexels.com/photos/230544/pexels-photo-230544.jpeg?auto=compress&cs=tinysrgb&w=100&h=100';">
        `;
        
        content.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Order Info -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class='bx bx-receipt mr-2 text-blue-600'></i>
                            Order Information
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order ID:</span>
                                <span class="font-semibold">#${order.order_id}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-semibold">${new Date(order.created_at).toLocaleDateString()}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="status-badge status-${order.status.toLowerCase()}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-semibold">${order.payment_method || 'N/A'}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer Info -->
                    <div class="bg-blue-50 rounded-xl p-6">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class='bx bx-user mr-2 text-blue-600'></i>
                            Buyer Information
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Name:</span>
                                <span class="font-semibold">${order.buyer_name || 'N/A'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-semibold">${order.buyer_email || 'N/A'}</span>
                            </div>
                            ${order.buyer_phone ? `
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phone:</span>
                                <span class="font-semibold">${order.buyer_phone}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Seller Info -->
                    <div class="bg-green-50 rounded-xl p-6">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class='bx bx-store mr-2 text-green-600'></i>
                            Seller Information
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Seller:</span>
                                <span class="font-semibold">${order.seller_name || 'N/A'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-semibold">${order.seller_email || 'N/A'}</span>
                            </div>
                            ${order.seller_phone ? `
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phone:</span>
                                <span class="font-semibold">${order.seller_phone}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Product Info -->
                    <div class="bg-purple-50 rounded-xl p-6">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class='bx bx-package mr-2 text-purple-600'></i>
                            Product Details
                        </h4>
                        <div class="flex items-start space-x-4">
                            <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden">
                                ${productImageHtml}
                            </div>
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900 mb-2">${order.product_name || 'N/A'}</h5>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Quantity:</span>
                                        <span class="font-semibold">${order.quantity || 1}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Unit Price:</span>
                                        <span class="font-semibold">₱${parseFloat(order.unit_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2">
                                        <span class="text-gray-900 font-semibold">Total Amount:</span>
                                        <span class="font-bold text-lg text-green-600">₱${parseFloat(order.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    ${shippingAddress ? `
                    <div class="bg-orange-50 rounded-xl p-6">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class='bx bx-map mr-2 text-orange-600'></i>
                            Shipping Address
                        </h4>
                        <div class="space-y-2 text-sm">
                            ${order.shipping_name ? `<p class="font-medium text-gray-900">${order.shipping_name}</p>` : ''}
                            ${order.shipping_phone ? `<p class="text-gray-600">${order.shipping_phone}</p>` : ''}
                            <p class="text-gray-800">${shippingAddress}</p>
                        </div>
                    </div>
                    ` : ''}

                    <!-- Timeline -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class='bx bx-time mr-2 text-gray-600'></i>
                            Order Timeline
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <div>
                                    <p class="font-medium text-gray-900">Order Placed</p>
                                    <p class="text-sm text-gray-600">${new Date(order.created_at).toLocaleString()}</p>
                                </div>
                            </div>
                            ${order.status === 'delivered' ? `
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <div>
                                    <p class="font-medium text-gray-900">Delivered</p>
                                    <p class="text-sm text-gray-600">Status: ${order.status}</p>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function closeModal() {
        const modal = document.getElementById('orderModal');
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('orderModal').addEventListener('click', (e) => {
        if (e.target.id === 'orderModal') {
            closeModal();
        }
    });

    // Close modal with escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>


</body>
</html>