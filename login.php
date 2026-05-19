<?php
session_start();
include 'config.php';

$errors = [];
$loginSuccess = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    if (empty($errors)) {
        $query = "SELECT * FROM users WHERE email = '$email' AND status = 'active'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            if (verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Update last login
                $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}";
                mysqli_query($conn, $updateQuery);
                
                header('Location: index.html');
                exit();
            } else {
                $errors[] = 'Invalid password';
            }
        } else {
            $errors[] = 'Email not found or account inactive';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PetShop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-paw"></i>
                <h1>PetShop Login</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p>❌ <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-login">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                </div>

                <div class="form-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" class="checkbox-label">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="auth-divider">OR</div>

            <button class="btn btn-social btn-google" onclick="loginWithGoogle()">
                <i class="fab fa-google"></i> Login with Google
            </button>

            <p class="auth-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>

    <script>
        function loginWithGoogle() {
            alert('Google login feature coming soon!');
        }
    </script>
</body>
</html>
