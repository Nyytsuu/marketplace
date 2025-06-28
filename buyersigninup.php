<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$conn = new mysqli("localhost", "root", "", "marketplace");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    // SIGN UP LOGIC
    if (isset($_POST['signup'])) {
        $username = trim($_POST['Username'] ?? '');
        $email = trim($_POST['EmailAdd'] ?? '');
        $password = $_POST['Password'] ?? '';

        if (!empty($username) && !empty($email) && !empty($password)) {
            $stmt = $conn->prepare("INSERT INTO buyers (Username, EmailAdd, Password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);

            if ($stmt->execute()) {
                // Redirect to sign-in page after successful signup
                header("Location: finalslogin.php?signup=success");
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "All fields are required.";
        }
    }

    // SIGN IN LOGIC
    if (isset($_POST['signin'])) {
    $username = trim($_POST['Username'] ?? '');
$password = trim($_POST['Password'] ?? '');
// Check if username and password are not empty

        if (!empty($username) && !empty($password)) {
    $stmt = $conn->prepare("SELECT AccountID, Username, Password FROM buyers WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Trim spaces just in case
        if (trim($password) === trim($user['Password'])) {
            $_SESSION['AccountID'] = $user['AccountID'];
            $_SESSION['Username'] = $user['Username'];
            header("Location: homepage.php");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Invalid username.";
    }
}
    }
}

 // Ensure session variable is set
$conn->close();
?>
