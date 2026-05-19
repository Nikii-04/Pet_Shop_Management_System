<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

$userQuery = "SELECT * FROM users WHERE id = $userId";
$userResult = mysqli_query($conn, $userQuery);
$user = mysqli_fetch_assoc($userResult);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = 'Valid 10-digit phone is required';
    }
    if (empty($address)) {
        $errors[] = 'Address is required';
    }
    if (empty($city)) {
        $errors[] = 'City is required';
    }
    if (empty($pincode) || !preg_match('/^[0-9]{6}$/', $pincode)) {
        $errors[] = 'Valid 6-digit pincode is required';
    }
    
    if (empty($errors)) {
        $updateQuery = "UPDATE users SET name = '$name', phone = '$phone', address = '$address', 
                        city = '$city', pincode = '$pincode' WHERE id = $userId";
        
        if (mysqli_query($conn, $updateQuery)) {
            $_SESSION['user_name'] = $name;
            $success = true;
           
            $userResult = mysqli_query($conn, $userQuery);
            $user = mysqli_fetch_assoc($userResult);
        } else {
            $errors[] = 'Error updating profile: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PetShop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            padding: 40px 0;
        }

        .profile-sidebar {
            background: white;
            padding: 25px;
            border-radius: 8px;
            height: fit-content;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 25px;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 15px;
        }

        .profile-sidebar h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-sidebar p {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .profile-menu {
            list-style: none;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .profile-menu li {
            margin: 0;
        }

        .profile-menu a {
            display: block;
            padding: 12px 0;
            color: #666;
            text-decoration: none;
            border-bottom: 1px solid #f0f0f0;
            transition: 0.3s;
        }

        .profile-menu a:hover,
        .profile-menu a.active {
            color: #3498db;
            padding-left: 10px;
        }

        .profile-menu i {
            margin-right: 10px;
            width: 20px;
        }

        .profile-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .profile-content h2 {
            margin-bottom: 25px;
            color: #333;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .btn-save {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            margin-top: 20px;
        }

        .btn-save:hover {
            background: #2980b9;
        }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                display: flex;
                gap: 20px;
                align-items: center;
            }

            .profile-avatar {
                margin-bottom: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
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
                <a href="index.php" class="btn">Home</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                
                <ul class="profile-menu">
                    <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Edit Profile</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                    <li><a href="#"><i class="fas fa-heart"></i> Wishlist</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            <div class="profile-content">
                <h2>Edit Profile</h2>

                <?php if ($success): ?>
                    <div class="success-message">
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p>❌ <?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address (Cannot Change)</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="pincode">Pincode</label>
                            <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($user['pincode']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Member Since</label>
                        <input type="text" value="<?php echo date('M d, Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
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
