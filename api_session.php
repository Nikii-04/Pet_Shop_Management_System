<?php
header('Content-Type: application/json');
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';

$cartCount = 0;
if ($isLoggedIn && isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}

echo json_encode([
    'isLoggedIn' => $isLoggedIn,
    'userName' => $userName,
    'cartCount' => $cartCount
]);
?>
