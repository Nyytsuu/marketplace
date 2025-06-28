<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace"); // Fixed database name

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['Username'])) {
    die("Unauthorized access");
}

// Check if delete request is made
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $Username = $_SESSION['full_name'] ?? $_SESSION['Username'];
    
    try {
        // Start transaction
        mysqli_autocommit($conn, FALSE);
        
        // Get driver ID first
        $getDriverQuery = "SELECT driver_id FROM drivers WHERE Username = ?";
        $getDriverStmt = mysqli_prepare($conn, $getDriverQuery);
        mysqli_stmt_bind_param($getDriverStmt, "s", $Username);
        mysqli_stmt_execute($getDriverStmt);
        $result = mysqli_stmt_get_result($getDriverStmt);
        $driver = mysqli_fetch_assoc($result);
        mysqli_stmt_close($getDriverStmt);
        
        if (!$driver) {
            throw new Exception("Driver not found");
        }
        
        $driver_id = $driver['driver_id'];
        
        // Delete related records first (to maintain referential integrity)
        
        // Delete deliveries
        $deleteDeliveriesQuery = "DELETE FROM deliveries WHERE driver_id = ?";
        $deleteDeliveriesStmt = mysqli_prepare($conn, $deleteDeliveriesQuery);
        mysqli_stmt_bind_param($deleteDeliveriesStmt, "i", $driver_id);
        mysqli_stmt_execute($deleteDeliveriesStmt);
        mysqli_stmt_close($deleteDeliveriesStmt);
        
        // Delete withdrawals (if exists)
        $deleteWithdrawalsQuery = "DELETE FROM withdrawals WHERE driver_id = ?";
        $deleteWithdrawalsStmt = mysqli_prepare($conn, $deleteWithdrawalsQuery);
        if ($deleteWithdrawalsStmt) {
            mysqli_stmt_bind_param($deleteWithdrawalsStmt, "i", $driver_id);
            mysqli_stmt_execute($deleteWithdrawalsStmt);
            mysqli_stmt_close($deleteWithdrawalsStmt);
        }
        
        // Delete driver earnings (if exists)
        $deleteEarningsQuery = "DELETE FROM driver_earnings WHERE driver_id = ?";
        $deleteEarningsStmt = mysqli_prepare($conn, $deleteEarningsQuery);
        if ($deleteEarningsStmt) {
            mysqli_stmt_bind_param($deleteEarningsStmt, "i", $driver_id);
            mysqli_stmt_execute($deleteEarningsStmt);
            mysqli_stmt_close($deleteEarningsStmt);
        }
        
        // Finally, delete the driver account
        $deleteDriverQuery = "DELETE FROM drivers WHERE driver_id = ?";
        $deleteDriverStmt = mysqli_prepare($conn, $deleteDriverQuery);
        mysqli_stmt_bind_param($deleteDriverStmt, "i", $driver_id);
        
        if (mysqli_stmt_execute($deleteDriverStmt)) {
            mysqli_stmt_close($deleteDriverStmt);
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Destroy session
            session_unset();
            session_destroy();
            
            echo "success";
        } else {
            throw new Exception("Failed to delete driver account");
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
    
    // Restore autocommit
    mysqli_autocommit($conn, TRUE);
} else {
    echo "Invalid request";
}

mysqli_close($conn);
?>