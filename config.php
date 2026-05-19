<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root123');
define('DB_NAME', 'petshop_db');


define('RAZORPAY_KEY_ID', 'rzp_live_YOUR_KEY_ID');
define('RAZORPAY_KEY_SECRET', 'rzp_live_YOUR_KEY_SECRET');


define('SITE_URL', 'http://localhost/petshop');
define('SITE_NAME', 'PetShop');


$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


mysqli_set_charset($conn, "utf8");


function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}


function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>
