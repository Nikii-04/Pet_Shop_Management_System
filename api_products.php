<?php
header('Content-Type: application/json');
include 'config.php';

$query = "SELECT * FROM products WHERE status = 'active'";
$result = mysqli_query($conn, $query);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);
?>
