<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: driver.php");
    exit();
}

// Set the username before using it in query
$Username = $_SESSION['full_name'] ?? $_SESSION['Username'];

// Fetch the driver record from the database
$query = "SELECT * FROM drivers WHERE Username = ?";
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    die("Driver Profile Query Failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "s", $Username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$driver = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// If no driver found, handle it
if (!$driver) {
    die("No driver record found for username: " . htmlspecialchars($Username));
}

// Handle logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: driver.php"); 
    exit();
}

// Handle password change
if (isset($_POST['change_pass'])) {
    $currentPassword = $_POST['current-password'] ?? '';
    $newPassword     = $_POST['new-password']     ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';
    
    // 1) All fields required
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        header("Location: driverprofile.php?error=empty");
        exit();
    }
    // 2) New vs confirm match
    if ($newPassword !== $confirmPassword) {
        header("Location: driverprofile.php?error=mismatch");
        exit();
    }
    // 3) Plain‑text compare (no password_verify)
    if ($currentPassword !== $driver['Password']) {
        header("Location: driverprofile.php?error=wrongpass");
        exit();
    }
    // 4) Save new password as plain text
    $updateQuery = "UPDATE drivers SET Password = ? WHERE Username = ?";
    $updateStmt  = mysqli_prepare($conn, $updateQuery);
    if (!$updateStmt) {
        header("Location: driverprofile.php?error=prepare");
        exit();
    }
    mysqli_stmt_bind_param($updateStmt, "ss", $newPassword, $Username);
    if (mysqli_stmt_execute($updateStmt)) {
        mysqli_stmt_close($updateStmt);
        header("Location: driverprofile.php?success=1");
        exit();
    } else {
        mysqli_stmt_close($updateStmt);
        header("Location: driverprofile.php?error=update");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Profile</title>
    <link rel="stylesheet" href="driversProfile1.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <aside>
        <div class="logo">
            <img src="../5. pictures/driver.png" alt="CartIT x Driver Logo" />
        </div>
        <hr class="logo-divider">
        <p class="menu-title">Main Menu</p>

        <nav>
            <ul class="sidebar-list">
                <li>
                    <a href="driver_dashboard.php"><i class='bx bx-layer'></i>Dashboard</a>
                </li>
                <li>
                    <a href="deliveries.php"><i class='bx bx-package'></i>Deliveries</a>
                </li>
                <li>
                    <a href="../User_Driver_Nav_Withdrawal/withdraw.php"><i class='bx bx-money'></i>Cash Deposit</a>
                </li>
                <li class="active">
                    <a href="driverprofile.php"><i class='bx bx-user'></i>My Profile</a>
                </li>
            </ul>
        </nav>

        <form method="POST" style="display:inline;">
            <button type="submit" name="logout" class="logout">
                <i class='bx bx-log-out'></i> Log-out
            </button>
        </form>
    </aside>

    <main>
        <header class="user-info-container">
            <div class="user-info-top">
                <div class="user-profile">
                    <i class='bx bxs-user-circle'></i>
                </div>
                <div class="user-text">
                    <p>@<?php echo htmlspecialchars($driver['Username']); ?></p>
                    <p><?php echo htmlspecialchars($driver['EmailAdd']); ?></p>
                </div>
            </div>
            <hr class="user-info-divider">
        </header>

        <section class="profile">
            <h2>Profile</h2>
            <hr class="profile-divider">
            
            <!-- Display fields -->
            <dl id="profile-display">
                <dt>Driver Name</dt>
                <dd id="display-username"><?php echo htmlspecialchars($driver['Username']); ?></dd>
                <dt>Email</dt>
                <dd id="display-email"><?php echo htmlspecialchars($driver['EmailAdd']); ?></dd>
                <dt>Phone number</dt>
                <dd id="display-phone"><?php echo htmlspecialchars($driver['PhoneNum']); ?></dd>
            </dl>
        </section>

        <!--Change Password-->
        <section class="change-password">
            <h2>Change Password</h2>
            <hr class="profile-divider" />
            <form id="password-form" method="POST" action="">
                <div class="form-group">
                    <label for="current-password">Current password</label>
                    <div class="input-wrapper">
                        <input type="password" id="current-password" name="current-password" required>
                        <span class="material-symbols-outlined" onclick="togglePassword('current-password', this)">visibility</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new-password">New password</label>
                    <div class="input-wrapper">
                        <input type="password" id="new-password" name="new-password" required>
                        <span class="material-symbols-outlined" onclick="togglePassword('new-password', this)">visibility</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm new password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm-password" name="confirm-password" required>
                        <span class="material-symbols-outlined" onclick="togglePassword('confirm-password', this)">visibility</span>
                    </div>
                </div>

                <button type="submit" name="change_pass" id="change_pass" class="change_pass">Change Password</button>
            </form>
        </section>

        <!--Danger zone-->
        <section class="danger-zone">
            <h2>Danger Zone</h2>
            <p>Deleting your account is permanent and cannot be undone.</p>
            <hr class="profile-divider">
            <span style="color:blue"><strong>Action Will:</strong></span>
            <ul>
                <li>Remove your driver profile and all associated data</li>
                <li>Cancel any active deliveries</li>
                <li>Delete your delivery and earning history</li>
                <li>Prevent any future logins using this account</li>
            </ul>
            <button id="delete-btn" class="delete-account">Delete Account</button>
            <p>Feel free to contact <a href="mailto:cartit.support@gmail.com">cartit.support@gmail.com</a> with any questions.</p>
        </section>
    </main>

    <script>
        function togglePassword(id, icon) {
            const input = document.getElementById(id);
            const isPassword = input.type === "password";
            input.type = isPassword ? "text" : "password";
            icon.textContent = isPassword ? "visibility_off" : "visibility";
        }

        // Handle password change form submission
        document.getElementById('password-form').addEventListener('submit', function(e) {
            const newPass = document.getElementById('new-password').value;
            const confirmPass = document.getElementById('confirm-password').value;

            if (newPass !== confirmPass) {
                e.preventDefault();
                Swal.fire({
                    title: 'Passwords do not match',
                    icon: 'error',
                    confirmButtonText: 'Try Again'
                });
                return false;
            }
        });

      // Handle delete account
    document.getElementById('delete-btn').addEventListener('click', function () {
        Swal.fire({
            title: 'Are you sure you want to delete your account?',
            text: "Deleting your account is permanent and cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete Account',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            backdrop: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('deleteaccount.php', { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'delete_account=1'
                })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "success") {
                        Swal.fire('Deleted!', 'Your account has been deleted.', 'success')
                            .then(() => window.location.href = 'driver.php');
                    } else {
                        Swal.fire('Error', data, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Failed to delete account. Please try again.', 'error');
                });
            }
        });
    });

    // Handle URL parameters for success/error messages
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.get('success') === '1') {
            Swal.fire({
                title: 'Password Changed Successfully!',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        const error = urlParams.get('error');
        if (error) {
            let errorMessage = 'An error occurred';
            switch(error) {
                case 'wrongpass':
                    errorMessage = 'Incorrect current password!';
                    break;
                case 'mismatch':
                    errorMessage = 'New passwords do not match!';
                    break;
                case 'empty':
                    errorMessage = 'All fields are required!';
                    break;
                case 'update':
                    errorMessage = 'Failed to update password. Please try again.';
                    break;
                case 'prepare':
                    errorMessage = 'Database error. Please try again.';
                    break;
            }
            
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
</body>
</html>