<?php
session_start();
include('db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Count items in cart for current user
$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
    $cart_query->bind_param("i", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_data = $cart_result->fetch_assoc();
    $cart_count = $cart_data['total_items'] ?? 0;
}

$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Log out if the logout button is clicked
if (isset($_POST['logout'])) {
    session_destroy();
    echo "<script>
        alert('You have successfully logged out!');
        window.location.href = 'landingpage.php'; // Redirect to landing page
    </script>";
    exit();  // Stop further script execution after redirect
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
    <!-- Wrap the logo and the title in a link -->
    <a href="homepage.php" class="flex items-center space-x-3">
      <img src="../images/logo/logo.png" alt="Thrift Hive Logo" class="h-10">
      <h1 class="text-2xl font-bold">Thrifted Threads</h1>
    </a>
  </div>
  <div class="flex items-center space-x-6">
    <a href="homepage.php" class="hover:text-[#D98324] font-medium transition">Home</a>
    <a href="profile.php" class="hover:text-[#D98324] font-medium">Profile</a>
    <a href="my_orders.php" class="hover:text-[#D98324] font-medium">My Orders</a>
    <!-- Logout Button (styled as part of the navbar) -->
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

  <?php if ($result->num_rows > 0): ?>
    <div class="space-y-6">
      <?php while ($order = $result->fetch_assoc()): ?>
      <?php
      // Get and format the order date
      $orderDateRaw = $order['order_date'];
      if ($orderDateRaw && strtotime($orderDateRaw)) {
          $orderDate = date("F d, Y \a\\t h:i A", strtotime($orderDateRaw)); // Full date + time
      } else {
          $orderDate = "Unknown date";
      }
      ?>
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div class="space-y-1">
              <p class="text-sm text-[#443627]/80">Order #: <span class="font-medium"><?php echo $order['order_number']; ?></span></p>
              <p class="text-sm text-[#443627]/80">Order Date: <span class="font-medium"><?php echo $orderDate; ?></span></p>
            </div>
            <div class="text-right mt-4 md:mt-0">
              <p class="text-lg font-semibold">₱<?php echo number_format($order['total_price'], 2); ?></p>
              <p class="text-sm text-[#443627]/80"><?php echo $order['total_items']; ?> item(s)</p>
            </div>
          </div>
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
