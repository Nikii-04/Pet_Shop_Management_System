<?php
include 'config.php';

$createTable = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    status VARCHAR(20) DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $createTable)) {
    echo "✅ admin_users table ready.<br>";
} else {
    echo "❌ Error creating table: " . mysqli_error($conn) . "<br>";
}

$check = mysqli_query($conn, "SELECT id FROM admin_users WHERE username = 'admin'");
if (mysqli_num_rows($check) > 0) {

    $hashedPassword = password_hash('admin@123', PASSWORD_BCRYPT);
    $update = "UPDATE admin_users SET password = '$hashedPassword', status = 'active' WHERE username = 'admin'";
    if (mysqli_query($conn, $update)) {
        echo "✅ Admin password updated successfully.<br>";
    } else {
        echo "❌ Error updating admin: " . mysqli_error($conn) . "<br>";
    }
} else {
    $hashedPassword = password_hash('admin@123', PASSWORD_BCRYPT);
    $insert = "INSERT INTO admin_users (username, password, role, status) VALUES ('admin', '$hashedPassword', 'admin', 'active')";
    if (mysqli_query($conn, $insert)) {
        echo "✅ Admin user created successfully.<br>";
    } else {
        echo "❌ Error inserting admin: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br><strong>Admin Credentials:</strong><br>";
echo "Username: admin<br>";
echo "Password: admin@123<br>";
echo "<br><a href='admin-login.php'>👉 Go to Admin Login</a><br>";
echo "<br><em style='color:red;'>⚠️ Delete this file (setup-admin.php) after use for security!</em>";

mysqli_close($conn);
?>
