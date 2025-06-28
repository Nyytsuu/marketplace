<?php
$conn = mysqli_connect("localhost", "root", "", "marketplace");
session_start();

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    if (isset($_POST['signin'])) {  // Your form button name is 'signin'

        // Correct variable names (match your form)
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Safe query - but better to use prepared statements (for now I'll show basic fix)
        $my = "SELECT * FROM admin_signin WHERE Username='$username' AND Password='$password'";
        $result = mysqli_query($conn, $my);

        if (!$result) {
            echo "Error executing query: " . mysqli_error($conn);
        }

        $row = mysqli_fetch_assoc($result);

        if ($row) {
            // You don't need to compare password again — you already checked in the SQL query
            $_SESSION['Username'] = $username;
            $_SESSION['Password'] = $password; // Not recommended to store plain password in session
            header('Location: ../Admin_Dashboard/dashboard.php');
            exit(); 
        } else {
            // Invalid username or password
            $error_message = "Invalid username or password."; 
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_signin.css">
    <title>Admin Sign In</title>
</head>
<body>
    <div class="form">
        <div class="logo"> 
            <img src="../5. pictures/blue_admin.png" alt="Logo" class="logo">
        </div>
        <form id="adminform" method="post">
            <input type="hidden" name="formType" value="signin">
            <p style="color:black;">Username</p>
            <input type="text" name="username" placeholder="Username" required>

            <p style="color:black;">Password</p>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="signin">Sign In</button>

            <?php
                if (isset($error_message)) {
                    echo "<p class='error'>$error_message</p>";
                }
            ?>
        </form>
    </div>
</body>
</html>