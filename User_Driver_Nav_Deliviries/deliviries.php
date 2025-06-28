<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$driverID = $_SESSION['driver_id'] ?? 0;
$username = $_SESSION['Username'] ?? 'Guest'; // ✅ fixed from full_name to Username

// Get driver's area
$areaQuery = "SELECT delivery_barangay, delivery_city, delivery_province FROM drivers WHERE driver_id = ?";
$stmt = $conn->prepare($areaQuery);
$stmt->bind_param("i", $driverID);
$stmt->execute();
$areaResult = $stmt->get_result();
$driverArea = $areaResult->fetch_assoc();

// Handle area submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['set_area'])) {
    $barangay = $_POST['barangay'] ?? '';
    $city = $_POST['city'] ?? '';
    $province = $_POST['province'] ?? '';

    // Validate inputs (simple)
    if ($barangay && $city && $province) {
        $updateStmt = $conn->prepare("UPDATE drivers SET delivery_barangay = ?, delivery_city = ?, delivery_province = ? WHERE driver_id = ?");
        $updateStmt->bind_param("sssi", $barangay, $city, $province, $driverID);
        $updateStmt->execute();
        $updateStmt->close();

        // Redirect after update to refresh delivery list with updated area
        header("Location: deliveries.php");
        exit;
    } else {
        $error = "Please select barangay, city, and province.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Delivery Dashboard</title>
  <link rel="stylesheet" href="deliviries.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* Minimal modal styling */
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); }
    .modal-content { background:#fff; padding: 2rem; max-width: 500px; margin: 5% auto; border-radius: 10px; position: relative; }
  </style>
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
        <li>
              <a href="../driver_dashboard.php"><i class='bx bx-layer'></i>Dashboard</a>
            </li>
            <li class="active">
              <a href="../deliveries.php"><i class='bx bx-package'></i>Deliveries</a>
            </li>
            <li>
              <a href="withdraw.php"><i class='bx bx-money'></i>Cash Deposit</a>
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

    <main>
      <header class="user-info-container">
            <div class="user-info-top">
              <div class="user-profile">
                <i class='bx bxs-user-circle'></i>
              </div>
              <div class="user-text">
                <span class="username">@<?= htmlspecialchars($username) ?></span>
    
              </div>
            </div>
            <hr class="user-info-divider">
          </header>
  <!-- Sidebar & Header HTML -->
  <!-- ... your sidebar/header here ... -->

  <main>
    <?php if (empty($driverArea['delivery_barangay']) || empty($driverArea['delivery_city']) || empty($driverArea['delivery_province'])): ?>
      <!-- Show area form when area not set -->
      <h2>Please set your delivery area</h2>
      <?php if (!empty($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" id="areaForm">
        <input type="hidden" name="set_area" value="1" />

        <label for="province">Province:</label><br />
        <select id="province" name="province" required>
          <option value="">Select Province</option>
        </select><br /><br />

        <label for="city">City:</label><br />
        <select id="city" name="city" required disabled>
          <option value="">Select City</option>
        </select><br /><br />

        <label for="barangay">Barangay:</label><br />
        <select id="barangay" name="barangay" required disabled>
          <option value="">Select Barangay</option>
        </select><br /><br />

        <button type="submit">Save Area</button>
      </form>
    <?php else: ?>
      <!-- Show deliveries for driver area -->
      <h1>Delivery Orders</h1>
      <p class="subtitle">Orders in your area: <?= htmlspecialchars($driverArea['delivery_barangay']) ?>, <?= htmlspecialchars($driverArea['delivery_city']) ?>, <?= htmlspecialchars($driverArea['delivery_province']) ?></p>

      <table class="deliveries-table" border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse;">
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
$driverProvince = $driverArea['delivery_province'];
$driverBarangay = $driverArea['delivery_barangay'];

$availableSql = "
  SELECT 
    o.order_id, 
    ba.full_name AS buyer_name,
    CONCAT(ba.street_address, ', ', ba.barangay, ', ', ba.province) AS address,
    o.total_price,
    o.payment_method
  FROM orders o
  JOIN buyer_addresses ba ON o.delivery_address_id = ba.address_id
  LEFT JOIN deliveries d ON o.order_id = d.order_id
  WHERE 
    d.order_id IS NULL AND
    ba.province = ? AND 
    ba.barangay = ?
  ORDER BY o.order_id DESC
";

$stmt = $conn->prepare($availableSql);
$stmt->bind_param("ss", $driverProvince, $driverBarangay);

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
  while ($row = $result->fetch_assoc()):
?>
  <tr>
    <td><input type="checkbox" name="selected[]" value="<?= $row['order_id']; ?>"></td>
    <td><?= htmlspecialchars($row['order_id']); ?></td>
    <td><?= htmlspecialchars($row['buyer_name']); ?></td>
    <td><?= htmlspecialchars($row['address']); ?></td>
    <td>₱<?= number_format($row['total_price'], 2); ?></td>
    <td><span class="status pending">Pending</span></td>
    <td class="action-buttons">
      <button class="accept" data-orderid="<?= $row['order_id']; ?>">✔ Accept</button>
    </td>
  </tr>
<?php endwhile; else: ?>
  <tr>
    <td colspan="7" style="text-align:center;">No available orders found in your area.</td>
  </tr>
<?php endif; ?>

        </tbody>
      </table>

      <button id="changeAreaBtn" class="change-area-btn">Change Delivery Area</button>

      <!-- Area Modal -->
      <div id="areaModal" class="modal">
        <div class="modal-content">
          <h2>Update Delivery Area</h2>
          <form id="areaFormModal" method="POST">
            <input type="hidden" name="set_area" value="1" />

            <label for="provinceModal">Province:</label><br />
            <select id="provinceModal" name="province" required>
              <option value="">Select Province</option>
            </select><br /><br />

            <label for="cityModal">City:</label><br />
            <select id="cityModal" name="city" required disabled>
              <option value="">Select City</option>
            </select><br /><br />

            <label for="barangayModal">Barangay:</label><br />
            <select id="barangayModal" name="barangay" required disabled>
              <option value="">Select Barangay</option>
            </select><br /><br />

            <div style="display:flex; justify-content: space-between;">
              <button type="button" id="cancelModal" style="background:#ccc;">Cancel</button>
              <button type="submit">Save Area</button>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  // Function to load locations for given select elements
  function setupLocationSelectors(provinceSelect, citySelect, barangaySelect) {
    const baseURL = "http://localhost/Online%20Marketing%20System/location_api.php";

    // Load provinces
    fetch(`${baseURL}?type=province`)
      .then(res => res.json())
      .then(provinces => {
        provinces.forEach(province => {
          const opt = document.createElement("option");
          opt.value = province;
          opt.textContent = province;
          provinceSelect.appendChild(opt);
        });
        provinceSelect.disabled = false;
      });

    // Province change event
    provinceSelect.addEventListener("change", () => {
      const province = provinceSelect.value;
      citySelect.innerHTML = '<option value="">Select City</option>';
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      citySelect.disabled = true;
      barangaySelect.disabled = true;

      if (!province) return;

      fetch(`${baseURL}?type=city&province=${encodeURIComponent(province)}`)
        .then(res => res.json())
        .then(cities => {
          cities.forEach(city => {
            const opt = document.createElement("option");
            opt.value = city;
            opt.textContent = city;
            citySelect.appendChild(opt);
          });
          citySelect.disabled = false;
        });
    });

    // City change event
    citySelect.addEventListener("change", () => {
      const province = provinceSelect.value;
      const city = citySelect.value;

      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      barangaySelect.disabled = true;

      if (province && city) {
        console.log('Fetching barangays for', province, city);

        fetch(`${baseURL}?type=barangay&province=${encodeURIComponent(province)}&city=${encodeURIComponent(city)}`)
          .then(res => res.json())
          .then(barangays => {
            barangays.forEach(barangay => {
              const opt = document.createElement("option");
              opt.value = barangay;
              opt.textContent = barangay;
              barangaySelect.appendChild(opt);
            });
            barangaySelect.disabled = false;
          });
      }
    });
  }

  <?php if (empty($driverArea['delivery_barangay']) || empty($driverArea['delivery_city']) || empty($driverArea['delivery_province'])): ?>
    // Setup selectors on initial form
    setupLocationSelectors(
      document.getElementById("province"),
      document.getElementById("city"),
      document.getElementById("barangay")
    );
  <?php else: ?>
    // Setup selectors on modal form
    const modal = document.getElementById("areaModal");
    const changeAreaBtn = document.getElementById("changeAreaBtn");
    const cancelModalBtn = document.getElementById("cancelModal");
    const provinceModal = document.getElementById("provinceModal");
    const cityModal = document.getElementById("cityModal");
    const barangayModal = document.getElementById("barangayModal");
    const areaFormModal = document.getElementById("areaFormModal");

    setupLocationSelectors(provinceModal, cityModal, barangayModal);

    // Open modal
    changeAreaBtn.addEventListener("click", () => {
      modal.style.display = "block";

      // Optionally reset selects and disable city/barangay
      provinceModal.value = "";
      cityModal.innerHTML = '<option value="">Select City</option>';
      cityModal.disabled = true;
      barangayModal.innerHTML = '<option value="">Select Barangay</option>';
      barangayModal.disabled = true;
    });

    // Close modal
    cancelModalBtn.addEventListener("click", () => {
      modal.style.display = "none";
    });

    // Optionally, close modal when clicking outside modal-content
    window.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.style.display = "none";
      }
    });

    // You can add SweetAlert2 confirmation on form submit if desired
    areaFormModal.addEventListener("submit", function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Save new delivery area?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          areaFormModal.submit();
        }
      });
    });
  <?php endif; ?>
});
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".accept").forEach(button => {
    button.addEventListener("click", function() {
      const orderID = this.dataset.orderid;

      Swal.fire({
        title: 'Accept this order?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, accept it'
      }).then(result => {
        if (result.isConfirmed) {
          fetch('accept_delivery.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_id=${orderID}`
          })
          .then(res => res.text())
          .then(response => {
            if (response === "success") {
              Swal.fire('Accepted!', 'Order has been assigned to you.', 'success').then(() => {
                location.reload(); // Refresh to remove accepted order
              });
            } else {
              Swal.fire('Oops!', response, 'error');
            }
          });
        }
      });
    });
  });
});
</script>
</body>
</html>