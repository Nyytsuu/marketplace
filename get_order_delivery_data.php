
<?php
// Example PHP file showing how to get order data through deliveries
session_start();

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get comprehensive order data through deliveries
function getOrderDataThroughDeliveries($conn, $orderId = null, $deliveryId = null) {
    $whereClause = "";
    $params = [];
    $types = "";
    
    if ($orderId) {
        $whereClause = "WHERE o.order_id = ?";
        $params[] = $orderId;
        $types .= "i";
    } elseif ($deliveryId) {
        $whereClause = "WHERE d.deliveryID = ?";
        $params[] = $deliveryId;
        $types .= "i";
    }
    
    $sql = "
        SELECT 
            -- Order Information
            o.order_id,
            o.user_id as buyer_id,
            o.order_date,
            o.total_price,
            o.status as order_status,
            o.payment_method,
            o.delivery_address_id,
            
            -- Delivery Information
            d.deliveryID,
            d.DriverID,
            d.status as delivery_status,
            d.earnings as delivery_earnings,
            d.pickup_date,
            d.delivery_date,
            
            -- Driver Information
            dr.Username as driver_username,
            dr.EmailAdd as driver_email,
            dr.ContactNum as driver_phone,
            dr.FirstName as driver_first_name,
            dr.LastName as driver_last_name,
            
            -- Buyer Address Information
            ba.full_name as buyer_name,
            ba.street_address as buyer_street,
            ba.barangay as buyer_barangay,
            ba.city as buyer_city,
            ba.province as buyer_province,
            ba.region as buyer_region,
            ba.postal_code as buyer_postal,
            ba.phone_number as buyer_phone,
            
            -- Seller Information (from products)
            s.fullname as seller_name,
            s.ship_street as seller_street,
            s.ship_city as seller_city,
            s.ship_province as seller_province,
            s.ship_region as seller_region,
            s.ship_phone as seller_phone,
            s.email as seller_email,
            
            -- Order Items
            oi.product_id,
            oi.quantity,
            oi.price as item_price,
            oi.color,
            oi.size,
            oi.main_image,
            
            -- Product Information
            p.product_name,
            p.description as product_description,
            p.seller_id
            
        FROM orders o
        LEFT JOIN deliveries d ON o.order_id = d.order_id
        LEFT JOIN driver_signup dr ON d.DriverID = dr.DriverID
        LEFT JOIN buyer_addresses ba ON o.delivery_address_id = ba.address_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN sellers s ON p.seller_id = s.id
        $whereClause
        ORDER BY o.order_date DESC, oi.product_id
    ";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orderData = [];
    while ($row = $result->fetch_assoc()) {
        $orderId = $row['order_id'];
        
        // Initialize order if not exists
        if (!isset($orderData[$orderId])) {
            $orderData[$orderId] = [
                'order_info' => [
                    'order_id' => $row['order_id'],
                    'buyer_id' => $row['buyer_id'],
                    'order_date' => $row['order_date'],
                    'total_price' => $row['total_price'],
                    'order_status' => $row['order_status'],
                    'payment_method' => $row['payment_method']
                ],
                'delivery_info' => [
                    'delivery_id' => $row['deliveryID'],
                    'driver_id' => $row['DriverID'],
                    'delivery_status' => $row['delivery_status'],
                    'delivery_earnings' => $row['delivery_earnings'],
                    'pickup_date' => $row['pickup_date'],
                    'delivery_date' => $row['delivery_date']
                ],
                'driver_info' => [
                    'username' => $row['driver_username'],
                    'email' => $row['driver_email'],
                    'phone' => $row['driver_phone'],
                    'full_name' => trim($row['driver_first_name'] . ' ' . $row['driver_last_name'])
                ],
                'buyer_info' => [
                    'name' => $row['buyer_name'],
                    'address' => [
                        'street' => $row['buyer_street'],
                        'barangay' => $row['buyer_barangay'],
                        'city' => $row['buyer_city'],
                        'province' => $row['buyer_province'],
                        'region' => $row['buyer_region'],
                        'postal_code' => $row['buyer_postal']
                    ],
                    'phone' => $row['buyer_phone']
                ],
                'seller_info' => [
                    'name' => $row['seller_name'],
                    'email' => $row['seller_email'],
                    'address' => [
                        'street' => $row['seller_street'],
                        'city' => $row['seller_city'],
                        'province' => $row['seller_province'],
                        'region' => $row['seller_region']
                    ],
                    'phone' => $row['seller_phone']
                ],
                'items' => []
            ];
        }
        
        // Add item if product_id exists
        if ($row['product_id']) {
            $orderData[$orderId]['items'][] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'description' => $row['product_description'],
                'quantity' => $row['quantity'],
                'price' => $row['item_price'],
                'color' => $row['color'],
                'size' => $row['size'],
                'image' => $row['main_image'],
                'seller_id' => $row['seller_id']
            ];
        }
    }
    
    $stmt->close();
    return $orderData;
}

