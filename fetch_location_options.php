<?php
$conn = mysqli_connect("localhost", "root", "", "marketplace");
if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$type = $_GET['type'] ?? '';
$province = $_GET['province'] ?? '';
$city = $_GET['city'] ?? '';

$data = [];

if ($type === 'province') {
    $query = "SELECT DISTINCT province_name FROM locations ORDER BY province_name ASC";
} elseif ($type === 'city' && $province) {
    $query = "SELECT DISTINCT city_municipality_name FROM locations WHERE province_name = ? ORDER BY city_municipality_name ASC";
} elseif ($type === 'barangay' && $province && $city) {
    $query = "SELECT barangay_name FROM locations WHERE province_name = ? AND city_municipality_name = ? ORDER BY barangay_name ASC";
}

if (!empty($query)) {
    $stmt = $conn->prepare($query);
    if ($type === 'province') {
        $stmt->execute();
    } elseif ($type === 'city') {
        $stmt->bind_param("s", $province);
        $stmt->execute();
    } elseif ($type === 'barangay') {
        $stmt->bind_param("ss", $province, $city);
        $stmt->execute();
    }
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = array_values($row)[0];
    }
}

echo json_encode($data);
?>
