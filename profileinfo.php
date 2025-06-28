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
$sql = "SELECT fullname, email, phone, Address FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

   echo "<div class='profile-info'>";
   echo "<div class='left-column'>";
    echo "<div class='nameinfo'><strong>Shop Name:</strong><br> " . htmlspecialchars($row['fullname']) . "</div>"; 
    echo "</div>";
    echo "<div class='right-column'>";
    echo "<div class='emailinfo'><strong>Email:</strong><br> " . htmlspecialchars($row['email']) . "</div>";
    echo "<div class='phoneinfo'><strong>Phone:</strong><br> " . htmlspecialchars($row['phone']) . "</div>";
    echo "<div class='addressinfo'><strong>Location:</strong><br> " . htmlspecialchars($row['Address']) . "</div>";
    echo "</div>";

    echo "</div>";


} else {
    echo "<p>User not found.</p>";
}

$conn->close();
?>

