<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the admin is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: dashboard.php");
    exit();
}

$Username = $_SESSION['Username'];

// Handle AJAX requests for status updates - MUST BE BEFORE ANY HTML OUTPUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Ensure no output before this point
    ob_clean(); // Clear any previous output
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_withdrawal_status') {
        $id = (int)$_POST['id']; // Cast to integer for security
        $status = $_POST['status'];
        $admin_notes = isset($_POST['admin_note']) ? $_POST['admin_note'] : '';
        
        // Validate status
        if (!in_array($status, ['approved', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit();
        }
        
        // Use the correct table name (payout_requests)
        $query = "UPDATE payout_requests SET status = ?, admin_note = ?, processed_date = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssi", $status, $admin_notes, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Check if any row was actually updated
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    echo json_encode(['success' => true, 'message' => 'Withdrawal request updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No withdrawal request found with that ID']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating withdrawal request: ' . mysqli_error($conn)]);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . mysqli_error($conn)]);
        }
        exit(); // CRITICAL: Exit here to prevent HTML output
    }
    
    // If we get here, unknown action
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit();
}

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

// Get all withdrawal requests with seller info - use correct table name
$sql = "SELECT pr.*, s.username as seller_name, s.email as seller_email 
        FROM payout_requests pr 
        LEFT JOIN sellers s ON pr.seller_id = s.id 
        ORDER BY pr.request_date DESC";
$result = mysqli_query($conn, $sql);

// Check if query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$withdrawals = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Calculate statistics
$stats = [
    'pending_requests' => 0,
    'approved_requests' => 0,
    'rejected_requests' => 0,
    'total_amount' => 0
];

foreach ($withdrawals as $withdrawal) {
    $stats['total_amount'] += $withdrawal['amount'];
    if ($withdrawal['status'] == 'pending') {
        $stats['pending_requests']++;
    } elseif ($withdrawal['status'] == 'approved') {
        $stats['approved_requests']++;
    } elseif ($withdrawal['status'] == 'rejected') {
        $stats['rejected_requests']++;
    }
}
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
   <link href="../Admin_Withdrawal/withdrawal.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Withdrawal Management</title>
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

<li class="sidebar-list-item">
  <a href="../Admin_Category_management/categorymanagement.php">
    <span class="material-icons-outlined">shopping_cart</span>
    Category Management
  </a>
</li>

