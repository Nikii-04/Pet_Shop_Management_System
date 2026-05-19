<?php
session_start();
include 'config.php';


if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit();
}


$totalOrdersQuery = "SELECT COUNT(*) as count FROM orders";
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, $totalOrdersQuery))['count'];

$totalRevenueQuery = "SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'completed'";
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, $totalRevenueQuery))['revenue'] ?? 0;

$totalUsersQuery = "SELECT COUNT(*) as count FROM users";
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, $totalUsersQuery))['count'];

$pendingOrdersQuery = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'";
$pendingOrders = mysqli_fetch_assoc(mysqli_query($conn, $pendingOrdersQuery))['count'];

$recentOrdersQuery = "SELECT o.*, u.name, u.email FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 10";
$recentOrders = mysqli_query($conn, $recentOrdersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PetShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2ecc71;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #ecf0f1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--dark);
            color: white;
            padding: 20px 0;
            position: fixed;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            font-size: 24px;
        }

        .sidebar-menu {
            list-style: none;
            margin-top: 20px;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #bbb;
            text-decoration: none;
            transition: 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: white;
            background: rgba(52, 152, 219, 0.1);
            border-left-color: var(--primary);
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .top-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .top-bar h1 {
            color: var(--dark);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }

        .stat-card.revenue {
            border-left-color: var(--secondary);
        }

        .stat-card.users {
            border-left-color: var(--primary);
        }

        .stat-card.orders {
            border-left-color: var(--danger);
        }

        .stat-card h3 {
            color: #999;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: var(--dark);
        }

        .stat-card .icon {
            float: right;
            font-size: 40px;
            opacity: 0.1;
        }

        .table-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .table-section h2 {
            margin-bottom: 20px;
            color: var(--dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #2980b9;
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 14px;
            }

            table td, table th {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-paw"></i>
                <h3>PetShop Admin</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin-orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="admin-products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin-users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin-payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
                <li><a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h1>Dashboard</h1>
                <div>
                    <span style="margin-right: 20px;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="admin-logout.php" class="btn">Logout</a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card orders">
                    <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                    <h3>Total Orders</h3>
                    <div class="value"><?php echo $totalOrders; ?></div>
                    <p style="color: #999; font-size: 12px; margin-top: 10px;">All time orders</p>
                </div>

                <div class="stat-card revenue">
                    <div class="icon"><i class="fas fa-rupee-sign"></i></div>
                    <h3>Total Revenue</h3>
                    <div class="value">₹<?php echo number_format($totalRevenue, 0); ?></div>
                    <p style="color: #999; font-size: 12px; margin-top: 10px;">From completed orders</p>
                </div>

                <div class="stat-card users">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <h3>Total Users</h3>
                    <div class="value"><?php echo $totalUsers; ?></div>
                    <p style="color: #999; font-size: 12px; margin-top: 10px;">Registered customers</p>
                </div>

                <div class="stat-card">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <h3>Pending Orders</h3>
                    <div class="value"><?php echo $pendingOrders; ?></div>
                    <p style="color: #999; font-size: 12px; margin-top: 10px;">Waiting to ship</p>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="table-section">
                <h2>Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($recentOrders)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="admin-order-details.php?id=<?php echo $order['id']; ?>" class="btn">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
