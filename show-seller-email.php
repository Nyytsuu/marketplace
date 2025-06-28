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
$sql = "SELECT email FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

echo "<span><strong></strong>" . htmlspecialchars($row['email']) . "</span><br>";


} else {
    echo "<p>User not found.</p>";
}

$conn->close();
?>

