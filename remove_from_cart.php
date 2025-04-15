<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    echo "<script>
        alert('Invalid product!');
        window.location.href = 'cart.php';
    </script>";
    exit();
}

// Delete the item from the user's cart
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();

echo "<script>
    alert('Item removed from cart.');
    window.location.href = 'cart.php';
</script>";
?>
