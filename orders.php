<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];


$ordersQuery = "SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC";
$ordersResult = mysqli_query($conn, $ordersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - PetShop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-paw"></i> PetShop
            </div>
            <div class="nav-actions">
                <a href="index.php" class="btn">Home</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-page" style="padding: 40px 0;">
            <h1>My Orders</h1>

            <?php if (mysqli_num_rows($ordersResult) > 0): ?>
                <div class="orders-list" style="background: white; border-radius: 8px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                                <th style="padding: 15px; text-align: left;">Order ID</th>
                                <th style="padding: 15px; text-align: left;">Date</th>
                                <th style="padding: 15px; text-align: left;">Amount</th>
                                <th style="padding: 15px; text-align: left;">Payment Status</th>
                                <th style="padding: 15px; text-align: left;">Order Status</th>
                                <th style="padding: 15px; text-align: left;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($ordersResult)): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 15px;">
                                        <strong><?php echo htmlspecialchars($order['order_id']); ?></strong>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        ₹<?php echo number_format($order['total_amount'], 2); ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; 
                                                     background: <?php echo $order['payment_status'] == 'completed' ? '#d4edda' : '#fff3cd'; ?>; 
                                                     color: <?php echo $order['payment_status'] == 'completed' ? '#155724' : '#856404'; ?>;">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; 
                                                     background: <?php echo $order['order_status'] == 'delivered' ? '#d4edda' : '#e2e3e5'; ?>; 
                                                     color: <?php echo $order['order_status'] == 'delivered' ? '#155724' : '#383d41'; ?>;">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                           style="color: #3498db; text-decoration: none; font-weight: 500;">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="background: white; padding: 40px; border-radius: 8px; text-align: center;">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 20px; display: block;"></i>
                    <h2 style="color: #666;">No orders yet</h2>
                    <p style="color: #999; margin-bottom: 20px;">You haven't placed any orders yet.</p>
                    <a href="index.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 PetShop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
