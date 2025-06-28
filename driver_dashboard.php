<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['Username'] ?? 'Guest';
$driver_id = $_SESSION['driver_id'] ?? null;

if (!$driver_id) {
    echo "<div style='color: red; font-weight: bold;'>ERROR: Please login first.</div>";
    header("Location: driver.php"); // Fixed: Changed from drivers.php to driver.php
    exit;
}

$stmt = $conn->prepare("SELECT * FROM drivers WHERE driver_id = ?");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

if (!$driver) {
    echo "<div style='color: red; font-weight: bold;'>ERROR: No driver found with ID $driver_id.</div>";
    header("Location: driver.php"); // Fixed: Changed from drivers.php to driver.php
    exit;
}

$driverBarangay = $driver['delivery_barangay'] ?? '';
$driverProvince = $driver['delivery_province'] ?? '';

// Calculate dashboard metrics
$totalDeliveries = 0;
$availableBalance = 0;
$pendingWithdrawals = 0;

$deliveryStmt = $conn->prepare("SELECT COUNT(*) as total FROM deliveries WHERE driver_id = ? AND status = 'Delivered'");

if (!$deliveryStmt) {
    die("Prepare failed for delivery count: " . $conn->error);
}

$deliveryStmt->bind_param("i", $driver_id);
$deliveryStmt->execute();
$deliveryResult = $deliveryStmt->get_result();

if ($deliveryRow = $deliveryResult->fetch_assoc()) {
    $totalDeliveries = $deliveryRow['total'];
}

$balanceStmt = $conn->prepare("SELECT SUM(earnings) as balance FROM deliveries WHERE driver_id = ? AND status = 'Delivered'");
$balanceStmt->bind_param("i", $driver_id);
$balanceStmt->execute();
$balanceResult = $balanceStmt->get_result();
if ($balanceRow = $balanceResult->fetch_assoc()) {
    $availableBalance = $balanceRow['balance'] ?? 0;
}

// Handle logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: driver.php"); // Fixed: Changed from drivers.php to driver.php
    exit();
}

// Update delivery and order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_delivered']) && isset($_POST['delivery_id'])) {
    $deliveryID = (int)$_POST['delivery_id'];

    $updateDelivery = $conn->prepare("UPDATE deliveries SET status = 'Delivered' WHERE deliveryID = ?");
    $updateDelivery->bind_param("i", $deliveryID);
    $updateDelivery->execute();

    $updateOrder = $conn->prepare("UPDATE orders SET status = 'Delivered' WHERE order_id = (SELECT order_id FROM deliveries WHERE deliveryID = ?)");
    $updateOrder->bind_param("i", $deliveryID);
    $updateOrder->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Driver Dashboard</title>
  <link rel="stylesheet" href="dashboard.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <div class="container">
    <aside>
      <div class="logo">
        <img src="../5. pictures/driver.png" alt="CartIT x Driver Logo" />
      </div>
      <hr class="logo-divider">
      <p class="menu-title">Main Menu</p>
          <nav>
        <ul class="sidebar-list">
          <li class="active">
            <a href="driver_dashboard.php"><i class='bx bx-layer'></i>Dashboard</a>
          </li>
          <li>
            <a href="deliveries.php"><i class='bx bx-package'></i>Deliveries</a>
          </li>
          <li>
            <a href="../User_Driver_Nav_Withdrawal/withdraw.php"><i class='bx bx-money'></i>Cash Deposit</a>
          </li>
          <li>
            <a href="../User_Driver_Profile/driverprofile.php"><i class='bx bx-user'></i>My Profile</a>
          </li>
        </ul>
      </nav>
      <form method="POST" style="display:inline;">
        <button type="submit" name="logout" class="logout">
          <i class='bx bx-log-out'></i> Log-out
        </button>
      </form>
    </aside>

    <main>
      <header class="user-info-container">
       <div class="user-info-top">
          <div class="user-profile">
            <i class='bx bxs-user-circle'></i>
          </div>
          <div class="user-text">
            <span class="username">@<?= htmlspecialchars($username) ?></span>
            <span class="driver-id">Driver ID: <?= htmlspecialchars($driver_id) ?></span>
          </div>
        </div>
        <hr class="user-info-divider">
      </header>

      <section class="top-info">
        <div class="card">
          <div class="card-icon">
            <i class='bx bx-package'></i>
          </div>
          <div class="card-content">
            <p>Total Delivered</p>
            <h2><?= $totalDeliveries; ?></h2>
          </div>
        </div>
        <div class="card">
          <div class="card-icon">
            <i class='bx bx-wallet'></i>
          </div>
          <div class="card-content">
            <p>Available Balance</p>
            <h2>₱<?= number_format($availableBalance, 2); ?></h2>
          </div>
        </div>
        <div class="card">
          <div class="card-icon">
            <i class='bx bx-time-five'></i>
          </div>
          <div class="card-content">
            <p>Pending Withdrawals</p>
            <h2>₱<?= number_format($pendingWithdrawals, 2); ?></h2>
          </div>
        </div>
      </section>

      <section class="accepted-deliveries">
        <div class="section-header">
          <h1>Accepted Deliveries</h1>
          <p class="subtitle">Deliveries you've accepted and are in progress</p>
        </div>

        <div class="table-container">
          <table class="deliveries-table">
            <thead>
              <tr>
                <th></th>
                <th>Order ID</th>
                <th>Buyer Name</th>
                <th>Delivery Address</th>
                <th>Earnings</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
<?php
$acceptedSql = "
  SELECT 
    d.deliveryID, 
    d.order_id, 
    d.earnings, 
    d.status,
    a.full_name AS buyer_name,
    CONCAT(a.street_address, ', ', a.barangay, ', ', a.province) AS address
  FROM deliveries d
  LEFT JOIN buyer_addresses a ON d.address_id = a.address_id
  WHERE d.driver_id = ? 
    AND d.status IN ('accepted', 'shipped', 'on the way', 'Ongoing')
  ORDER BY d.deliveryID DESC
";

$stmt = $conn->prepare($acceptedSql);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
  while ($row = $result->fetch_assoc()):
?>
  <tr>
    <td><input type="checkbox" name="selected[]" value="<?= $row['deliveryID']; ?>"></td>
    <td><span class="order-id">#<?= htmlspecialchars($row['order_id']); ?></span></td>
    <td><?= htmlspecialchars($row['buyer_name'] ?? 'N/A'); ?></td>
    <td class="address-cell"><?= htmlspecialchars($row['address'] ?? 'N/A'); ?></td>
    <td class="earnings">₱<?= number_format($row['earnings'], 2); ?></td>
    <td><span class="status <?= strtolower($row['status']); ?>"><?= ucfirst($row['status']); ?></span></td>
    <td class="action-buttons">
      <?php if (strtolower($row['status']) !== 'delivered'): ?>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="delivery_id" value="<?= $row['deliveryID']; ?>">
          <button type="submit" name="mark_delivered" class="btn-delivered">Mark as Delivered</button>
        </form>
      <?php else: ?>
        <span class="delivered-text">Delivered</span>
      <?php endif; ?>
    </td>
  </tr>
<?php endwhile; else: ?>
  <tr>
    <td colspan="7" class="no-data">
      <div class="no-data-content">
        <i class='bx bx-package'></i>
        <p>No accepted deliveries found.</p>
      </div>
    </td>
  </tr>
<?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <script>
    function refreshPendingDeliveries() {
      location.reload();
    }

    setInterval(refreshPendingDeliveries, 30000);
    window.addEventListener('focus', refreshPendingDeliveries);
  </script>
</body>
</html>