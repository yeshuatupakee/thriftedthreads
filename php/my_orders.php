<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Count items in cart
$cart_count = 0;
$cart_query = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$cart_result = $cart_query->get_result();
$cart_data = $cart_result->fetch_assoc();
$cart_count = $cart_data['total_items'] ?? 0;

// Fetch user's orders
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total amount spent
$total_spent = 0;
$total_stmt = $conn->prepare("SELECT SUM(total_price) as total FROM orders WHERE user_id = ?");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
if ($row = $total_result->fetch_assoc()) {
    $total_spent = $row['total'] ?? 0;
}
$total_stmt->close();

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    echo "<script>
        alert('You have successfully logged out!');
        window.location.href = 'landingpage.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Orders | Thrifted Threads</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="../images/logo/logo.png">
</head>
<body class="bg-[#EFDCAB] text-[#443627] min-h-screen">

<!-- Navigation -->
<nav class="bg-[#F2F6D0] shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
  <div class="flex items-center space-x-3">
    <a href="homepage.php" class="flex items-center space-x-3">
      <img src="../images/logo/logo.png" alt="Thrift Hive Logo" class="h-10">
      <h1 class="text-2xl font-bold">Thrifted Threads</h1>
    </a>
  </div>
  <div class="flex items-center space-x-6">
    <a href="homepage.php" class="hover:text-[#D98324] font-medium transition">Home</a>
    <a href="profile.php" class="hover:text-[#D98324] font-medium">Profile</a>
    <a href="my_orders.php" class="hover:text-[#D98324] font-medium">My Orders</a>
    <form method="POST" class="inline">
      <button type="submit" name="logout" class="hover:text-[#D98324] font-medium transition bg-transparent border-none cursor-pointer">Logout</button>
    </form>
    <a href="cart.php" class="relative">
      <img src="../images/icons/shopping_cart_black.svg" alt="Cart" class="h-6">
      <span class="absolute -top-2 -right-2 bg-[#D98324] text-white text-xs px-1.5 py-0.5 rounded-full">
        <?php echo $cart_count; ?>
      </span>
    </a>
  </div>
</nav>

<!-- Main Content -->
<main class="max-w-5xl mx-auto px-4 py-10">
  <h2 class="text-3xl font-bold text-center mb-8">My Orders</h2>

  <!-- Total Spent -->
  <div class="bg-white shadow-md rounded-lg p-4 text-center mb-10">
    <p class="text-lg font-medium text-[#443627]">Total Spent</p>
    <p class="text-2xl font-bold text-[#D98324] mt-1">₱<?php echo number_format($total_spent, 2); ?></p>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <div class="space-y-6">
      <?php while ($order = $result->fetch_assoc()): ?>
        <?php
        $orderDateRaw = $order['order_date'];
        $orderDate = ($orderDateRaw && strtotime($orderDateRaw)) ? date("F d, Y \a\\t h:i A", strtotime($orderDateRaw)) : "Unknown date";

        // Fetch order items
        $order_items_stmt = $conn->prepare("
            SELECT od.quantity, od.price, p.id AS product_id, p.name, p.image
            FROM order_details od
            JOIN products p ON od.product_id = p.id 
            WHERE od.order_id = ?
        ");
        $order_items_stmt->bind_param("i", $order['id']);
        $order_items_stmt->execute();
        $item_result = $order_items_stmt->get_result();
        ?>
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
            <div class="space-y-1">
              <p class="text-sm text-[#443627]/80">Order #: <span class="font-medium"><?php echo $order['order_number']; ?></span></p>
              <p class="text-sm text-[#443627]/80">Order Date: <span class="font-medium"><?php echo $orderDate; ?></span></p>
              <p class="text-sm text-[#443627]/80">Status: <span class="font-medium"><?php echo ucfirst($order['status']); ?></span></p>
            </div>
            <div class="text-right mt-4 md:mt-0">
              <p class="text-sm text-[#443627]/80"><?php echo $order['total_items']; ?> item(s)</p>
            </div>
          </div>

          <!-- Order Items -->
          <ul class="space-y-2 border-t pt-4">
            <?php $individual_total = 0; ?>
            <?php while ($item = $item_result->fetch_assoc()): ?>
              <?php $individual_price = $item['quantity'] * $item['price']; ?>
              <li class="flex items-center space-x-4">
                <img src="../admin/<?php echo htmlspecialchars($item['image']); ?>" class="w-12 h-12 object-cover rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="flex-1">
                  <p class="font-medium"><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</p>
                  <a href="item_details.php?id=<?php echo $item['product_id']; ?>" class="text-sm text-[#D98324] underline hover:text-[#a95e15]">View Item</a>
                </div>
                <div class="text-right">
                  <p class="font-medium">₱<?php echo number_format($individual_price, 2); ?></p>
                </div>
              </li>
              <?php $individual_total += $individual_price; ?>
            <?php endwhile; ?>
            <div class="border-t pt-2 text-right font-semibold">Total for this Order: ₱<?php echo number_format($individual_total, 2); ?></div>
          </ul>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="bg-white text-center p-10 rounded-lg shadow">
      <p class="text-lg text-gray-600">You have no past orders.</p>
      <a href="homepage.php" class="text-[#D98324] font-medium underline mt-4 inline-block">Shop Now</a>
    </div>
  <?php endif; ?>
</main>

<!-- Footer -->
<footer class="text-center text-sm text-[#443627]/70 py-6 border-t mt-10">
  &copy; 2025 Thrifted Threads • Sustainable fashion made easy
</footer>

</body>
</html>
