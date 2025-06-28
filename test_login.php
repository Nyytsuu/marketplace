<?php
session_start();

// This is a test script to simulate a login and set session variables
// Use this to test if your session handling works

if (isset($_POST['test_login'])) {
    // Simulate a successful login by setting session variables
    $_SESSION['AccountID'] = 'USR001';  // Test user ID
    $_SESSION['username'] = 'testuser';
    $_SESSION['email'] = 'test@example.com';

    echo "<div style='color: green; padding: 10px; background: #f0fff0; border: 1px solid green; margin: 10px;'>";
    echo "✓ Test login successful! Session variables set.<br>";
    echo "AccountID: " . $_SESSION['AccountID'] . "<br>";
    echo "Username: " . $_SESSION['username'] . "<br>";
    echo "<a href='buyerdashboard.php'>Go to Dashboard</a> | ";
    echo "<a href='buyerAddress.php'>Go to Address Page</a>";
    echo "</div>";
}

if (isset($_POST['clear_session'])) {
    session_destroy();
    session_start();
    echo "<div style='color: red; padding: 10px; background: #fff0f0; border: 1px solid red; margin: 10px;'>";
    echo "✓ Session cleared!";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Test Login - CartIT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .button {
            padding: 10px 20px;
            margin: 5px;
            background: #007cba;
            color: white;
            border: none;
            cursor: pointer;
        }

        .button:hover {
            background: #005a87;
        }

        .info {
            background: #f0f0f0;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #007cba;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Test Login System</h1>

        <div class="info">
            <h3>Current Session Status:</h3>
            <p><strong>Session ID:</strong> <?= session_id() ?></p>
            <p><strong>AccountID:</strong> <?= isset($_SESSION['AccountID']) ? $_SESSION['AccountID'] : 'Not set' ?></p>
            <p><strong>Username:</strong> <?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Not set' ?></p>
            <p><strong>All Session Data:</strong></p>
            <pre><?= print_r($_SESSION, true) ?></pre>
        </div>

        <h3>Test Actions:</h3>
        <form method="post">
            <button type="submit" name="test_login" class="button">Simulate Login (Set Session Variables)</button>
        </form>

        <form method="post">
            <button type="submit" name="clear_session" class="button" style="background: #dc3545;">Clear Session</button>
        </form>

        <h3>Navigation:</h3>
        <a href="buyerdashboard.php" class="button">Go to Dashboard</a>
        <a href="buyerAddress.php" class="button">Go to Address Page</a>
        <a href="debug_session.php" class="button">Debug Session</a>
        <a href="finalslogin.php" class="button">Go to Real Login</a>

        <div class="info">
            <h3>Instructions:</h3>
            <ol>
                <li>Click "Simulate Login" to set test session variables</li>
                <li>Then try visiting the Dashboard or Address pages</li>
                <li>If they work, your session handling is correct</li>
                <li>If not, check your login script (finalslogin.php)</li>
            </ol>
        </div>

        <div class="info">
            <h3>What Your Login Script Should Do:</h3>
            <p>Your <code>finalslogin.php</code> should set these session variables after successful authentication:</p>
            <pre>
$_SESSION['AccountID'] = $user_data['AccountID'];
$_SESSION['username'] = $user_data['username'];
$_SESSION['email'] = $user_data['email'];
            </pre>
        </div>
    </div>
</body>

</html>