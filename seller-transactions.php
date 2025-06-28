<?php
session_start();
require 'db_connection.php'; // include your PDO connection file

$seller_id = $_SESSION['seller_id'] ?? 1; // Replace with real session login system

// Total Revenue
$stmt = $pdo->prepare("SELECT SUM(amount) AS total_revenue FROM seller_transactions WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

// Total Payouts
$stmt = $pdo->prepare("SELECT SUM(amount) AS total_payouts FROM seller_payouts WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$total_payouts = $stmt->fetch()['total_payouts'] ?? 0;

// Current Balance
$current_balance = $total_revenue - $total_payouts;

// Recent Payouts
$stmt = $pdo->prepare("SELECT * FROM seller_payouts WHERE seller_id = ? ORDER BY payout_date DESC LIMIT 5");
$stmt->execute([$seller_id]);
$recent_payouts = $stmt->fetchAll();

// Transactions (from seller_transactions table)
$stmt = $pdo->prepare("SELECT created_at, payment_method, amount, payment_status FROM seller_transactions WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->execute([$seller_id]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CartIT Seller Center - Finances</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="seller-transactions1.css" rel="stylesheet">
      <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet" />
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <a href="seller.orders.php" class="menu-item">
                            <i class="bx bx-package"></i> 
                            <span>Orders</span>
                        </a>
                    </li>

                    <li class="sidebar-list-item">
                        <a href="seller-transactions.php" class="menu-item active">
                            <i class="bx bx-credit-card"></i> 
                            <span>Transaction</span>
                        </a>
                    </li>

                </ul>
            </nav>
        </aside>

        
  <div class="main">
  <div class="section-title">Transactions</div>
  <p>Keeps track of all your completed sales, withdrawal requests, and payouts, so you can easily monitor your income and manage your finances.</p>
  <button class="request-btn" onclick="window.location.href='seller-payout.php'">+ Request Withdrawal</button>

  <div class="tabs">
    <div class="tab active">All Transactions</div>
    <div class="tab">Sales</div>
    <div class="tab">Payouts</div>
  </div>

  <div class="transactions-wrapper">
    <div class="transaction-table">
      <!-- ALL TRANSACTIONS -->
      <div class="transaction-content active-tab" id="transaction-all">
        <?php if (empty($transactions)): ?>
          <p class="no-data">No transactions yet.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $t): ?>
                <tr>
                  <td><?= date('M d, Y', strtotime($t['date'])) ?></td>
                  <td><?= htmlspecialchars($t['type']) ?></td>
                  <td>₱<?= number_format($t['amount'], 2) ?></td>
                  <td class="status-<?= strtolower($t['status']) ?>"><?= ucfirst($t['status']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- SALES -->
      <div class="transaction-content" id="transaction-sales">
        <p class="no-data">No sales transactions yet.</p>
      </div>

      <!-- PAYOUTS -->
      <div class="transaction-content" id="transaction-payouts">
        <p class="no-data">No payout transactions yet.</p>
      </div>
    </div>

    <div class="summary-boxes">
      <div class="summary">Current Balance<br><strong>₱<?= number_format($current_balance, 2) ?></strong></div>
      <div class="summary">Total Revenue<br><strong>₱<?= number_format($total_revenue, 2) ?></strong></div>
      <div class="summary">Recent Payouts<br><strong>₱<?= number_format($total_payouts, 2) ?></strong></div>
    </div>
  </div>
</div>

<script>
  const tabs = document.querySelectorAll('.tab');
  const contents = document.querySelectorAll('.transaction-content');

  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      contents.forEach(c => {
        c.classList.remove('active-tab');
        c.style.opacity = 0;
      });

      tab.classList.add('active');
      const selected = contents[index];
      selected.classList.add('active-tab');
      setTimeout(() => selected.style.opacity = 1, 50);
    });
  });
</script>

</body>
</html>