// Example usage:
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Example 1: Get order data by order ID
    if (isset($_GET['order_id'])) {
        $orderData = getOrderDataThroughDeliveries($conn, $_GET['order_id']);
        echo "<h3>Order Data for Order ID: " . $_GET['order_id'] . "</h3>";
        echo "<pre>" . print_r($orderData, true) . "</pre>";
    }
    
    // Example 2: Get order data by delivery ID
    if (isset($_GET['delivery_id'])) {
        $orderData = getOrderDataThroughDeliveries($conn, null, $_GET['delivery_id']);
        echo "<h3>Order Data for Delivery ID: " . $_GET['delivery_id'] . "</h3>";
        echo "<pre>" . print_r($orderData, true) . "</pre>";
    }
    
    // Example 3: Get all orders with delivery information
    if (isset($_GET['all_orders'])) {
        $orderData = getOrderDataThroughDeliveries($conn);
        echo "<h3>All Orders with Delivery Information</h3>";
        foreach ($orderData as $orderId => $data) {
            echo "<h4>Order #$orderId</h4>";
            echo "<p><strong>Status:</strong> " . $data['order_info']['order_status'] . "</p>";
            echo "<p><strong>Delivery Status:</strong> " . ($data['delivery_info']['delivery_status'] ?? 'No delivery assigned') . "</p>";
            echo "<p><strong>Driver:</strong> " . ($data['driver_info']['full_name'] ?? 'No driver assigned') . "</p>";
            echo "<p><strong>Buyer:</strong> " . $data['buyer_info']['name'] . "</p>";
            echo "<p><strong>Items:</strong> " . count($data['items']) . " item(s)</p>";
            echo "<hr>";
        }
    }
}

// Example API endpoint for AJAX calls
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_order_details':
            if (isset($_POST['order_id'])) {
                $orderData = getOrderDataThroughDeliveries($conn, $_POST['order_id']);
                echo json_encode($orderData);
            } else {
                echo json_encode(['error' => 'Order ID required']);
            }
            break;
            
        case 'get_delivery_details':
            if (isset($_POST['delivery_id'])) {
                $orderData = getOrderDataThroughDeliveries($conn, null, $_POST['delivery_id']);
                echo json_encode($orderData);
            } else {
                echo json_encode(['error' => 'Delivery ID required']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Delivery Data Example</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .example { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        button { margin: 5px; padding: 10px; }
    </style>
</head>
<body>
    <h1>Order Delivery Data Examples</h1>
    
    <div class="example">
        <h3>Example Usage:</h3>
        <p>Add these parameters to the URL to see different data:</p>
        <ul>
            <li><code>?order_id=1</code> - Get data for specific order</li>
            <li><code>?delivery_id=1</code> - Get data for specific delivery</li>
            <li><code>?all_orders=1</code> - Get all orders with delivery info</li>
        </ul>
    </div>
    
    <div class="example">
        <h3>AJAX Example:</h3>
        <button onclick="getOrderDetails(1)">Get Order #1 Details</button>
        <button onclick="getDeliveryDetails(1)">Get Delivery #1 Details</button>
        <div id="result"></div>
    </div>
    
    <script>
        function getOrderDetails(orderId) {
            fetch('get_order_delivery_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_order_details&order_id=' + orderId
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function getDeliveryDetails(deliveryId) {
            fetch('get_order_delivery_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_delivery_details&delivery_id=' + deliveryId
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>