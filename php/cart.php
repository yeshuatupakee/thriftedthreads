<?php
session_start();
include('db_conn.php');

// Log out if the logout button is clicked
if (isset($_POST['logout'])) {
    session_destroy();
    echo "<script>
        alert('You have successfully logged out!');
        window.location.href = 'landingpage.php'; // Redirect to landing page
    </script>";
    exit();  // Stop further script execution after redirect
}

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$query = "SELECT c.product_id, p.name, p.price, p.image, c.quantity 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

$emptyCart = count($cart_items) === 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cart | Thrifted Threads</title>
  <link rel="icon" href="../images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
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
    <!-- Logout Button (styled as part of the navbar) -->
    <form method="POST" class="inline">
      <button type="submit" name="logout" class="hover:text-[#D98324] font-medium transition bg-transparent border-none cursor-pointer">Logout</button>
    </form>
    <a href="cart.php" class="relative">
      <img src="../images/icons/shopping_cart_black.svg" alt="Cart" class="h-6">
      <span class="absolute -top-2 -right-2 bg-[#D98324] text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo count($cart_items); ?></span>
    </a>
  </div>
</nav>

<!-- Cart Content -->
<!-- Inside the cart content section -->
<div class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-semibold mb-8 text-center">🛒 Your Shopping Cart</h1>

  <?php if ($emptyCart): ?>
    <div class="text-center text-lg text-gray-600 bg-white py-12 rounded-lg shadow-sm">
      <p>Your cart is currently empty.</p>
      <a href="homepage.php" class="mt-4 inline-block text-[#D98324] hover:text-[#443627] underline transition">Continue Shopping</a>
    </div>
  <?php else: ?>
    <div class="bg-white p-6 rounded-2xl shadow-md">
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
          <thead>
            <tr class="border-b text-left text-[#443627]">
              <th class="py-3 px-4">Product</th>
              <th class="py-3 px-4">Price</th>
              <th class="py-3 px-4">Quantity</th>
              <th class="py-3 px-4">Subtotal</th>
              <th class="py-3 px-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart_items as $item): ?>
              <tr class="border-b hover:bg-[#f9f6ec] transition">
                <td class="py-4 px-4 flex items-center gap-4">
                  <img src="../admin/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-16 w-16 rounded object-cover border border-gray-300" />
                  <span><?php echo htmlspecialchars($item['name']); ?></span>
                </td>
                <td class="py-4 px-4">₱<?php echo number_format($item['price'], 2); ?></td>
                <td class="py-4 px-4"><?php echo $item['quantity']; ?></td>
                <td class="py-4 px-4 font-medium">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                <td class="py-4 px-4">
                  <div class="flex gap-3">
                    <a href="item_details.php?id=<?php echo $item['product_id']; ?>" class="text-blue-600 hover:text-blue-800 transition">View</a>
                    <a href="remove_from_cart.php?product_id=<?php echo $item['product_id']; ?>" onclick="return confirm('Remove this item from cart?')" class="text-red-600 hover:text-red-800 transition">Remove</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Total and Checkout -->
      <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-xl font-semibold text-[#443627]">Total: <span class="text-[#D98324]">₱<?php echo number_format($total, 2); ?></span></p>
        <a href="checkout.php" class="bg-[#D98324] text-white px-6 py-3 rounded-lg hover:bg-[#443627] transition text-center w-full md:w-auto">Proceed to Checkout</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Footer -->
<footer class="text-center text-sm text-[#443627]/70 py-6">
  &copy; 2025 Thrifted Threads • Sustainable fashion made easy
</footer>

</body>
</html>
