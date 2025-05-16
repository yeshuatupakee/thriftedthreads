<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/login.php");
    exit();
}
include 'db_conn.php';

if (isset($_POST['delete_order'])) {
    $orderId = $_POST['order_id'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Step 1: Delete from order_details first
        $stmt1 = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt1->bind_param("i", $orderId);
        $stmt1->execute();
        $stmt1->close();

        // Step 2: Delete from orders table
        $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt2->bind_param("i", $orderId);
        $stmt2->execute();
        $stmt2->close();

        // Commit transaction
        $conn->commit();

        header("Location: orders.php?status=deleted");
        exit();

    } catch (Exception $e) {
        $conn->rollback(); // Roll back on error
        header("Location: orders.php?status=delete_error");
        exit();
    }
}
?>
