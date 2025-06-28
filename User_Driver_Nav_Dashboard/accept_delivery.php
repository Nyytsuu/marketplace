<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    echo "Error: Not logged in";
    exit();
}

if (isset($_POST['deliveryID']) && isset($_POST['driverID'])) {
    $deliveryID = intval($_POST['deliveryID']);
    $driverID = intval($_POST['driverID']);

    // Start transaction to prevent race conditions
    $conn->autocommit(FALSE);

    try {
        // Lock the row for update and check current status
        $checkStmt = $conn->prepare("SELECT deliveryID, status, DriverID FROM deliveries WHERE deliveryID = ? FOR UPDATE");
        $checkStmt->bind_param("i", $deliveryID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            throw new Exception("Delivery not found");
        }
        
        $delivery = $checkResult->fetch_assoc();
        $checkStmt->close();
        
        // Check if delivery is still available
        if ($delivery['DriverID'] != 0 && $delivery['DriverID'] != NULL) {
            throw new Exception("Delivery already assigned to another driver");
        }
        
        if (strtolower($delivery['status']) !== 'pending') {
            throw new Exception("Delivery is no longer pending");
        }

        // Update delivery status and assign driver
        $updateStmt = $conn->prepare("UPDATE deliveries SET status = 'accepted', DriverID = ? WHERE deliveryID = ? AND (DriverID IS NULL OR DriverID = 0) AND status = 'pending'");
        $updateStmt->bind_param("ii", $driverID, $deliveryID);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Database update failed");
        }
        
        if ($updateStmt->affected_rows === 0) {
            throw new Exception("Delivery was just accepted by another driver");
        }
        
        $updateStmt->close();
        
        // Commit transaction
        $conn->commit();
        echo "success";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
    
    $conn->autocommit(TRUE);
} else {
    echo "Error: Missing delivery ID or driver ID";
}

$conn->close();
?>
