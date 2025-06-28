<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    header('Location: signin.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get seller balance
$balanceQuery = "SELECT * FROM seller_balance WHERE seller_id = ?";
$balanceStmt = $conn->prepare($balanceQuery);
$balanceStmt->bind_param("i", $seller_id);
$balanceStmt->execute();
$balanceResult = $balanceStmt->get_result();
$balance = $balanceResult->fetch_assoc();

if (!$balance) {
    // Create initial balance record
    $insertBalance = "INSERT INTO seller_balance (seller_id) VALUES (?)";
    $insertStmt = $conn->prepare($insertBalance);
    $insertStmt->bind_param("i", $seller_id);
    $insertStmt->execute();
    $insertStmt->close();
    
    $balance = [
        'available_balance' => 0.00,
        'pending_balance' => 0.00,
        'total_earned' => 0.00,
        'total_withdrawn' => 0.00
    ];
}

$balanceStmt->close();

// Get recent earnings
$earningsQuery = "
    SELECT se.*, p.product_name, o.order_date
    FROM seller_earnings se
    INNER JOIN products p ON se.product_id = p.product_id
    INNER JOIN orders o ON se.order_id = o.order_id
    WHERE se.seller_id = ?
    ORDER BY se.earned_date DESC
    LIMIT 10
";

$earningsStmt = $conn->prepare($earningsQuery);
$earningsStmt->bind_param("i", $seller_id);
$earningsStmt->execute();
$earningsResult = $earningsStmt->get_result();
$recent_earnings = $earningsResult->fetch_all(MYSQLI_ASSOC);
$earningsStmt->close();

// Get payout history
$payoutQuery = "SELECT * FROM payout_requests WHERE seller_id = ? ORDER BY request_date DESC LIMIT 10";
$payoutStmt = $conn->prepare($payoutQuery);
if (!$payoutStmt) {
    die("SQL error: " . $conn->error);
}
$payoutStmt->bind_param("i", $seller_id);
$payoutStmt->execute();
$payoutResult = $payoutStmt->get_result();
$payout_history = $payoutResult->fetch_all(MYSQLI_ASSOC);
$payoutStmt->close();


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CartIT Seller Dashboard - Request Withdrawal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;1,500&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" />
  <link rel="stylesheet" href="seller-payout.css">
  <style>
    .balance-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .balance-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .balance-card.available {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .balance-card.pending {
      background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
      color: #333;
    }
    
    .balance-card h3 {
      margin: 0 0 10px 0;
      font-size: 0.9rem;
      opacity: 0.9;
    }
    
    .balance-card .amount {
      font-size: 2rem;
      font-weight: 700;
      margin: 0;
    }
    
    .history-section {
      margin-top: 40px;
    }
    
    .history-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }
    
    .history-tab {
      padding: 10px 20px;
      background: #f8f9fa;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .history-tab.active {
      background: #667eea;
      color: white;
    }
    
    .history-content {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .history-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }
    
    .history-item:last-child {
      border-bottom: none;
    }
    
    .history-details h4 {
      margin: 0 0 5px 0;
      color: #333;
    }
    
    .history-details p {
      margin: 0;
      color: #666;
      font-size: 0.9rem;
    }
    
    .history-amount {
      font-weight: 600;
      font-size: 1.1rem;
    }
    
    .status-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      margin-left: 10px;
    }
    
    .status-available { background: #d1fae5; color: #065f46; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-completed { background: #dbeafe; color: #1e40af; }
    .status-processing { background: #e0e7ff; color: #5b21b6; }
  </style>
</head>
<body>

<div class="container"> 
  <div class="grid-container">   
    <div class="header-container">
      <form action="#" method="get" class="search-bar">
        <input type="text" name="search" placeholder="Search for products">
        <i class="bx bx-search"></i>    
      </form>

      <div class="icons">
        <button type="button" class="profile-btn" onclick="window.location.href='sellerprofilenew.php'">
          <i class="bx bx-user"></i>
        </button>
        <span class="icon-separator">|</span>
        <button type="button" class="edit-dp-btn" onclick="window.location.href='editprofile.php'">
          <i class="bx bx-cog"></i>
        </button>
      </div>

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

    <main class="main-container">
      <!-- Withdrawal Form -->
      <div class="withdrawal-box">
        <h2>Request Withdrawal</h2>
        <p>Choose your preferred payout method: Bank Account, GCash, or PayPal. Enter the amount you'd like to withdraw,
           provide the necessary payment details, and submit your request. Withdrawals will be processed within 1–2 business days.</p>
        
        <form class="withdrawal-form" method="POST" action="submit_payout_request.php">
          <input type="number" name="amount" placeholder="Enter amount (Max: ₱<?= number_format($balance['available_balance'], 2) ?>)" 
                 max="<?= $balance['available_balance'] ?>" step="0.01" required>
          
          <select name="payment_method" required>
            <option value="">Select Payment Method</option>
            <option value="gcash">GCash</option>
            <option value="bank">Bank Transfer</option>
            <option value="paypal">PayPal</option>
          </select>
          
          <input type="text" name="account_number" placeholder="Account Number / Phone Number" required>
          <input type="text" name="account_name" placeholder="Account Name" required>

          <label class="checkbox-label">
            <input type="checkbox" required>
            By checking, you agree to CartIT <a href="#">Terms of Condition</a>
          </label>

          <button type="submit" id="request-withdraw-btn" class="withdraw-btn">Request Withdrawal</button>
        </form>
      </div>

        <div class="history-content" id="payouts-history" style="display: none;">
          <h3>Payout History</h3>
          <?php if (empty($payout_history)): ?>
            <p>No payout requests yet.</p>
          <?php else: ?>
            <?php foreach ($payout_history as $payout): ?>
              <div class="history-item">
                <div class="history-details">
                  <h4><?= ucfirst($payout['payment_method']) ?> Withdrawal</h4>
                  <p>Requested: <?= date('M j, Y', strtotime($payout['request_date'])) ?></p>
                  <?php if ($payout['payout_date']): ?>
                    <p>Completed: <?= date('M j, Y', strtotime($payout['payout_date'])) ?></p>
                  <?php endif; ?>
                </div>
                <div>
                  <span class="history-amount">₱<?= number_format($payout['amount'], 2) ?></span>
                  <span class="status-badge status-<?= $payout['status'] ?>"><?= ucfirst($payout['status']) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</div>

<script>
function showHistory(type) {
  // Hide all history content
  document.getElementById('earnings-history').style.display = 'none';
  document.getElementById('payouts-history').style.display = 'none';
  
  // Remove active class from all tabs
  document.querySelectorAll('.history-tab').forEach(tab => {
    tab.classList.remove('active');
  });
  
  // Show selected content and activate tab
  document.getElementById(type + '-history').style.display = 'block';
  event.target.classList.add('active');
}

document.getElementById('request-withdraw-btn').addEventListener('click', function (e) {
  e.preventDefault();
  
  const form = document.querySelector('.withdrawal-form');
  const formData = new FormData(form);
  const amount = parseFloat(formData.get('amount'));
  const maxAmount = <?= $balance['available_balance'] ?>;
  
  if (amount > maxAmount) {
    Swal.fire({
      title: 'Invalid Amount',
      text: `You can only withdraw up to ₱${maxAmount.toFixed(2)}`,
      icon: 'error',
      confirmButtonText: 'OK'
    });
    return;
  }
  
  if (amount < 100) {
    Swal.fire({
      title: 'Minimum Amount',
      text: 'Minimum withdrawal amount is ₱100.00',
      icon: 'error',
      confirmButtonText: 'OK'
    });
    return;
  }

  // Submit the form
  fetch('submit_payout_request.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        title: '✔ Your request has been sent',
        text: 'Withdrawals are typically processed within 1–2 business days. It may take longer depending on the payment provider or additional verification.',
        icon: 'success',
        confirmButtonText: 'Go to Dashboard',
        cancelButtonText: 'View History',
        showCancelButton: true,
        showClass: {
          popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
          popup: 'animate__animated animate__fadeOutUp'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'sellerdashboard.php';
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          showHistory('payouts');
        }
      });
    } else {
      Swal.fire({
        title: 'Error',
        text: data.message || 'Failed to submit withdrawal request',
        icon: 'error',
        confirmButtonText: 'OK'
      });
    }
  })
  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      title: 'Error',
      text: 'Network error. Please try again.',
      icon: 'error',
      confirmButtonText: 'OK'
    });
  });
});

function toggleDropdown() {
  const dropdown = document.getElementById('dropdownMenu');
  dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}
</script>

</body>
</html>