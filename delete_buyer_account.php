<?php
session_start();
include 'db_connection.php'; // Make sure $pdo (PDO connection) is available

// Check if the user is logged in
if (!isset($_SESSION['AccountID'])) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Access Denied',
            text: 'Please log in first.'
        }).then(() => window.location.href = 'finalslogin.php');
    </script>";
    exit;
}

$buyer_id = $_SESSION['AccountID'];

try {
    $stmt = $pdo->prepare("DELETE FROM buyers WHERE AccountID = ?");
    $success = $stmt->execute([$buyer_id]);

    if ($success) {
        // Clear session
        session_unset();
        session_destroy();
header("Location: finalslogin.php?deleted=1");
exit;
    } else {
        throw new Exception('Failed to delete your account. Please try again later.');
    }
} catch (Exception $e) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '".addslashes($e->getMessage())."'
        }).then(() => window.history.back());
    </script>";
}
?>
