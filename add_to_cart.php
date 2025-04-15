<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;

if (!$product_id) {
    echo "<script>
        alert('Invalid product!');
        window.location.href = 'homepage.php';
    </script>";
    exit();
}

// Check if the product is already in the user's cart
$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

// Check stock availability
$stock_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
$stock_stmt->bind_param("i", $product_id);
$stock_stmt->execute();
$stock_result = $stock_stmt->get_result();
$stock_row = $stock_result->fetch_assoc();

if (!$stock_row || $stock_row['stock'] <= 0) {
    echo "<script>
        alert('Sorry, this item is out of stock!');
        window.location.href = 'homepage.php';
    </script>";
    exit();
}

if ($result->num_rows > 0) {
    echo "<script>
        alert('This item is already in your cart!');
        window.location.href = 'homepage.php';
    </script>";
} else {
    // Add product to cart with default quantity of 1
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $user_id, $product_id);
    $insert->execute();

    echo "<script>
        alert('Item added to cart!');
        window.location.href = 'homepage.php';
    </script>";
}
