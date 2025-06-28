<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['deliveryID'], $_POST['newStatus'])) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit;
    }

    $deliveryID = intval($_POST['deliveryID']);
    $newStatus = $_POST['newStatus'];

    $allowedStatuses = ['accepted', 'delivered']; // allow accepted

    if (!in_array($newStatus, $allowedStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    $conn = mysqli_connect("localhost", "root", "", "marketplace");
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE deliveries SET status = ? WHERE deliveryID = ?");
    $stmt->bind_param("si", $newStatus, $deliveryID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update delivery']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
