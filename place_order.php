<?php
session_start();
require 'db_connection.php';  // your DB connection

function clean($data) {
    return htmlspecialchars(trim($data));
}

// Get order type: 'buy_now' or 'cart_checkout'
$order_type = isset($_POST['status']) ? strtolower($_POST['status']) : 'cart_checkout';


// Check logged-in user
$user_id = $_SESSION['AccountID'] ?? null;
if (!$user_id) {
    die("User not logged in.");
}
// Step 1: Get default delivery address
$stmt = $pdo->prepare("SELECT address_id FROM buyer_addresses WHERE buyer_id = ? AND is_default = TRUE LIMIT 1");
$stmt->execute([$user_id]);
$defaultAddress = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$defaultAddress) {
    throw new Exception("No default delivery address found.");
}

$delivery_address_id = $defaultAddress['address_id'];
// Extract billing info from POST
$first_name = clean($_POST['first_name'] ?? '');
$last_name = clean($_POST['last_name'] ?? '');
$address = clean($_POST['address'] ?? '');
$region = clean($_POST['region'] ?? '');
$province = clean($_POST['province'] ?? '');
$zip_code = clean($_POST['zip_code'] ?? '');
$phone = clean($_POST['phone'] ?? '');
$email = clean($_POST['email'] ?? '');  
// If billing info fields are not filled in POST, fetch from billing_info table
if (empty($first_name) || empty($last_name) || empty($address) || empty($region) ||
    empty($province) || empty($zip_code) || empty($phone) || empty($email)) {
    }
// If billing info exists, load it, but don't throw error if missing
$stmt = $pdo->prepare("SELECT * FROM billing_info WHERE user_id = ?");
$stmt->execute([$user_id]);
$billing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($billing) {
    $first_name = $billing['first_name'];
    $last_name = $billing['last_name'];
    $address = $billing['address'];
    $region = $billing['region'];
    $province = $billing['province'];
    $zip_code = $billing['zip_code'];
    $phone = $billing['phone'];
    $email = $billing['email'];
}
// NO ELSE -- don't throw exception -- allow proceeding with delivery_address only


// Check if billing info exists
$stmt = $pdo->prepare("SELECT user_id FROM billing_info WHERE user_id = ?");
$stmt->execute([$user_id]);
$billingExists = $stmt->fetchColumn();

// Insert or update billing info
if (!$billingExists) {
    $insert = $pdo->prepare("INSERT INTO billing_info (user_id, first_name, last_name, address, region, province, zip_code, phone, email) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([$user_id, $first_name, $last_name, $address, $region, $province, $zip_code, $phone, $email]);
} elseif (isset($_POST['remember_billing'])) {
    $update = $pdo->prepare("UPDATE billing_info SET first_name = ?, last_name = ?, address = ?, region = ?, province = ?, zip_code = ?, phone = ?, email = ? WHERE user_id = ?");
    $update->execute([$first_name, $last_name, $address, $region, $province, $zip_code, $phone, $email, $user_id]);
}

// Get payment method
$payment_method = clean($_POST['payment_method'] ?? 'cod');

try {
    $pdo->beginTransaction();

    $status = 'pending';
    $total_price = 0;


    if ($order_type === 'buy_now') {
        // Single product order
        $product_id = clean($_POST['product_id']);
        $name = clean($_POST['name']);
        $price = (float) $_POST['price'];
        $color = clean($_POST['color'] ?? '');
        $size = clean($_POST['size'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $image = clean($_POST['main_image'] ?? '');
        $total_price = (float) $_POST['total_price'];

        if (!$product_id || !$first_name || !$last_name || !$address || !$payment_method) {
            throw new Exception('Missing required fields.');
        }

        // Insert into orders table with order_type
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, delivery_address_id, order_date, status, payment_method, total_price, order_type) 
        VALUES (?, ?, NOW(), ?, ?, ?, ?)");
        $stmt->execute([$user_id, $delivery_address_id, $status, $payment_method, $total_price, $order_type]);
        $order_id = $pdo->lastInsertId();

        file_put_contents("debug_log.txt", "Color: $color | Size: $size | Image: $image" . PHP_EOL, FILE_APPEND);

        // Insert into order_items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, color, size, quantity, price, main_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_item->execute([$order_id, $product_id, $color, $size, $quantity, $price, $image]);
file_put_contents("debug_log.txt", "Order type received: " . $order_type . PHP_EOL, FILE_APPEND);
    } else {
        // Cart checkout
        if (empty($_SESSION['cart'])) {
            throw new Exception("Your cart is empty.");
        }

        // Calculate total price
        foreach ($_SESSION['cart'] as $item) {
            $total_price += $item['price'] * $item['quantity'];
        }

        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, delivery_address_id, order_date, status, payment_method, total_price, order_type) 
VALUES (?, ?, NOW(), ?, ?, ?, ?)");
$stmt->execute([$user_id, $delivery_address_id, $status, $payment_method, $total_price, $order_type]);

        $order_id = $pdo->lastInsertId();

        // Insert items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, color, size, quantity, price, main_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            $stmt_item->execute([
                $order_id,
                $item['product_id'],
                $item['color'] ?? '',
                $item['size'] ?? '',
                $item['quantity'],
                $item['price'],
                $item['main_image'] ?? ''
            ]);
        }
    }

    // Insert into payments
    $payment_stmt = $pdo->prepare("INSERT INTO payments 
        (order_id, user_id, first_name, last_name, address, region, province, zip_code, phone, email, payment_method, amount, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $payment_stmt->execute([
        $order_id, $user_id, $first_name, $last_name, $address, $region, $province,
        $zip_code, $phone, $email, $payment_method, $total_price
    ]);

    $pdo->commit();

    // Clear cart if it's a cart checkout
    if ($order_type !== 'buy_now') {
        unset($_SESSION['cart']);
    }

    header("Location: complete.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Order failed: " . $e->getMessage());
}
?>
