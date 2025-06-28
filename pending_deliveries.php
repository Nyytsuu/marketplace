<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // your password
$database = "marketplace";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pending Deliveries</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table th, table td {
      padding: 10px;
      border: 1px solid #ddd;
    }
    .accept, .details {
      margin-right: 5px;
      padding: 5px 10px;
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: #fff;
      padding: 20px;
      max-width: 400px;
      border-radius: 8px;
      animation-duration: 0.5s;
    }
    .button-group button {
      margin: 10px 5px 0 0;
    }
  </style>
</head>
<body>

<section class="pending">
  <h3>Pending Deliveries</h3>
  <table>
    <thead>
      <tr>
        <th>Delivery ID</th>
        <th>Order ID</th>
        <th>Customer Name</th>
        <th>Delivery Address</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $pendingQuery = "SELECT * FROM deliveries WHERE status = 'Pending' ORDER BY deliveryID DESC";
      $pendingResult = $conn->query($pendingQuery);

      if (!$pendingResult) {
          echo "<tr><td colspan='5'>Query failed: " . $conn->error . "</td></tr>";
      } elseif ($pendingResult->num_rows > 0) {
          while ($row = $pendingResult->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($row['deliveryID']) . "</td>";
              echo "<td>" . htmlspecialchars($row['order_id']) . "</td>";
              echo "<td>" . htmlspecialchars($row['buyer_name']) . "</td>";
              echo "<td>" . htmlspecialchars($row['address']) . "</td>";
              echo "<td>
                      <button class='accept' data-id='" . $row['deliveryID'] . "'>✔ Accept</button>
                      <button class='details' 
                              data-orderid='" . $row['order_id'] . "' 
                              data-name='" . $row['buyer_name'] . "' 
                              data-address='" . $row['address'] . "'>
                        📄 Details</button>
                    </td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='5' style='text-align:center;'>No pending deliveries.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</section>

<!-- Modal -->
<div class="modal" id="popupModal">
  <div class="modal-content animate__animated">
    <span class="close-btn" style="float:right; cursor:pointer;">&times;</span>
    <div id="modal-body"></div>
  </div>
</div>

<script>
const modal = document.getElementById("popupModal");
const modalContent = modal.querySelector(".modal-content");
const modalBody = document.getElementById("modal-body");

// Show Modal
function showModal(contentHTML) {
  modalBody.innerHTML = contentHTML;
  modal.style.display = "flex";
  modalContent.classList.remove("animate__fadeOutUp");
  modalContent.classList.add("animate__fadeInDown");
  setTimeout(() => bindDynamicButtons(), 50);
}

// Hide Modal
function hideModal() {
  modalContent.classList.remove("animate__fadeInDown");
  modalContent.classList.add("animate__fadeOutUp");
  setTimeout(() => {
    modal.style.display = "none";
    modalContent.classList.remove("animate__fadeOutUp");
  }, 500);
}

// Accept Button Modal
document.querySelectorAll(".accept").forEach(button => {
  button.addEventListener("click", () => {
    const deliveryID = button.getAttribute("data-id");

    showModal(`
      <h2>Confirm Acceptance</h2>
      <p>Are you sure you want to accept delivery #${deliveryID}?</p>
      <div class="button-group">
        <form method="post" style="display:inline;">
          <input type="hidden" name="accept_id" value="${deliveryID}">
          <button type="submit" class="confirm-btn">Yes, Accept</button>
        </form>
        <button class="cancel-btn">Cancel</button>
      </div>
    `);
  });
});

// Details Button Modal
document.querySelectorAll(".details").forEach(button => {
  button.addEventListener("click", () => {
    const orderId = button.getAttribute("data-orderid");
    const name = button.getAttribute("data-name");
    const address = button.getAttribute("data-address");

    showModal(`
      <h2>Order Details</h2>
      <p><strong>Order ID:</strong> ${orderId}</p>
      <p><strong>Customer:</strong> ${name}</p>
      <p><strong>Address:</strong> ${address}</p>
      <div class="button-group">
        <button class="cancel-btn">Close</button>
      </div>
    `);
  });
});

// Cancel Buttons
function bindDynamicButtons() {
  document.querySelectorAll(".cancel-btn").forEach(btn => {
    btn.addEventListener("click", () => hideModal());
  });
}

document.querySelector(".close-btn").onclick = () => hideModal();
window.onclick = (e) => {
  if (e.target === modal) hideModal();
};
</script>

<?php
// Handle Accept form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_id'])) {
    $acceptID = (int)$_POST['accept_id'];
    $updateQuery = "UPDATE deliveries SET status = 'Delivered' WHERE deliveryID = $acceptID";
    if ($conn->query($updateQuery)) {
        echo "<script>
            Swal.fire({
              icon: 'success',
              title: 'Accepted!',
              text: 'Delivery #$acceptID marked as Delivered.',
              showConfirmButton: false,
              timer: 2000
            }).then(() => window.location.reload());
        </script>";
    } else {
        echo "<script>
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Failed to update status.',
            });
        </script>";
    }
}
?>

</body>
</html>
