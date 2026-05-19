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
    
    
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    
    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit();
    }

    
    if ($data['payment_method'] === 'razorpay') {
        $paymentId = $data['payment_id'];
        
        $paymentStatus = 'completed';
    } else {
        $paymentStatus = 'pending';
    }

    $orderId = 'ORD' . date('YmdHis') . rand(1000, 9999);
    $userId = $_SESSION['user_id'];
    $totalAmount = $data['amount'];
    $paymentMethod = $data['payment_method'];
    
    $orderQuery = "INSERT INTO orders 
                   (order_id, user_id, total_amount, payment_method, payment_status, order_status, created_at) 
                   VALUES 
                   ('$orderId', $userId, $totalAmount, '$paymentMethod', '$paymentStatus', 'pending', NOW())";
    
    if (mysqli_query($conn, $orderQuery)) {
        $lastOrderId = mysqli_insert_id($conn);
        
        foreach ($cart as $productId => $item) {
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            $itemQuery = "INSERT INTO order_items 
                         (order_id, product_id, quantity, price) 
                         VALUES 
                         ($lastOrderId, $productId, $quantity, $price)";
            
            mysqli_query($conn, $itemQuery);
        }
        
        unset($_SESSION['cart']);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Order created successfully',
            'order_id' => $orderId
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error creating order: ' . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
