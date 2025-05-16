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
<nav class="bg-[#F2F6D0] shadow-lg px-6 py-4 flex justify-between items-center sticky top-0 z-50">
  <a href="homepage.php" class="flex items-center gap-3">
    <img src="../images/logo/logo.png" alt="Logo" class="h-10">
    <span class="text-2xl font-bold text-[#443627]">Thrifted Threads</span>
  </a>
  <div class="flex items-center gap-6 text-[#443627] font-medium">
    <a href="homepage.php" class="hover:text-[#D98324] transition">Home</a>
    <a href="profile.php" class="hover:text-[#D98324] transition">Profile</a>
    <a href="my_orders.php" class="hover:text-[#D98324] transition">My Orders</a>
    <form method="POST" class="inline">
      <button type="submit" name="logout" class="hover:text-[#D98324] transition bg-transparent border-none cursor-pointer">Logout</button>
    </form>
    <a href="cart.php" class="relative">
      <img src="../images/icons/shopping_cart_black.svg" alt="Cart" class="h-6">
      <span class="absolute -top-2 -right-2 bg-[#D98324] text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo count($cart_items); ?></span>
    </a>
  </div>
</nav>

<!-- Cart Content -->
<div class="max-w-6xl mx-auto px-4 py-12">
  <h1 class="text-4xl font-bold text-center text-[#443627] mb-10">🛍️ Your Shopping Cart</h1>

  <?php if ($emptyCart): ?>
    <div class="text-center bg-white py-12 px-6 rounded-xl shadow-md text-[#443627]">
      <p class="text-lg mb-4">Your cart is currently empty.</p>
      <a href="homepage.php" class="text-[#D98324] hover:text-[#443627] underline transition">Continue Shopping</a>
    </div>
  <?php else: ?>
    <div class="bg-white p-8 rounded-2xl shadow-lg">
      <div class="overflow-x-auto">
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-[#f9f6ec] text-[#443627] text-left">
              <th class="py-3 px-5">Product</th>
              <th class="py-3 px-5">Price</th>
              <th class="py-3 px-5">Quantity</th>
              <th class="py-3 px-5">Subtotal</th>
              <th class="py-3 px-5">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart_items as $item): ?>
              <tr class="border-b hover:bg-[#f5f3ea] transition">
                <td class="py-4 px-5 flex items-center gap-4">
                  <img src="../admin/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-16 w-16 object-cover rounded border border-gray-300" />
                  <span class="text-[#443627] font-medium"><?php echo htmlspecialchars($item['name']); ?></span>
                </td>
                <td class="py-4 px-5 text-[#443627]">₱<?php echo number_format($item['price'], 2); ?></td>
                <td class="py-4 px-5 text-[#443627]"><?php echo $item['quantity']; ?></td>
                <td class="py-4 px-5 text-[#443627] font-semibold">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                <td class="py-4 px-5">
                  <div class="flex gap-4">
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
      <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-2xl font-semibold text-[#443627]">Total: <span class="text-[#D98324]">₱<?php echo number_format($total, 2); ?></span></p>
        <a href="checkout.php" class="bg-[#D98324] hover:bg-[#443627] text-white px-8 py-3 rounded-xl transition shadow-md w-full md:w-auto text-center font-semibold">Proceed to Checkout</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Footer -->
<footer class="text-center text-sm text-[#443627]/70 py-8">
  &copy; 2025 Thrifted Threads • Sustainable fashion made easy
</footer>


</body>
</html>
