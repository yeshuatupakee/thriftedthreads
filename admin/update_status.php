<?php
include 'db_conn.php';

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the order status in the database
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        // Redirect back to the orders page with a success message
        header("Location: orders.php?status=updated");
        exit();
    } else {
        // Redirect back with an error message
        header("Location: orders.php?status=error");
        exit();
    }
}
?>
