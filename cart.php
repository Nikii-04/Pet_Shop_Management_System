<?php
session_start();
include 'config.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$products = [];
$subtotal = 0;

if (!empty($cart)) {
    $productIds = implode(',', array_keys($cart));
    $query = "SELECT * FROM products WHERE id IN ($productIds)";
    $result = mysqli_query($conn, $query);
    
    while ($product = mysqli_fetch_assoc($result)) {
        $products[$product['id']] = $product;
        $subtotal += $product['price'] * $cart[$product['id']]['quantity'];
    }
}

$shipping = $subtotal > 500 ? 0 : 50;
$tax = $subtotal * 0.05; // 5% tax
$total = $subtotal + $shipping + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - PetShop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-paw"></i> PetShop
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#products">Products</a></li>
            </ul>
            <div class="nav-actions">
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="cart-page">
            <h1>Shopping Cart</h1>

            <?php if (empty($cart)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Add some products to get started!</p>
                    <a href="index.html" class="btn btn-primary">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $productId => $product): ?>
                                    <tr>
                                        <td>
                                            <div class="cart-item">
                                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <div>
                                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                                    <p><?php echo htmlspecialchars($product['category']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <input type="number" min="1" value="<?php echo $cart[$productId]['quantity']; ?>" 
                                                   onchange="updateQuantity(<?php echo $productId; ?>, this.value)">
                                        </td>
                                        <td>₹<?php echo number_format($product['price'] * $cart[$productId]['quantity'], 2); ?></td>
                                        <td>
                                            <button class="btn-remove" onclick="removeFromCart(<?php echo $productId; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $shipping == 0 ? 'FREE' : '₹' . number_format($shipping, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax (5%)</span>
                            <span>₹<?php echo number_format($tax, 2); ?></span>
                        </div>

                        <div class="promo-code">
                            <input type="text" placeholder="Enter promo code" id="promoCode">
                            <button class="btn btn-secondary" onclick="applyPromo()">Apply</button>
                        </div>

                        <div class="summary-row total">
                            <span>Total</span>
                            <span id="totalAmount">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="payment-methods">
                            <h3>Payment Method</h3>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="razorpay" checked>
                                <span>Credit/Debit Card & UPI (Razorpay)</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cod">
                                <span>Cash on Delivery</span>
                            </label>
                        </div>

                        <button class="btn btn-primary btn-block btn-checkout" onclick="proceedToPayment(<?php echo $total; ?>)">
                            Proceed to Payment
                        </button>

                        <a href="index.php" class="btn btn-secondary btn-block">Continue Shopping</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const userId = <?php echo $_SESSION['user_id']; ?>;
        const userName = "<?php echo htmlspecialchars($_SESSION['user_name']); ?>";
        const userEmail = "<?php echo htmlspecialchars($_SESSION['user_email']); ?>";
        let totalAmount = <?php echo $total * 100; ?>; // In paise

        function proceedToPayment(amount) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (paymentMethod === 'cod') {
                if (confirm('Confirm Cash on Delivery order?')) {
                    createOrder('cod', amount);
                }
            } else {
                initiateRazorpay(amount);
            }
        }

        function initiateRazorpay(amount) {
            const options = {
                key: "<?php echo RAZORPAY_KEY_ID; ?>",
                amount: Math.round(amount * 100), 
                currency: "INR",
                name: "PetShop",
                description: "Pet Products Purchase",
                image: "https://petshop.local/logo.png",
                
                handler: function (response) {
                    verifyPayment(response.razorpay_payment_id, amount);
                },
                
                prefill: {
                    name: userName,
                    email: userEmail,
                },
                
                theme: {
                    color: "#3498db"
                },
                
                modal: {
                    ondismiss: function () {
                        alert('Payment cancelled');
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        }

        function verifyPayment(paymentId, amount) {
            fetch('verify-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    amount: amount
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    createOrder('razorpay', amount, paymentId);
                } else {
                    alert('Payment verification failed');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function createOrder(paymentMethod, amount, paymentId = '') {
            fetch('create-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_method: paymentMethod,
                    amount: amount,
                    payment_id: paymentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order placed successfully! Order ID: ' + data.order_id);
                    window.location.href = 'orders.php';
                } else {
                    alert('Error creating order: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateQuantity(productId, quantity) {
            fetch('update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: parseInt(quantity)
                })
            })
            .then(() => location.reload());
        }

        function removeFromCart(productId) {
            if (confirm('Remove this item from cart?')) {
                fetch('update-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: 0
                    })
                })
                .then(() => location.reload());
            }
        }

        function applyPromo() {
            const promoCode = document.getElementById('promoCode').value;
            alert('Promo code feature coming soon!');
        }
    </script>

    <script src="assets/js/cart.js"></script>
</body>
</html>
