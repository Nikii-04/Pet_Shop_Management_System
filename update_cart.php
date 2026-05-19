<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = intval($data['product_id']);
    $quantity = intval($data['quantity']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        if (!isset($_SESSION['cart'][$productId])) {
            $query = "SELECT price FROM products WHERE id = $productId";
            $result = mysqli_query($conn, $query);
            $product = mysqli_fetch_assoc($result);
            
            $_SESSION['cart'][$productId] = [
                'quantity' => $quantity,
                'price' => $product['price']
            ];
        } else {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        }
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
