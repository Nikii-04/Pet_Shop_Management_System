<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = $_SESSION['user_id'];


$orderQuery = "SELECT * FROM orders WHERE id = $orderId AND user_id = $userId";
$orderResult = mysqli_query($conn, $orderQuery);

if (mysqli_num_rows($orderResult) == 0) {
    header('Location: orders.php');
    exit();
}

$order = mysqli_fetch_assoc($orderResult);


$itemsQuery = "SELECT oi.*, p.name, p.image FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = $orderId";
$itemsResult = mysqli_query($conn, $itemsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - PetShop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .order-details-page {
            padding: 40px 0;
        }

        .order-header {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .order-header-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 20px;
        }

        .order-info {
            border-right: 1px solid #eee;
            padding-right: 20px;
        }

        .order-info:last-child {
            border-right: none;
            padding-right: 0;
        }

        .order-info label {
            color: #999;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .order-info p {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .status-timeline {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .timeline-item {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            align-items: flex-start;
        }

        .timeline-marker {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #2ecc71;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        .timeline-marker.pending {
            background: #f39c12;
        }

        .timeline-marker.processing {
            background: #3498db;
        }

        .timeline-content h4 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .timeline-content p {
            margin: 0;
            color: #999;
            font-size: 12px;
        }

        .items-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            background: #f5f5f5;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .item-details {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .item-info p {
            margin: 0;
            color: #999;
            font-size: 12px;
        }

        .summary-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            max-width: 400px;
            margin-left: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: bold;
            color: #3498db;
            border-bottom: none;
            padding: 15px 0;
            margin-top: 10px;
        }

        .action-buttons {
            margin-top: 25px;
            display: flex;
            gap: 15px;
        }

        .btn-print {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-print:hover {
            background: #2980b9;
        }

        @media print {
            .navbar, .action-buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-paw"></i> PetShop
            </div>
            <div class="nav-actions">
                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="order-details-page">
            <h1>Order Details</h1>
            <div class="order-header">
                <div class="order-header-row">
                    <div class="order-info">
                        <label>Order ID</label>
                        <p><?php echo htmlspecialchars($order['order_id']); ?></p>
                    </div>
                    <div class="order-info">
                        <label>Order Date</label>
                        <p><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div class="order-info">
                        <label>Payment Method</label>
                        <p><?php echo ucfirst($order['payment_method']); ?></p>
                    </div>
                    <div class="order-info">
                        <label>Total Amount</label>
                        <p style="color: #2ecc71;">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                </div>

                <div class="status-timeline">
                    <h3>Order Status</h3>
                    
                    <?php
                    $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                    $currentStatus = $order['order_status'];
                    $statusIndex = array_search($currentStatus, $statuses);
                    ?>

                    <?php foreach ($statuses as $index => $status): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?php echo $index <= $statusIndex ? 'completed' : 'pending'; ?>">
                                <i class="fas fa-<?php echo match($status) {
                                    'pending' => 'hourglass-start',
                                    'processing' => 'cog',
                                    'shipped' => 'truck',
                                    'delivered' => 'check',
                                    default => 'circle'
                                }; ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <h4><?php echo ucfirst($status); ?></h4>
                                <p><?php echo match($status) {
                                    'pending' => 'Your order has been received',
                                    'processing' => 'We are preparing your order',
                                    'shipped' => 'Your order is on the way',
                                    'delivered' => 'Order delivered successfully',
                                    default => ''
                                }; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="items-section">
                <h2>Order Items</h2>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($itemsResult)): ?>
                            <tr>
                                <td>
                                    <div class="item-details">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="" class="item-image">
                                        <div class="item-info">
                                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td><strong>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="summary-section">
                <h2>Summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($order['total_amount'] * 0.95, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?php echo $order['total_amount'] > 5000 ? 'FREE' : '₹50'; ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (5%)</span>
                    <span>₹<?php echo number_format($order['total_amount'] * 0.05, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>

                <div class="action-buttons">
                    <button class="btn-print" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Invoice
                    </button>
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 PetShop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
