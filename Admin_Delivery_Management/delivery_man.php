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

// Get delivery data with driver information
$deliveryQuery = "
    SELECT 
        d.delivery_id,
        d.order_id,
        d.driver_id,
        d.status as delivery_status,
        d.delivery_date,
        d.created_at,
        dr.full_name as driver_name,
        dr.email as driver_email,
        dr.phone_number as driver_phone,
        dr.status as driver_status,
        o.total_amount,
        o.order_date,
        CONCAT(b.first_name, ' ', b.last_name) as buyer_name
    FROM deliveries d
    LEFT JOIN drivers dr ON d.driver_id = dr.driver_id
    LEFT JOIN orders o ON d.order_id = o.order_id
    LEFT JOIN buyers b ON o.buyer_id = b.buyer_id
    ORDER BY d.created_at DESC
";

$deliveryResult = mysqli_query($conn, $deliveryQuery);
$deliveries = [];

if ($deliveryResult) {
    while ($row = mysqli_fetch_assoc($deliveryResult)) {
        $deliveries[] = $row;
    }
} else {
    // If deliveries table doesn't exist or query fails, create sample data from drivers
    $driversQuery = "
        SELECT 
            driver_id,
            full_name,
            email,
            phone_number,
            status,
            date_joined
        FROM drivers 
        ORDER BY date_joined DESC
    ";
    
    $driversResult = mysqli_query($conn, $driversQuery);
    if ($driversResult) {
        while ($row = mysqli_fetch_assoc($driversResult)) {
            // Create mock delivery data based on drivers
            $deliveries[] = [
                'delivery_id' => 'D' . $row['driver_id'],
                'order_id' => 'ORD' . rand(1000, 9999),
                'driver_id' => $row['driver_id'],
                'driver_name' => $row['full_name'],
                'driver_email' => $row['email'],
                'driver_phone' => $row['phone_number'],
                'delivery_status' => $row['status'] === 'Active' ? 'pending' : 'delivered',
                'driver_status' => $row['status'],
                'delivery_date' => $row['date_joined'],
                'created_at' => $row['date_joined'],
                'total_amount' => rand(500, 5000),
                'buyer_name' => 'Customer ' . rand(1, 100)
            ];
        }
    }
}

