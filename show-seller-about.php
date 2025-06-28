<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    return;
}

$sql = "SELECT shop_about FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$aboutText = '';

if ($row = $result->fetch_assoc()) {
    $aboutText = $row['shop_about'] ?? '';
}

$conn->close();

// Echo the about text directly
echo nl2br(htmlspecialchars($aboutText));
?>