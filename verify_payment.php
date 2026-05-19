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
    $paymentId = $data['payment_id'];
    $amount = $data['amount'];


    // For now, we'll simulate verification
    // You should implement actual API call:
    /*
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/payments/$paymentId");
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $payment = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    if ($payment['status'] === 'captured' && $payment['amount'] == ($amount * 100)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
    }
    */
    
    if (!empty($paymentId) && $amount > 0) {
        $paymentQuery = "INSERT INTO payments 
                        (user_id, payment_id, amount, status, created_at) 
                        VALUES 
                        ('{$_SESSION['user_id']}', '$paymentId', $amount, 'verified', NOW())";
        
        if (mysqli_query($conn, $paymentQuery)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid payment details']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
