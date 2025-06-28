<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: ../User_Driver_SignIn/driver.php"); // or wherever your login page is
    exit();
}

$Username = $_SESSION['Username'];
$query = "SELECT * FROM driver_signup WHERE Username = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $Username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $driver = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    die("Database error: " . mysqli_error($conn));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ensure driver is logged in
    if (!isset($_SESSION['Username'])) {
        http_response_code(403);
        echo "Unauthorized";
        exit;
    }

    $driver_username = $_SESSION['Username'];
    $amount = $_POST['amount'];

    // Optional: sanitize inputs
    $amount = floatval($amount);
    $date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO withdrawals (Username, amount, request_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $driver_username, $amount, $date);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Database error";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Earnings</title>
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../User_Driver_Nav_Withdrawal/request_withdrawal.css"/>
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
          <a href="../User_Driver_Nav_Dashboard/nav_dashboard.php"><i class='bx bx-layer'></i>Dashboard</a>
        </li>
        <li>
          <a href="../User_Driver_Nav_Deliviries/deliviries.php"><i class='bx bx-package'></i>Deliveries</a>
        </li>
        <li class="active">
          <a href="../User_Driver_Nav_Withdrawal/withdraw.php"><i class='bx bx-money'></i>Cash Deposit</a>
        </li>
        <li>
          <a href="../User_Driver_Nav_Settings/driverprofile.php"><i class='bx bx-user'></i>My Profile</a>
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
    header("Location: driver.php"); 
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
   <div class="card">
        <h2>Request Withdrawal</h2>
        <p style="font-size: 13px; color: #555;">
            Choose your preferred payout method: Bank Account, GCash, or PayPal. Enter the amount you'd like to withdraw,
            provide the necessary payment details, and submit your request. Withdrawals will be processed within 1–2 business days.
        </p>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Withdrawal Form</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<form id="withdrawForm">
    <div class="form-group">
        <label for="amount">Withdrawal Amount</label>
        <input type="number" id="amount" name="amount" placeholder="enter amount" required />
    </div>
    <div class="form-group">
        <label for="method">Payment Method</label>
        <input type="text" id="method" name="method" placeholder="enter your payment method" required />
    </div>
    <div class="form-group">
        <label for="card_number">Card Number</label>
        <input type="text" id="card_number" name="card_number" placeholder="enter your card number" required />
    </div>
    <div class="form-group">
        <label for="card_name">Card Full Name</label>
        <input type="text" id="card_name" name="card_name" placeholder="enter your card name" required />
    </div>
    <div class="form-check">
        <input type="checkbox" id="agree" name="agree" required />
        <label for="agree">By checking, you agree to CartIT <a href="#" style="color:#0043b3;">Terms of Condition</a></label>
    </div>

    <button type="button" class="submit-btn" onclick="submitForm()">Request Withdrawal</button>
</form>

<script>
    function submitForm() {
        const form = document.getElementById('withdrawForm');
        const formData = new FormData(form);

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Show loading alert
        Swal.fire({
            title: 'Sending Request...',
            text: 'Please wait while we process your withdrawal request.',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('submit_withdrawal.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Close loading alert before showing success alert
            Swal.close();

            // Show success alert with OK and Back buttons
            Swal.fire({
                title: 'Request Sent!',
                text: 'Your withdrawal request has been submitted successfully.',
                icon: 'success',
                confirmButtonText: 'OK',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/User_Driver_Nav_Withdrawal/withdraw.php';
                }
                // else Back pressed: do nothing, alert closes and user stays
            });

            form.reset();
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Something went wrong. Please try again.',
                icon: 'error'
            });
        });
    }
</script>

</body>
</html>