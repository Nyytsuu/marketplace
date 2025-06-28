<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: ../driver.php");
    exit();
}

$Username = $_SESSION['Username'];

// Get driver profile info
$query = "SELECT * FROM drivers WHERE Username = ?";
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    die("Driver Profile Query Failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "s", $Username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$driver = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Total deliveries and earnings
$deliveryQuery = "SELECT COUNT(*) AS total_deliveries, SUM(earnings) AS total_earned FROM deliveries WHERE driver_id = ?";
$deliveryStmt = mysqli_prepare($conn, $deliveryQuery);
if (!$deliveryStmt) {
    die("Delivery Query Failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($deliveryStmt, "i", $driver['driver_id']);
mysqli_stmt_execute($deliveryStmt);
$deliveryResult = mysqli_stmt_get_result($deliveryStmt);
$deliveryData = mysqli_fetch_assoc($deliveryResult);
$totalDeliveries = $deliveryData['total_deliveries'] ?? 0;
$totalEarned = $deliveryData['total_earned'] ?? 0.00;
mysqli_stmt_close($deliveryStmt);

// Pending withdrawals
$withdrawalQuery = "SELECT SUM(amount) AS pending_withdrawals FROM withdrawals WHERE Username = ? AND status = 'pending'";
$withdrawalStmt = mysqli_prepare($conn, $withdrawalQuery);
if (!$withdrawalStmt) {
    die("Withdrawal Query Failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($withdrawalStmt, "s", $Username);
mysqli_stmt_execute($withdrawalStmt);
$withdrawalResult = mysqli_stmt_get_result($withdrawalStmt);
$withdrawalData = mysqli_fetch_assoc($withdrawalResult);
$pendingWithdrawals = $withdrawalData['pending_withdrawals'] ?? 0.00;
mysqli_stmt_close($withdrawalStmt);

// Compute available balance
$availableBalance = $totalEarned - $pendingWithdrawals;

$transactions = [];

// Fetch deliveries transactions
$deliveryQuery = "SELECT earnings AS amount, 'delivery fee' AS type, 'completed' AS status, delivery_date AS date FROM deliveries WHERE driver_id = ?";
$deliveryStmt = mysqli_prepare($conn, $deliveryQuery);
if (!$deliveryStmt) {
    die("Delivery Transactions Query Failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($deliveryStmt, "i", $driver['driver_id']);
mysqli_stmt_execute($deliveryStmt);
$deliveryResult = mysqli_stmt_get_result($deliveryStmt);

while ($row = mysqli_fetch_assoc($deliveryResult)) {
    $transactions[] = [
        'date' => $row['date'],
        'type' => $row['type'],
        'amount' => '+ ₱' . number_format($row['amount'], 2),
        'status' => ucfirst($row['status'])
    ];
}
mysqli_stmt_close($deliveryStmt);

// Fetch withdrawal transactions
$withdrawQuery = "SELECT amount, status, request_date FROM withdrawals WHERE Username = ?";
$withdrawStmt = mysqli_prepare($conn, $withdrawQuery);
if (!$withdrawStmt) {
    die("Withdrawal Transactions Query Failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($withdrawStmt, "s", $Username);
mysqli_stmt_execute($withdrawStmt);
$withdrawResult = mysqli_stmt_get_result($withdrawStmt);

while ($row = mysqli_fetch_assoc($withdrawResult)) {
    $transactions[] = [
        'date' => $row['request_date'],
        'type' => 'withdrawal',
        'amount' => '- ₱' . number_format($row['amount'], 2),
        'status' => ucfirst($row['status'])
    ];
}
mysqli_stmt_close($withdrawStmt);

// Sort transactions by date descending
usort($transactions, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Earnings</title>
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../User_Driver_Nav_Withdrawal/withdrawal.css"/>
</head>
<body>
  <aside>
    <div class="logo">
      <img src="../5. pictures/driver.png" alt="CartIT x Driver Logo" />
    </div>
    <hr class="logo-divider">
    <p class="menu-title">Main Menu</p>

          <nav>
            <ul class="sidebar-list">
        <li>
              <a href="../driver_dashboard.php"><i class='bx bx-layer'></i>Dashboard</a>
            </li>
            <li>
              <a href="../deliveries.php"><i class='bx bx-package'></i>Deliveries</a>
            </li>
            <li class="active">
              <a href="../User_Driver_Nav_Withdrawal/withdraw.php"><i class='bx bx-money'></i>Cash Deposit</a>
            </li>
            <li>
              <a href="../driverprofile.php"><i class='bx bx-user'></i>My Profile</a>
            </li>
            </ul>
          </nav>

<form method="POST" style="display:inline;">
  <button type="submit" name="logout" class="logout">
    <i class='bx bx-log-out'></i> Log-out
  </button>
</form>
  </aside>

  <?php
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../driver.php"); 
    exit();
}
?>
    <main>
      <header class="user-info-container">
        <div class="user-info-top">
          <div class="user-profile">
            <i class='bx bxs-user-circle'></i>
          </div>
          <div class="user-text">
<p>@<?php echo htmlspecialchars($driver['Username']); ?></p>
<p><?php echo htmlspecialchars($driver['EmailAdd']); ?></p>
  </div>
          </div>
        </div>
        <hr class="user-info-divider">
      </header>

    <section class="content">
      <h1 style="color: white;">Manage Your Earnings</h1>
      <p style="color: white; margin-top:15px;" class="subtitle">Track your earnings and easily deposit or withdraw funds. Stay updated on your balance, and cash out when you're ready.</p>

<div class="summary-cards">
  <div class="card">
    <p>Total Delivered</p>
    <h2><?php echo $totalDeliveries; ?></h2>
  </div>
  <div class="card">
    <p>Pending Withdrawals</p>
    <h2>₱<?php echo number_format($pendingWithdrawals, 2); ?></h2>
  </div>
  <div class="card">
    <p>Available Balance</p>
    <h2>₱<?php echo number_format($availableBalance, 2); ?></h2>
  </div>
</div>


      <div class="transactions">
        <div class="table-section">
          <div class="table-header">
            <h3>All Transaction</h3>
<a href="../User_Driver_Nav_Withdrawal/request.php" class="btn">+ Request Withdrawal</a>
          </div>
          <table>
            <thead>
              <tr>
                <th>Date</th><th>Type</th><th>Amount</th><th>Status</th>
              </tr>
            </thead>
<tbody>
<?php if (empty($transactions)): ?>
    <tr><td colspan="4">No transactions yet.</td></tr>
<?php else: ?>
    <?php foreach ($transactions as $txn): ?>
        <tr>
            <td><?php echo date("M d, Y", strtotime($txn['date'])); ?></td>
            <td><?php echo htmlspecialchars($txn['type']); ?></td>
            <td><?php echo htmlspecialchars($txn['amount']); ?></td>
            <td class="status <?php echo strtolower($txn['status']); ?>"><?php echo htmlspecialchars($txn['status']); ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>

          </table>
        </div>

        <div class="faq">
          <h3>❓ FAQ</h3>
          <p><strong>Q1: How to withdraw:</strong><br>
          Withdrawals are processed within 24 hours. Check your available balance to see if you're eligible for a payout.</p>
          <p><strong>Q2: How can I check if I am eligible for a withdrawal?</strong><br>
          You can check your available balance in the dashboard. If your earnings meet the minimum withdrawal requirements, you will be eligible to request a payout.</p>
        </div>
      </div>
    </section>
  </main>
      </div>
      
</body>
</html>

