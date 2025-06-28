<?php
  session_start();
  $conn = mysqli_connect("localhost", "root", "", "marketplace");

  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

  // Check if the admin is logged in
  if (!isset($_SESSION['Username'])) {
      header("Location: ../Admin_Dashboard/login.php"); // or admin_login.php
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

  // Pagination setup
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $limit = 5;
  $offset = ($page - 1) * $limit;

  // Get current tab (default to seller)
  $currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'seller';

  // Search and status filter
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

  // Function to fetch sellers
  function fetchSellers($conn, $limit, $offset, $search = '', $statusFilter = '') {
      $query = "SELECT id, username, email, created_at, status FROM sellers WHERE 1";

      if ($search !== '') {
          $query .= " AND (username LIKE ? OR id LIKE ?)";
      }

      if ($statusFilter !== '') {
          $query .= " AND status = ?";
      }

      $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

      $stmt = mysqli_prepare($conn, $query);
      if (!$stmt) {
          die("Prepare failed in fetchSellers: " . mysqli_error($conn));
      }

      if ($search !== '' && $statusFilter !== '') {
          $searchParam = "%$search%";
          mysqli_stmt_bind_param($stmt, "sssii", $searchParam, $searchParam, $statusFilter, $limit, $offset);
      } elseif ($search !== '') {
          $searchParam = "%$search%";
          mysqli_stmt_bind_param($stmt, "ssii", $searchParam, $searchParam, $limit, $offset);
      } elseif ($statusFilter !== '') {
          mysqli_stmt_bind_param($stmt, "sii", $statusFilter, $limit, $offset);
      } else {
          mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
      }

      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      $sellers = [];
      while ($row = mysqli_fetch_assoc($result)) {
          $sellers[] = $row;
      }
      mysqli_stmt_close($stmt);
      return $sellers;
  }

  // Function to fetch buyers
  function fetchBuyers($conn, $limit, $offset, $search = '', $statusFilter = '') {
      $query = "SELECT AccountID, Username, email, date_joined, status FROM buyers WHERE 1";

      if ($search !== '') {
          $query .= " AND (Username LIKE ? OR AccountID LIKE ?)";
      }

      if ($statusFilter !== '') {
          $query .= " AND status = ?";
      }

      $query .= " ORDER BY date_joined DESC LIMIT ? OFFSET ?";

      $stmt = mysqli_prepare($conn, $query);
      if (!$stmt) {
          die("Prepare failed in fetchBuyers: " . mysqli_error($conn));
      }

      if ($search !== '' && $statusFilter !== '') {
          $searchParam = "%$search%";
          mysqli_stmt_bind_param($stmt, "sssii", $searchParam, $searchParam, $statusFilter, $limit, $offset);
      } elseif ($search !== '') {
          $searchParam = "%$search%";
          mysqli_stmt_bind_param($stmt, "ssii", $searchParam, $searchParam, $limit, $offset);
      } elseif ($statusFilter !== '') {
          mysqli_stmt_bind_param($stmt, "sii", $statusFilter, $limit, $offset);
      } else {
          mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
      }

      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      $buyers = [];
      while ($row = mysqli_fetch_assoc($result)) {
          $buyers[] = $row;
      }
      mysqli_stmt_close($stmt);
      return $buyers;
  }

  // Function to fetch drivers
  function fetchDrivers($conn, $limit, $offset, $search = '', $statusFilter = '') {
      $query = "SELECT driver_id, full_name, email, phone_number, date_joined, status FROM drivers WHERE 1";

      if ($search !== '') {
          $query .= " AND (full_name LIKE ? OR driver_id LIKE ?)";
      }

      if ($statusFilter !== '') {
          $query .= " AND status = ?";
      }

      $query .= " ORDER BY date_joined DESC LIMIT ? OFFSET ?";

      $stmt = mysqli_prepare($conn, $query);
      if (!$stmt) {
          die("Prepare failed in fetchDrivers: " . mysqli_error($conn));
      }

      if ($search !== '' && $statusFilter !== '') {
          $searchParam = "%$search%";
          mysqli_stmt_bind_param($stmt, "sssii", $searchParam, $searchParam, $statusFilter, $limit, $offset);
      } elseif ($search !== '') {
          $searchParam = "%$search%";
          mysqli_stmt_bind_param($stmt, "ssii", $searchParam, $searchParam, $limit, $offset);
      } elseif ($statusFilter !== '') {
          mysqli_stmt_bind_param($stmt, "sii", $statusFilter, $limit, $offset);
      } else {
          mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
      }

      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      $drivers = [];
      while ($row = mysqli_fetch_assoc($result)) {
          $drivers[] = $row;
      }
      mysqli_stmt_close($stmt);
      return $drivers;
  }

  // Fetch data based on current tab
  $sellers = fetchSellers($conn, $limit, $offset, $search, $statusFilter);
  $buyers = fetchBuyers($conn, $limit, $offset, $search, $statusFilter);
  $drivers = fetchDrivers($conn, $limit, $offset, $search, $statusFilter);

  // Calculate total number of sellers, buyers, drivers
  $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sellers");
  $row = mysqli_fetch_assoc($result);
  $totalSellers = $row['total'];

  $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buyers");
  $row = mysqli_fetch_assoc($result);
  $totalBuyers = $row['total'];

  $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM drivers");
  $row = mysqli_fetch_assoc($result);
  $totalDrivers = $row['total'];

  // Calculate total pages based on current tab
  switch($currentTab) {
      case 'buyer':
          $totalRecords = $totalBuyers;
          break;
      case 'driver':
          $totalRecords = $totalDrivers;
          break;
      default:
          $totalRecords = $totalSellers;
  }

  $totalPages = ceil($totalRecords / $limit);

  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <script src="https://cdn.tailwindcss.com"></script>
      <link href="../Admin_User_management/user-management.css" rel="stylesheet" />
      <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
      <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
      <title>User Management - Admin Dashboard</title>
      <style>
        .tab-content {
          display: none;
        }
        .tab-content.active {
          display: block;
        }
        .status.active {
          color: #10b981;
        }
        .status.suspended {
          color: #ef4444;
        }
        .status.pending {
          color: #f59e0b;
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

<li class="sidebar-list-item dashboard-item">
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
      <div class="content-wrapper">
        <div class="page-header">
          <h2>User Management</h2>
          <p>Displays a list of all platform users, including buyers, sellers, and drivers.</p>
        </div>
      
<!-- Search bar -->
<form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-[10px]">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
        <div class="relative mb-[10px]">
            <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search by username, ID..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-[10px]">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="suspended" <?php echo $statusFilter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
        </select>
    </div>

    <div class="flex items-end space-x-2 mb-[10px]">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <i class='bx bx-filter-alt mr-2'></i>Filter
        </button>
        <a href="user-management.php" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
            <i class='bx bx-refresh mr-2'></i>Reset
        </a>
    </div>
</form>


        <div class="tabs">
          <button class="tab active" data-tab="seller">Seller</button>
          <button class="tab" data-tab="buyer">Buyer</button>
          <button class="tab" data-tab="driver">Driver</button>
        </div>

        <div class="table-section">
          <!-- Seller Tab Content -->
          <div class="tab-content active" id="seller">
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th><input type="checkbox" /></th>
                    <th>SELLER ID</th>
                    <th>STORE NAME</th>
                    <th>EMAIL</th>
                    <th>DATE JOINED</th>
                    <th>SELLER STATUS</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($sellers)): ?>
                    <tr>
                      <td colspan="6" class="text-center py-4">No sellers found</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($sellers as $seller): ?>
                      <tr>
                        <td><input type="checkbox" /></td>
                        <td><?php echo htmlspecialchars($seller['id']); ?></td>
                        <td><?php echo htmlspecialchars($seller['username']); ?></td>
                        <td><?php echo htmlspecialchars($seller['email']); ?></td>
                        <td><?php echo htmlspecialchars($seller['created_at']); ?></td>
                        <td>
                          <span class="status <?php echo strtolower($seller['status']); ?>">
                            ● <?php echo ucfirst($seller['status']); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Buyer Tab Content -->
          <div class="tab-content" id="buyer">
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th><input type="checkbox" /></th>
                    <th>BUYER ID</th>
                    <th>NAME</th>
                    <th>EMAIL</th>
                    <th>DATE JOINED</th>
                    <th>BUYER STATUS</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($buyers)): ?>
                    <tr>
                      <td colspan="6" class="text-center py-4">No buyers found</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($buyers as $buyer): ?>
                      <tr>
                        <td><input type="checkbox" /></td>
                        <td><?php echo htmlspecialchars($buyer['AccountID']); ?></td>
                        <td><?php echo htmlspecialchars($buyer['Username']); ?></td>
                        <td><?php echo htmlspecialchars($buyer['email']); ?></td>
                        <td><?php echo htmlspecialchars($buyer['date_joined']); ?></td>
                        <td>
                          <span class="status <?php echo strtolower($buyer['status']); ?>">
                            ● <?php echo ucfirst($buyer['status']); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Driver Tab Content -->
          <div class="tab-content" id="driver">
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th><input type="checkbox" /></th>
                    <th>DRIVER ID</th>
                    <th>FULL NAME</th>
                    <th>EMAIL</th>
                    <th>CONTACT</th>
                    <th>DATE JOINED</th>
                    <th>STATUS</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($drivers)): ?>
                    <tr>
                      <td colspan="7" class="text-center py-4">No drivers found</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($drivers as $driver): ?>
                      <tr>
                        <td><input type="checkbox" /></td>
                        <td><?php echo htmlspecialchars($driver['driver_id']); ?></td>
                        <td><?php echo htmlspecialchars($driver['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($driver['email']); ?></td>
                        <td><?php echo htmlspecialchars($driver['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($driver['date_joined']); ?></td>
                        <td>
                          <span class="status <?php echo strtolower($driver['status']); ?>">
                            ● <?php echo ucfirst($driver['status']); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Table Footer with Pagination -->
          <div class="table-footer">
            <div class="entries-info">
              Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalRecords); ?> of <?php echo $totalRecords; ?> entries
            </div>
            <div class="pagination">
              <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&tab=<?php echo $currentTab; ?>" class="page-btn">Prev</a>
              <?php endif; ?>
              
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&tab=<?php echo $currentTab; ?>" 
                   class="page-btn <?php echo $i == $page ? 'current' : ''; ?>">
                  <?php echo $i; ?>
                </a>
              <?php endfor; ?>
              
              <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&tab=<?php echo $currentTab; ?>" class="page-btn">Next</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
const tabs = document.querySelectorAll('.tab');
const tabContents = document.querySelectorAll('.tab-content');

let currentTab = null;

function activateTab(tabName) {
  if (currentTab === tabName) return;
  
  // Remove active from all tabs and contents
  tabs.forEach(t => t.classList.remove('active'));
  tabContents.forEach(tc => {
    // Slide out active content
    if (tc.classList.contains('active')) {
      tc.classList.remove('active');
      // Force reflow for transition reset
      void tc.offsetWidth;
    }
  });
  
  // Activate new tab button
  const newTabBtn = document.querySelector(`.tab[data-tab="${tabName}"]`);
  if (newTabBtn) newTabBtn.classList.add('active');

  // Activate new tab content with animation
  const newTabContent = document.getElementById(tabName);
  if (newTabContent) {
    newTabContent.classList.add('active');
  }

  currentTab = tabName;

  // Update URL without reload
  const url = new URL(window.location);
  url.searchParams.set('tab', tabName);
  url.searchParams.set('page', '1'); // Reset page to 1
  window.history.pushState({}, '', url);
}

// Initialize active tab from URL or default
const urlParams = new URLSearchParams(window.location.search);
const initTab = urlParams.get('tab') || 'seller';
activateTab(initTab);

// Event listeners
tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    activateTab(tab.getAttribute('data-tab'));
  });
});

  </script>
</body>
</html>