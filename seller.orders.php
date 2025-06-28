<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="seller.orders12.css" rel="stylesheet" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet" />
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>Seller Dashboard</title>
  <style>
    /* Enhanced Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background-color: #ffffff;
      margin: 2% auto;
      padding: 0;
      border: none;
      border-radius: 16px;
      width: 90%;
      max-width: 800px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
      animation: slideInUp 0.3s ease-out;
    }

    @keyframes slideInUp {
      from {
        transform: translateY(30px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .modal-header {
      background: #0046ad;
      color: white;
      padding: 24px 32px;
      border-radius: 16px 16px 0 0;
      position: relative;
    }

    .modal-header h2 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
    }

    .close {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: white;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .close:hover {
      background-color: rgba(255, 255, 255, 0.2);
      transform: translateY(-50%) scale(1.1);
    }

    .modal-body {
      padding: 32px;
    }

    .order-details-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
      margin-bottom: 32px;
    }

    .detail-card {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      border-radius: 12px;
      padding: 20px;
      border-left: 4px solid #667eea;
    }

    .detail-card h3 {
      margin: 0 0 16px 0;
      color: #1e293b;
      font-size: 1.1rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .detail-card p {
      margin: 8px 0;
      color: #475569;
      line-height: 1.5;
    }

    .detail-card .label {
      font-weight: 600;
      color: #334155;
    }

    .items-section {
      margin-top: 32px;
    }

    .items-section h3 {
      color: #1e293b;
      font-size: 1.2rem;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid #e2e8f0;
    }

    .item-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 16px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      border: 1px solid #e2e8f0;
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .item-image {
      width: 80px;
      height: 80px;
      border-radius: 8px;
      object-fit: cover;
      border: 2px solid #e2e8f0;
    }

    .item-details {
      flex: 1;
    }

    .item-details h4 {
      margin: 0 0 8px 0;
      color: #1e293b;
      font-size: 1rem;
      font-weight: 600;
    }

    .item-meta {
      display: flex;
      gap: 16px;
      margin-bottom: 8px;
    }

    .item-meta span {
      background: #f1f5f9;
      padding: 4px 12px;
      border-radius: 6px;
      font-size: 0.875rem;
      color: #475569;
    }

    .item-price {
      font-weight: 600;
      color: #059669;
      font-size: 1.1rem;
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
      text-transform: capitalize;
    }

    .status-badge.pending {
      background: #fef3c7;
      color: #92400e;
    }

    .status-badge.processing {
      background: #dbeafe;
      color: #1e40af;
    }

    .status-badge.shipped {
      background: #e0e7ff;
      color: #5b21b6;
    }

    .status-badge.delivered {
      background: #d1fae5;
      color: #065f46;
    }

    .status-badge.cancelled {
      background: #fee2e2;
      color: #991b1b;
    }

    .details-btn {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-right: 8px;
    }

    .details-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .update-btn {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .update-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .action-buttons {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .modal-content {
        width: 95%;
        margin: 5% auto;
      }
      
      .order-details-grid {
        grid-template-columns: 1fr;
      }
      
      .item-card {
        flex-direction: column;
        text-align: center;
      }
      
      .item-meta {
        justify-content: center;
      }
    }
  </style>
  
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
                        <a href="sellerproductss.php" class="menu-item">
                            <i class="bx bx-cart-add"></i> 
                            <span>Products</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="seller.orders.php" class="menu-item active">
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

    <div class="form-wrapper" id="formWrapper">
      <section class="orders-section">
          <h2>Order Details</h2>
          <p class="sub-text">Check all the important info about your order here—like the order number, items, prices, payment method, shipping details, and status.</p>
          
        <div class="tabs">
    <a href="seller.orders.php" class="tab <?= !isset($_GET['status']) ? 'active' : '' ?>" data-status="allorders">All Orders</a>
    <a href="seller.orders.php?status=pending" class="tab <?= ($_GET['status'] ?? '') === 'pending' ? 'active' : '' ?>" data-status="pending">Pending</a>
    <a href="seller.orders.php?status=processing" class="tab <?= ($_GET['status'] ?? '') === 'processing' ? 'active' : '' ?>" data-status="processing">Processing</a>
    <a href="seller.orders.php?status=shipped" class="tab <?= ($_GET['status'] ?? '') === 'shipped' ? 'active' : '' ?>" data-status="shipped">Shipped</a>
    <a href="seller.orders.php?status=delivered" class="tab <?= ($_GET['status'] ?? '') === 'delivered' ? 'active' : '' ?>" data-status="delivered">Delivered</a>
    <a href="seller.orders.php?status=cancelled" class="tab <?= ($_GET['status'] ?? '') === 'cancelled' ? 'active' : '' ?>" data-status="cancelled">Cancelled</a>
</div>

<table class="order-table">
  <thead>
  <tr>
    <th>ORDER ID</th>
    <th>PRODUCT IMAGE </th>
    <th>PRODUCT NAME</th>
    <th>QUANTITY</th>
    <th>TOTAL</th>
    <th>Color</th>
    <th>Price</th>
    <th>BUYER</th>
    <th>STATUS</th>
    <th>ACTION</th>
  </tr>
</thead>
<tbody>
<?php
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$statusFilter = $_GET['status'] ?? '';
$validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    echo "<tr><td colspan='10'>Seller not logged in.</td></tr>";
} else {
    // Build WHERE clause and parameters
    $whereClause = "p.seller_id = ?";
    $params = [$seller_id];
    $types = "i";

    if (in_array($statusFilter, $validStatuses)) {
        $whereClause .= " AND o.status = ?";
        $params[] = $statusFilter;
        $types .= "s";
    }

    // Count total orders for this seller
    $countSql = "
        SELECT COUNT(DISTINCT o.order_id) AS total 
        FROM orders o
        INNER JOIN order_items oi ON o.order_id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.product_id
        WHERE $whereClause
    ";
    
    $countStmt = $conn->prepare($countSql);
    if ($countStmt === false) {
        die("Error preparing count query: " . $conn->error);
    }
    
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalOrders = $countResult->fetch_assoc()['total'];
    $countStmt->close();

    $totalPages = ceil($totalOrders / $limit);

    // Fetch orders for this seller with buyer information
    $orderSql = "
        SELECT DISTINCT o.order_id, o.order_date, o.total_price, o.status, o.user_id,
               u.Username AS username, u.EmailAdd AS email, u.phone_number AS phone,
               o.payment_method
        FROM orders o
        INNER JOIN order_items oi ON o.order_id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN buyers u ON o.user_id = u.AccountID
        WHERE $whereClause 
        ORDER BY o.order_date DESC 
        LIMIT ? OFFSET ?
    ";
    
    // Add limit and offset parameters
    $orderParams = $params;
    $orderParams[] = $limit;
    $orderParams[] = $offset;
    $orderTypes = $types . "ii";

    $orderStmt = $conn->prepare($orderSql);
    if ($orderStmt === false) {
        die("Error preparing order query: " . $conn->error);
    }
    
    $orderStmt->bind_param($orderTypes, ...$orderParams);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();

    if ($orderResult->num_rows === 0) {
        echo "<tr><td colspan='10' style='text-align: center; padding: 20px;'>No orders found for your products.</td></tr>";
    } else {
        while ($order = $orderResult->fetch_assoc()) {
            // Get shipping address
            $shipping_address = 'N/A';
            if ($order['user_id']) {
                $addressStmt = $conn->prepare("
                    SELECT CONCAT_WS(', ', street_address, barangay, province, region, postal_code) AS full_address 
                    FROM buyer_addresses 
                    WHERE buyer_id = ? AND address_type = 'shipping' AND is_default = 1
                ");
                
                if ($addressStmt) {
                    $addressStmt->bind_param("i", $order['user_id']);
                    $addressStmt->execute();
                    $addressResult = $addressStmt->get_result();
                    $addressRow = $addressResult->fetch_assoc();
                    $shipping_address = $addressRow['full_address'] ?? 'N/A';
                    $addressStmt->close();
                }
            }

            // Get only the items from this seller for this order
            $itemsStmt = $conn->prepare("
                SELECT oi.product_id, oi.color, oi.size, oi.quantity, oi.price, oi.main_image, p.product_name 
                FROM order_items oi 
                INNER JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = ? AND p.seller_id = ?
            ");
            
            if ($itemsStmt === false) {
                die("Error preparing items query: " . $conn->error);
            }
            
            $itemsStmt->bind_param("ii", $order['order_id'], $seller_id);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();
            $items = $itemsResult->fetch_all(MYSQLI_ASSOC);
            $itemsCount = count($items);

            if ($itemsCount > 0) {
                $currentStatus = $order['status'];
                $nextStatus = null;
                if ($currentStatus === 'pending') {
                    $nextStatus = 'processing';
                } elseif ($currentStatus === 'processing') {
                    $nextStatus = 'shipped';
                }

                $firstItem = $items[0];
                $imagePath = !empty($firstItem['main_image']) ? $firstItem['main_image'] : 'pics/default.jpg';

                // Prepare order details for modal
                $orderDetails = [
                    'order_id' => $order['order_id'],
                    'order_date' => $order['order_date'],
                    'status' => $order['status'],
                    'total_price' => $order['total_price'],
                    'buyer_name' => $order['username'] ?? 'N/A',
                    'buyer_email' => $order['email'] ?? 'N/A',
                    'buyer_phone' => $order['phone'] ?? 'N/A',
                    'shipping_address' => $shipping_address,
                    'payment_method' => $order['payment_method'] ?? 'N/A',
                    'items' => $items
                ];

                echo "<tr>";
                echo '<td rowspan="' . $itemsCount . '">O' . str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) . '</td>';
                echo '<td><img src="' . htmlspecialchars($imagePath) . '" width="130" height="60" style="object-fit:contain;"></td>';
                echo '<td>' . htmlspecialchars($firstItem['product_name']) . '</td>';
                echo '<td>' . htmlspecialchars($firstItem['quantity']) . '</td>';
                
                // Calculate total for seller's items only
                $sellerTotal = array_sum(array_map(function($item) { 
                    return $item['price'] * $item['quantity']; 
                }, $items));
                
                echo '<td rowspan="' . $itemsCount . '">₱' . number_format($sellerTotal, 2) . '</td>';
                echo '<td>' . htmlspecialchars($firstItem['color']) . '</td>';
                echo '<td>₱' . number_format($firstItem['price'], 2) . '</td>';
                echo '<td rowspan="' . $itemsCount . '">' . htmlspecialchars($order['username'] ?? 'User ' . $order['user_id']) . '</td>';

                echo '<td rowspan="' . $itemsCount . '">';
                echo "<span class='status-badge " . htmlspecialchars($currentStatus) . "'>" . ucfirst($currentStatus) . "</span>";
                echo '</td>';

                echo '<td rowspan="' . $itemsCount . '">';
                echo '<div class="action-buttons">';
                
                // Details Button
                echo '<button type="button" class="details-btn" data-order-details="' . 
                     htmlspecialchars(json_encode($orderDetails)) . '">';
                echo '<i class="fas fa-eye"></i> Details</button>';
                
                // Update Button
                if ($nextStatus) {
                    echo '<button type="button" class="update-btn" data-order-id="' . $order['order_id'] . 
                         '" data-current-status="' . $currentStatus . '" data-next-status="' . $nextStatus . '">';
                    echo '<i class="fas fa-sync-alt"></i> Update</button>';
                } else {
                    echo '<span style="color: gray; font-size: 0.875rem;">No further action</span>';
                }
                
                echo '</div>';
                echo '</td>';
                echo '</tr>';

                // Display additional items for this order
                for ($i = 1; $i < $itemsCount; $i++) {
                    $item = $items[$i];
                    $imagePath = !empty($item['main_image']) ? $item['main_image'] : 'pics/default.jpg';

                    echo "<tr>";
                    echo '<td><img src="' . htmlspecialchars($imagePath) . '" width="130" height="60" style="object-fit:contain;"></td>';
                    echo '<td>' . htmlspecialchars($item['product_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['quantity']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['color']) . '</td>';
                    echo '<td>₱' . number_format($item['price'], 2) . '</td>';
                    echo '</tr>';
                }
            }

            $itemsStmt->close();
        }
    }
    $orderStmt->close();
}
$conn->close();
?>
</tbody>
</table>

<!-- Pagination -->
<p class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></p>
<div class="pagination">
<?php
$queryStatus = $statusFilter ? '&status=' . urlencode($statusFilter) : '';
$visiblePages = 5;
$startPage = max(1, $page - floor($visiblePages / 2));
$endPage = $startPage + $visiblePages - 1;

if ($endPage > $totalPages) {
    $endPage = $totalPages;
    $startPage = max(1, $endPage - $visiblePages + 1);
}

if ($page > 1) {
    echo '<a href="?page=' . ($page - 1) . $queryStatus . '#product-table" class="nav-button"> &laquo; </a>';
}

if ($page < $totalPages) {
    echo '<a href="?page=' . ($page + 1) . $queryStatus . '#product-table" class="nav-button"> &raquo; </a>';
}

// Page numbers
for ($i = $startPage; $i <= $endPage; $i++) {
    $class = ($i == $page) ? 'active-page' : '';
    echo '<a href="?page=' . $i . '#product-table" class="' . $class . '">' . $i . '</a>';
}
?>
</div>

</section>
        </div>

        <!-- Enhanced Modal for Order Details -->
        <div id="orderModal" class="modal">
          <div class="modal-content">
            <div class="modal-header">
              <h2 id="modalTitle">Order Details</h2>
              <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="modalContent">
              Loading order details...
            </div>
          </div>
        </div>
    </div>
  </div>

  <script>
    function navigateWithConfirmation(url, title, message) {
      Swal.fire({
        title: title,
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, go there!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url;
        }
      });
    }

    function formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    function generateOrderDetailsHTML(orderData) {
      const sellerTotal = orderData.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
      
      return `
        <div class="order-details-grid">
          <div class="detail-card">
            <h3><i class="fas fa-receipt"></i> Order Information</h3>
            <p><span class="label">Order ID:</span> O${String(orderData.order_id).padStart(5, '0')}</p>
            <p><span class="label">Order Date:</span> ${formatDate(orderData.order_date)}</p>
            <p><span class="label">Status:</span> <span class="status-badge ${orderData.status}">${orderData.status.charAt(0).toUpperCase() + orderData.status.slice(1)}</span></p>
            <p><span class="label">Total Amount:</span> <strong>₱${sellerTotal.toFixed(2)}</strong></p>
          </div>
          
          <div class="detail-card">
            <h3><i class="fas fa-user"></i> Customer Information</h3>
            <p><span class="label">Name:</span> ${orderData.buyer_name}</p>
            <p><span class="label">Email:</span> ${orderData.buyer_email}</p>
            <p><span class="label">Phone:</span> ${orderData.buyer_phone}</p>
          </div>
          
          <div class="detail-card">
            <h3><i class="fas fa-truck"></i> Shipping Information</h3>
            <p><span class="label">Address:</span> ${orderData.shipping_address}</p>
            <p><span class="label">Payment Method:</span> ${orderData.payment_method}</p>
          </div>
        </div>
        
        <div class="items-section">
          <h3><i class="fas fa-box"></i> Order Items</h3>
          ${orderData.items.map(item => `
            <div class="item-card">
              <img src="${item.main_image || 'pics/default.jpg'}" alt="${item.product_name}" class="item-image">
              <div class="item-details">
                <h4>${item.product_name}</h4>
                <div class="item-meta">
                  <span>Color: ${item.color}</span>
                  ${item.size ? `<span>Size: ${item.size}</span>` : ''}
                  <span>Qty: ${item.quantity}</span>
                </div>
                <div class="item-price">₱${parseFloat(item.price).toFixed(2)} each</div>
              </div>
            </div>
          `).join('')}
        </div>
      `;
    }

    document.addEventListener('DOMContentLoaded', function () {
      const tabs = document.querySelectorAll('.tab');
      const rows = document.querySelectorAll('.order-table tr:not(:first-child)');
      const modal = document.getElementById('orderModal');
      const modalContent = document.getElementById('modalContent');
      const modalTitle = document.getElementById('modalTitle');
      const closeBtn = document.querySelector('.close');

      // Tab functionality
      tabs.forEach(tab => {
        tab.addEventListener('click', function () {
          tabs.forEach(t => t.classList.remove('active'));
          tab.classList.add('active');
          const status = tab.getAttribute('data-status');

          rows.forEach(row => {
            const stat = row.querySelector('.status')?.classList[1];
            if (status === 'allorders' || stat === status) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          });
        });
      });

      // Details button functionality
      document.querySelectorAll('.details-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          
          try {
            const orderData = JSON.parse(this.getAttribute('data-order-details'));
            modalTitle.textContent = `Order O${String(orderData.order_id).padStart(5, '0')} Details`;
            modalContent.innerHTML = generateOrderDetailsHTML(orderData);
            modal.style.display = 'block';
          } catch (error) {
            console.error('Error parsing order details:', error);
            Swal.fire({
              title: 'Error!',
              text: 'Failed to load order details. Please try again.',
              icon: 'error',
              confirmButtonText: 'OK'
            });
          }
        });
      });

      // Update button functionality
      document.querySelectorAll('.update-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          
          const orderId = this.getAttribute('data-order-id');
          const currentStatus = this.getAttribute('data-current-status');
          const nextStatus = this.getAttribute('data-next-status');
          
          Swal.fire({
            title: 'Update Order Status?',
            text: `Change order status from "${currentStatus}" to "${nextStatus}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, mark as ${nextStatus}!`,
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              const formData = new FormData();
              formData.append('order_id', orderId);
              formData.append('current_status', currentStatus);
              
              fetch('update_status.php', {
                method: 'POST',
                body: formData
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  const row = this.closest('tr');
                  const statusSpan = row.querySelector('.status-badge');
                  statusSpan.textContent = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);
                  statusSpan.className = `status-badge ${data.new_status}`;
                  
                  if (data.new_status === 'shipped') {
                    this.style.display = 'none';
                    const parent = this.parentElement;
                    parent.querySelector('.update-btn').style.display = 'none';
                  } else {
                    const newNextStatus = data.new_status === 'processing' ? 'shipped' : null;
                    if (newNextStatus) {
                      this.setAttribute('data-current-status', data.new_status);
                      this.setAttribute('data-next-status', newNextStatus);
                    }
                  }
                  
                  Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'Great!'
                  });
                } else {
                  Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to update order status. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                  });
                }
              })
              .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                  title: 'Error!',
                  text: 'Network error. Please try again.',
                  icon: 'error',
                  confirmButtonText: 'OK'
                });
              });
            }
          });
        });
      });

      // Modal close functionality
      closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
      });

      window.addEventListener('click', function (event) {
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });

      // Dropdown functions
      window.toggleDropdown = function() {
        const dropdown = document.getElementById('dropdownMenu');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
      }

      window.showDropdown = function() {
        document.getElementById('dropdownMenu').style.display = 'block';
      }

      let hideTimeout;
      window.hideDropdownDelayed = function() {
        hideTimeout = setTimeout(() => {
          document.getElementById('dropdownMenu').style.display = 'none';
        }, 300);
      }

      window.clearHideTimeout = function() {
        clearTimeout(hideTimeout);
      }
    });
  </script>
</body>
</html>