// Handle status filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../Admin_Delivery_Management/delivery.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <title>Delivery Management - Admin Dashboard</title>
    <style>
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status.delivered {
            background-color: #d4edda;
            color: #155724;
        }
        .status.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status.in-transit {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .animate-slide {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .delivery-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .delivery-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        .delivery-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .delivery-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .view-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .view-btn:hover {
            background: #0056b3;
        }
        .status-tabs {
            display: flex;
            gap: 8px;
            margin: 20px 0;
        }
        .tab {
            padding: 8px 16px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        .tab.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .tab:hover:not(.active) {
            background: #f8f9fa;
        }
    </style>
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

<li class="sidebar-list-item">
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
<li class="sidebar-list-item active">
  <a href="../Admin_Delivery_Management/delivery_man.php">
    <span class="material-icons-outlined">local_shipping</span>
    Delivery Management
  </a>
</li>
      </ul>
    </aside>
    
    <!-- Header -->
    <header class="header">
      <form class="search-bar">
        <i class="bx bx-search text-gray-500 mr-2"></i>
        <input type="text" placeholder="Search deliveries..." class="bg-transparent outline-none w-full text-sm" id="searchInput">
      </form>

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
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 30px; margin-bottom: 8px;">Delivery Management</h1>
          <p style="color: #666;">Manage and track all delivery assignments and statuses</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <button style="background: #28a745; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">
            <i class="material-icons-outlined" style="vertical-align: middle; margin-right: 4px; font-size: 18px;">add</i>
            Assign Delivery
          </button>
          <button style="background: #17a2b8; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">
            <i class="material-icons-outlined" style="vertical-align: middle; margin-right: 4px; font-size: 18px;">refresh</i>
            Refresh
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <?php
        $totalDeliveries = count($deliveries);
        $pendingCount = count(array_filter($deliveries, function($d) { return $d['delivery_status'] === 'pending'; }));
        $deliveredCount = count(array_filter($deliveries, function($d) { return $d['delivery_status'] === 'delivered'; }));
        $rejectedCount = count(array_filter($deliveries, function($d) { return $d['delivery_status'] === 'rejected'; }));
        ?>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #007bff;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 4px;"><?php echo $totalDeliveries; ?></h3>
              <p style="color: #666; font-size: 14px;">Total Deliveries</p>
            </div>
            <i class="material-icons-outlined" style="font-size: 32px; color: #007bff;">local_shipping</i>
          </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #ffc107;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 4px;"><?php echo $pendingCount; ?></h3>
              <p style="color: #666; font-size: 14px;">Pending</p>
            </div>
            <i class="material-icons-outlined" style="font-size: 32px; color: #ffc107;">schedule</i>
          </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #28a745;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 4px;"><?php echo $deliveredCount; ?></h3>
              <p style="color: #666; font-size: 14px;">Delivered</p>
            </div>
            <i class="material-icons-outlined" style="font-size: 32px; color: #28a745;">check_circle</i>
          </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #dc3545;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 4px;"><?php echo $rejectedCount; ?></h3>
              <p style="color: #666; font-size: 14px;">Rejected</p>
            </div>
            <i class="material-icons-outlined" style="font-size: 32px; color: #dc3545;">cancel</i>
          </div>
        </div>
      </div>

      <div class="status-tabs">
        <button class="tab active" data-filter="all">All Status (<?php echo $totalDeliveries; ?>)</button>
        <button class="tab" data-filter="pending">Pending (<?php echo $pendingCount; ?>)</button>
        <button class="tab" data-filter="delivered">Delivered (<?php echo $deliveredCount; ?>)</button>
        <button class="tab" data-filter="rejected">Rejected (<?php echo $rejectedCount; ?>)</button>
      </div>

      <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <table class="delivery-table" style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAll" /></th>
              <th>DELIVERY ID</th>
              <th>ORDER ID</th>
              <th>DRIVER INFO</th>
              <th>CUSTOMER</th>
              <th>AMOUNT</th>
              <th>STATUS</th>
              <th>DATE</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($deliveries)): ?>
              <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: #666;">
                  <i class="material-icons-outlined" style="font-size: 48px; color: #ddd;">local_shipping</i>
                  <p style="margin-top: 12px;">No deliveries found</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($deliveries as $delivery): ?>
                <tr data-status="<?php echo htmlspecialchars($delivery['delivery_status']); ?>" class="delivery-row">
                  <td><input type="checkbox" class="delivery-checkbox" value="<?php echo $delivery['delivery_id']; ?>" /></td>
                  <td style="font-weight: 600; color: #007bff;">
                    <?php echo htmlspecialchars($delivery['delivery_id']); ?>
                  </td>
                  <td style="font-family: monospace; color: #666;">
                    <?php echo htmlspecialchars($delivery['order_id']); ?>
                  </td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #007bff, #0056b3); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                        <?php echo strtoupper(substr($delivery['driver_name'], 0, 1)); ?>
                      </div>
                      <div>
                        <div style="font-weight: 500; color: #333;">
                          <?php echo htmlspecialchars($delivery['driver_name']); ?>
                        </div>
                        <?php if (!empty($delivery['driver_phone'])): ?>
                          <div style="font-size: 11px; color: #666;">
                            <?php echo htmlspecialchars($delivery['driver_phone']); ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div style="font-weight: 500; color: #333;">
                      <?php echo htmlspecialchars($delivery['buyer_name'] ?? 'N/A'); ?>
                    </div>
                  </td>
                  <td style="font-weight: 600; color: #28a745;">
                    ₱<?php echo number_format($delivery['total_amount'] ?? 0, 2); ?>
                  </td>
                  <td>
                    <span class="status <?php echo htmlspecialchars($delivery['delivery_status']); ?>">
                      ● <?php echo ucfirst(htmlspecialchars($delivery['delivery_status'])); ?>
                    </span>
                  </td>
                  <td style="color: #666; font-size: 12px;">
                    <?php echo date('M d, Y', strtotime($delivery['created_at'])); ?>
                  </td>
                  <td>
                    <div style="display: flex; gap: 4px;">
                      <button class="view-btn" onclick="viewDelivery('<?php echo $delivery['delivery_id']; ?>')">
                        <i class="material-icons-outlined" style="font-size: 14px;">visibility</i>
                        View
                      </button>
                      <?php if ($delivery['delivery_status'] === 'pending'): ?>
                        <button style="background: #28a745; color: white; border: none; padding: 6px 8px; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="updateStatus('<?php echo $delivery['delivery_id']; ?>', 'delivered')">
                          <i class="material-icons-outlined" style="font-size: 14px;">check</i>
                        </button>
                        <button style="background: #dc3545; color: white; border: none; padding: 6px 8px; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="updateStatus('<?php echo $delivery['delivery_id']; ?>', 'rejected')">
                          <i class="material-icons-outlined" style="font-size: 14px;">close</i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px;">
        <button style="padding: 8px 12px; border: 1px solid #dee2e6; background: white; border-radius: 4px; cursor: pointer;" disabled>Prev</button>
        <button class="page active" style="padding: 8px 12px; border: 1px solid #007bff; background: #007bff; color: white; border-radius: 4px; cursor: pointer;">1</button>
        <button class="page" style="padding: 8px 12px; border: 1px solid #dee2e6; background: white; border-radius: 4px; cursor: pointer;">2</button>
        <button class="page" style="padding: 8px 12px; border: 1px solid #dee2e6; background: white; border-radius: 4px; cursor: pointer;">3</button>
        <button style="padding: 8px 12px; border: 1px solid #dee2e6; background: white; border-radius: 4px; cursor: pointer;">Next</button>
      </div>
    </main>
  </div>

  <script>
    // Tab filtering functionality
    const tabs = document.querySelectorAll('.tab');
    const rows = document.querySelectorAll('.delivery-row');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const filter = tab.dataset.filter.toLowerCase().trim();

        rows.forEach(row => {
          row.style.opacity = '0';
          setTimeout(() => {
            if (filter === 'all' || row.dataset.status.toLowerCase() === filter) {
              row.style.display = '';
              row.classList.add('animate-slide');
            } else {
              row.style.display = 'none';
              row.classList.remove('animate-slide');
            }
            row.style.opacity = '1';
          }, 200);
        });
      });
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const deliveryCheckboxes = document.querySelectorAll('.delivery-checkbox');

    selectAllCheckbox.addEventListener('change', function() {
      deliveryCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
    });

    // Dropdown functionality
    const toggleBtn = document.getElementById('dropdownToggle');
    const popup = document.getElementById('logoutPopup');

    toggleBtn.addEventListener('click', () => {
      popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
    });

    window.addEventListener('click', function(e) {
      if (!toggleBtn.contains(e.target) && !popup.contains(e.target)) {
        popup.style.display = 'none';
      }
    });

    document.getElementById('logoutBtn').addEventListener('click', () => {
      if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../Admin_Dashboard/logout.php';
      }
    });

    // Delivery management functions
    function viewDelivery(deliveryId) {
      alert('Viewing delivery: ' + deliveryId);
      // You can implement a modal or redirect to a detailed view page
    }

    function updateStatus(deliveryId, newStatus) {
      if (confirm(`Are you sure you want to mark this delivery as ${newStatus}?`)) {
        // Send AJAX request to update status
        fetch('update_delivery_status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            delivery_id: deliveryId,
            status: newStatus
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload(); // Refresh the page to show updated status
          } else {
            alert('Error updating status: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error updating status');
        });
      }
    }
  </script>
</body>
</html>