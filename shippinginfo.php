<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Connect to database
$conn = new mysqli("localhost", "root", "", "marketplace");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "User not logged in.";
    exit;
}

// ✅ Make sure this matches your actual table name
$sql = "SELECT ship_name, ship_street, ship_city, ship_region, ship_province, phone FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

   echo "<div class='shipping-info'>";
   echo "<div class='leftshipping-column'>";
    echo "<div class='nameinfo'><strong>Full name:</strong><br> " . htmlspecialchars($row['ship_name']) . "</div>";
    echo "<div class='nameinfo'><strong>Shipping Address:</strong><br> " . htmlspecialchars($row['ship_street']) . "</div>";
    echo "<div class='nameinfo'><strong>City\Municipality</strong><br> " . htmlspecialchars($row['ship_city']) . "</div>"; 
    echo "</div>";
    echo "<div class='rightshipping-column'>";
    echo "<div class='emailinfo'><strong>Region:</strong><br> " . htmlspecialchars($row['ship_region']) . "</div>";
    echo "<div class='phoneinfo'><strong>Province:</strong><br> " . htmlspecialchars($row['ship_province']) . "</div>";
    echo "<div class='addressinfo'><strong>Contact No:</strong><br> " . htmlspecialchars($row['phone']) . "</div>";
    echo "</div>";

    echo "</div>";


} else {
    echo "<p>User not found.</p>";
}

$conn->close();
?>

