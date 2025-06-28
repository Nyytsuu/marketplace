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

if (isset($_POST['deliveryID']) && isset($_POST['status'])) {
    $deliveryID = intval($_POST['deliveryID']);
    $status = $_POST['status'];
    
    // Validate status
    $validStatuses = ['shipped', 'on the way', 'delivered'];
    if (!in_array($status, $validStatuses)) {
        echo "Error: Invalid status";
        exit();
    }
    
    // Handle file upload if provided
    $uploadPath = null;
    if (isset($_FILES['upload']) && $_FILES['upload']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['upload']['name']);
        $uploadPath = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $fileType = $_FILES['upload']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo "Error: Invalid file type. Only images and PDF files are allowed.";
            exit();
        }
        
        if (!move_uploaded_file($_FILES['upload']['tmp_name'], $uploadPath)) {
            echo "Error: Failed to upload file";
            exit();
        }
    }

    // Update delivery status
    if ($uploadPath) {
        $stmt = $conn->prepare("UPDATE deliveries SET status = ?, proof_file = ? WHERE deliveryID = ?");
        $stmt->bind_param("ssi", $status, $uploadPath, $deliveryID);
    } else {
        $stmt = $conn->prepare("UPDATE deliveries SET status = ? WHERE deliveryID = ?");
        $stmt->bind_param("si", $status, $deliveryID);
    }
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "success";
    } else {
        echo "Error: No rows updated - delivery may not exist or status unchanged";
    }
    
    $stmt->close();
} else {
    echo "Error: Missing required fields";
}

$conn->close();
?>