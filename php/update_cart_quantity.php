<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$new_quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

// Fetch product stock
$query = "SELECT stock FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "<script>alert('Product not found!'); window.location.href='cart.php';</script>";
    exit();
}

$stock = $product['stock'];

// Determine new quantity based on action or input
if ($action === 'increase') {
    $new_quantity = $new_quantity + 1;
} elseif ($action === 'decrease') {
    $new_quantity = $new_quantity - 1;
}

// Validate new quantity
if ($new_quantity < 1) {
    $new_quantity = 1;
} elseif ($new_quantity > $stock) {
    $new_quantity = $stock;
    echo "<script>alert('Cannot add more than available stock ($stock)!');</script>";
}

// Update cart
$query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $new_quantity, $user_id, $product_id);

if ($stmt->execute()) {
    echo "<script>window.location.href='cart.php';</script>";
} else {
    echo "<script>alert('Failed to update quantity!'); window.location.href='cart.php';</script>";
}

$stmt->close();
$conn->close();
?>