<li class="sidebar-list-item dashboard-item">
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
                    <p class="font-medium">@<?php echo htmlspecialchars($admin['username'] ?? 'Admin'); ?></p>
                    <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($admin['AdminID'] ?? 'N/A'); ?></p>
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
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Withdrawal Management</h1>
                <p class="text-gray-600">Manage withdrawal requests from sellers efficiently.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Pending Requests</p>
                            <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['pending_requests']; ?></p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="material-icons-outlined text-yellow-600">pending</i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Approved Requests</p>
                            <p class="text-3xl font-bold text-green-600"><?php echo $stats['approved_requests']; ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="material-icons-outlined text-green-600">check_circle</i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Rejected Requests</p>
                            <p class="text-3xl font-bold text-red-600"><?php echo $stats['rejected_requests']; ?></p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="material-icons-outlined text-red-600">cancel</i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-s m">Total Amount</p>
                            <p class="text-3xl font-bold text-blue-600">₱<?php echo number_format($stats['total_amount'], 2); ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="material-icons-outlined text-blue-600">payments</i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Withdrawal Requests Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="text-xl font-semibold text-gray-900">Withdrawal Requests</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <?php if (empty($withdrawals)): ?>
                        <div class="no-data">
                            <i class="material-icons-outlined">account_balance_wallet</i>
                            <h3 class="text-lg font-medium mb-2">No Withdrawal Requests</h3>
                            <p>There are currently no withdrawal requests from sellers.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Seller Info</th>
                                    <th>Amount</th>
                                    <th>Payment Method< /th>
                                    <th>Account Info</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($withdrawals as $withdrawal): ?>
                                <tr data-status="<?php echo $withdrawal['status']; ?>">
                                    <td class="font-medium">#<?php echo str_pad($withdrawal['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div>
                                            <p class="font-medium"><?php echo htmlspecialchars($withdrawal['seller_name'] ?? 'Unknown Seller'); ?></p>
                                            <p class="text-sm text-gray-500">ID: <?php echo $withdrawal['seller_id']; ?></p>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($withdrawal['seller_email'] ?? 'N/A'); ?></p>
                                        </div>
                                    </td>
                                    <td class="font-semibold">₱<?php echo number_format($withdrawal['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($withdrawal['payment_method']); ?></td>
                                    <td>
                                        <div>
                                            <p class="font-medium"><?php echo htmlspecialchars($withdrawal['card_name']); ?></p>
                                            <p class="text-sm text-gray-500">**** **** **** <?php echo substr($withdrawal['card_number'], -4); ?></p>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($withdrawal['request_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $withdrawal['status']; ?>">
                                            <?php echo ucfirst($withdrawal['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-view" onclick="viewWithdrawal(<?php echo htmlspecialchars(json_encode($withdrawal)); ?>)">
                                                View
                                            </button>
                                            <?php if ($withdrawal['status'] == 'pending'): ?>
                                            <button class="btn btn-approve" onclick="updateWithdrawalStatus(<?php echo $withdrawal['id']; ?>, 'approved')">
                                                Approve
                                            </button>
                                            <button class="btn btn-reject" onclick="updateWithdrawalStatus(<?php echo $withdrawal['id']; ?>, 'rejected')">
                                                Reject
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // View withdrawal details
        function viewWithdrawal(withdrawal) {
            Swal.fire({
                title: 'Withdrawal Request Details',
                html: `
                    <div style="text-align: left; padding: 1rem;">
                        <p><strong>Request ID:</strong> #${String(withdrawal.id).padStart(6, '0')}</p>
                        <p><strong>Seller:</strong> ${withdrawal.seller_name || 'Unknown'}</p>
                        <p><strong>Seller ID:</strong> ${withdrawal.seller_id}</p>
                        <p><strong>Amount:</strong> ₱${parseFloat(withdrawal.amount).toLocaleString()}</p>
                        <p><strong>Payment Method:</strong> ${withdrawal.payment_method}</p>
                        <p><strong>Account Number:</strong> ${withdrawal.card_number}</p>
                        <p><strong>Account Holder:</strong> ${withdrawal.card_name}</p>
                        <p><strong>Request Date:</strong> ${new Date(withdrawal.request_date).toLocaleDateString()}</p>
                        <p><strong>Status:</strong> ${withdrawal.status.charAt(0).toUpperCase() + withdrawal.status.slice(1)}</p>
                        ${withdrawal.admin_notes ? `<p><strong>Admin Notes:</strong> ${withdrawal.admin_notes}</p>` : ''}
                        ${withdrawal.processed_date ? `<p><strong>Processed Date:</strong> ${new Date(withdrawal.processed_date).toLocaleDateString()}</p>` : ''}
                    </div>
                `,
                confirmButtonText: 'Close'
            });
        }

        // Update withdrawal status - IMPROVED ERROR HANDLING
        function updateWithdrawalStatus(id, status) {
            Swal.fire({
                title: `${status.charAt(0).toUpperCase() + status.slice(1)} Withdrawal?`,
                text: `Are you sure you want to ${status} this withdrawal request?`,
                icon: 'question',
                input: 'textarea',
                inputLabel: 'Admin Notes (optional)',
                inputPlaceholder: 'Enter any notes about this decision...',
                showCancelButton: true,
                confirmButtonText: `Yes, ${status}`,
                confirmButtonColor: status === 'approved' ? '#10b981' : '#ef4444',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we update the withdrawal request.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('action', 'update_withdrawal_status');
                    formData.append('id', id);
                    formData.append('status', status);
                    formData.append('admin_notes', result.value || '');

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Check if response is OK
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text(); // Get as text first to debug
                    })
                    .then(text => {
                        console.log('Raw response:', text); // Debug log
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                Swal.fire('Success!', data.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            console.error('Response text:', text);
                            Swal.fire('Error!', 'Invalid response from server. Check console for details.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        Swal.fire('Error!', 'Network error: ' + error.message, 'error');
                    });
                }
            });
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
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
                window.location.href = 'logout.php';
            }
        });
    </script>
</body>
</html>