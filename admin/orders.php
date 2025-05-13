<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_conn.php';

// Handle Logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../php/login.php");
    exit();
}

// Fetch orders and user details
$sql = "
    SELECT 
        orders.id AS order_id, 
        orders.order_number, 
        orders.total_items, 
        orders.total_price, 
        orders.order_date, 
        orders.status,
        users.full_name AS user_name, 
        users.contact_number, 
        users.address AS shipping_address
    FROM orders 
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.order_date DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders | Thrifted Threads</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F2F6D0] text-[#443627] min-h-screen font-sans">

<div class="flex min-h-screen">

  <!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg hidden md:flex flex-col">
  <div class="p-6 border-b border-gray-200">
    <h2 class="text-2xl font-extrabold text-[#D98324] tracking-tight">Admin Panel</h2>
  </div>
  <nav class="flex flex-col gap-3 p-6 text-sm text-[#443627]">
    <a href="admin_dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
      <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0h-2v6h2"/></svg>
      Dashboard
    <a href="listed_products.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
      <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2" 
          viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
      Listed Products
    </a>
    <a href="add_product.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
      <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Add Product
    </a>
    <a href="orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
      <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v4H3zM5 7v13h14V7"/></svg>
      Orders
    </a>
    <a href="donations.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
      <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4z"/></svg>
      Donations
    </a>
    <a href="users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
      <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      Users
    </a>
  </nav>
</aside>

<!-- Main content -->
<div class="flex-1 flex flex-col">

<!-- Navbar -->
<header class="bg-white shadow-md px-6 py-4 border-b flex items-center justify-between">
    <h1 class="text-2xl font-bold text-[#D98324] tracking-tight">Thrifted Threads Admin</h1>
    <form method="POST">
        <button name="logout"
                class="bg-[#D98324] hover:bg-[#b86112] text-white font-medium px-5 py-2 rounded-lg transition-all duration-200 shadow-sm">
            Logout
        </button>
    </form>
</header>

<main class="flex-1 p-6">
    <h2 class="text-xl font-semibold mb-4">Orders</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="overflow-x-auto shadow-md rounded-lg">
            <table class="min-w-full table-auto text-sm">
                <thead>
                    <tr class="bg-[#D98324] text-white">
                        <th class="px-6 py-2 text-left">Order Number</th>
                        <th class="px-6 py-2 text-left">User Name</th>
                        <th class="px-6 py-2 text-left">Contact Number</th>
                        <th class="px-6 py-2 text-left">Shipping Address</th>
                        <th class="px-6 py-2 text-left">Total Items</th>
                        <th class="px-6 py-2 text-left">Ordered Items</th>
                        <th class="px-6 py-2 text-left">Total Price (₱)</th>
                        <th class="px-6 py-2 text-left">Order Date</th>
                        <th class="px-6 py-2 text-left">Order Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="px-6 py-3"><?php echo $row['order_number']; ?></td>
                            <td class="px-6 py-3"><?php echo $row['user_name']; ?></td>
                            <td class="px-6 py-3"><?php echo $row['contact_number']; ?></td>
                            <td class="px-6 py-3"><?php echo $row['shipping_address']; ?></td>
                            <td class="px-6 py-3"><?php echo $row['total_items']; ?></td>
                            <td class="px-6 py-3">
                                <ul class="list-disc ml-4">
                                    <?php
                                    $order_id = $row['order_id'];
                                    $stmt = $conn->prepare("
                                        SELECT p.name, od.quantity, od.price 
                                        FROM order_details od
                                        JOIN products p ON od.product_id = p.id
                                        WHERE od.order_id = ?
                                    ");
                                    $stmt->bind_param("i", $order_id);
                                    $stmt->execute();
                                    $item_result = $stmt->get_result();

                                    while ($item = $item_result->fetch_assoc()):
                                    ?>
                                        <li><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?> (₱<?php echo number_format($item['price'], 2); ?>)</li>
                                    <?php endwhile; ?>
                                </ul>
                            </td>
                            <td class="px-6 py-3">₱<?php echo number_format($row['total_price'], 2); ?></td>
                            <td class="px-6 py-3"><?php echo date('F j, Y, g:i a', strtotime($row['order_date'])); ?></td>
                            <td class="px-6 py-3">
                                <form method="POST" action="update_status.php" class="flex gap-2 items-center mb-2">
                                    <select name="status" class="p-2 border rounded">
                                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Shipped" <?= $row['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <input type="hidden" name="order_id" value="<?= $row['order_id']; ?>" />
                                    <button type="submit" name="update_status" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">Update</button>
                                </form>
                                <form method="POST" action="delete_order.php" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                    <input type="hidden" name="order_id" value="<?= $row['order_id']; ?>" />
                                    <button type="submit" name="delete_order" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm w-full">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600">No orders found.</p>
    <?php endif; ?>
</main>

</div>
</div>
</body>
</html>
