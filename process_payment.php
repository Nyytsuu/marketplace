<?php
header('Content-Type: application/json');

// Simulate basic Luhn Algorithm check
function isValidCardNumber($number) {
    $number = preg_replace('/\D/', '', $number); // remove non-digits
    $sum = 0;
    $alt = false;

    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $n = intval($number[$i]);
        if ($alt) {
            $n *= 2;
            if ($n > 9) $n -= 9;
        }
        $sum += $n;
        $alt = !$alt;
    }

    return $sum % 10 === 0;
}

// Simulated POST values
$card_name   = $_POST['card_name'] ?? '';
$card_number = $_POST['card_number'] ?? '';
$exp_month   = $_POST['exp_month'] ?? '';
$exp_year    = $_POST['exp_year'] ?? '';
$cvv         = $_POST['cvv'] ?? '';
$amount      = $_POST['amount'] ?? 0;

// Basic validation
if (empty($card_name) || empty($card_number) || empty($exp_month) || empty($exp_year) || empty($cvv)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill out all fields.']);
    exit;
}

if (!isValidCardNumber($card_number)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid card number.']);
    exit;
}

// Optional: Check for expired card (month/year)
$currentYear = date('Y');
$currentMonth = date('n');
if ($exp_year < $currentYear || ($exp_year == $currentYear && $exp_month < $currentMonth)) {
    echo json_encode(['status' => 'error', 'message' => 'Card is expired.']);
    exit;
}

// Optional: Validate CVV length
if (!preg_match('/^\d{3,4}$/', $cvv)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CVV.']);
    exit;
}

// All checks passed — simulate success
echo json_encode(['status' => 'success', 'message' => 'Payment processed successfully.']);
exit;
?>
