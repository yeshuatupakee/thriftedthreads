<?php
session_start();
include 'db_conn.php'; // Your DB connection

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Count items in cart for current user
$cart_count_stmt = $conn->prepare("SELECT SUM(quantity) AS item_count FROM cart WHERE user_id = ?");
$cart_count_stmt->bind_param("i", $user_id);
$cart_count_stmt->execute();
$cart_count_result = $cart_count_stmt->get_result()->fetch_assoc();
$cart_item_count = $cart_count_result['item_count'] ?? 0;
$cart_count_stmt->close();

$stmt = $conn->prepare("SELECT full_name, email, created_at, address, profile_picture, contact_number FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $email, $created_at, $address, $profile_picture, $contact_number);
$stmt->fetch();
$stmt->close();

// Fetch recent orders
$order_stmt = $conn->prepare("SELECT order_number, total_items, total_price, order_date FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$recent_orders = $order_result->fetch_all(MYSQLI_ASSOC);
$order_stmt->close();

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile | Thrifted Threads</title>
  <link rel="icon" href="../images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFDCAB] text-[#443627] min-h-screen flex flex-col items-center pt-10">

  <!-- Navigation Bar -->
  <nav class="bg-[#F2F6D0] shadow-md px-6 py-4 flex justify-between items-center w-full fixed top-0 left-0 z-50">
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
  <?php if ($cart_item_count > 0): ?>
    <span class="absolute -top-2 -right-2 bg-[#D98324] text-white text-xs px-1.5 py-0.5 rounded-full">
      <?= $cart_item_count ?>
    </span>
  <?php endif; ?>
</a>
    </div>
  </nav>

  <!-- Profile Container -->
  <div class="bg-white rounded-2xl shadow-lg w-full max-w-3xl p-8 space-y-10 mt-20">

    <!-- Edit Profile Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
      <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-lg relative">
        <h2 class="text-xl font-bold mb-4">Edit Profile</h2>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" class="w-full border border-[#ccc] px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full border border-[#ccc] px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Contact Number</label>
            <input type="tel" name="contact_number" value="<?= htmlspecialchars($contact_number) ?>" 
                pattern="[0-9]*" 
                inputmode="numeric" 
                maxlength="11"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                class="w-full border border-[#ccc] px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Shipping Address</label>
            <textarea name="address" rows="3" class="w-full border border-[#ccc] px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]" required><?= htmlspecialchars($address) ?></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Profile Picture</label>
            <input type="file" name="profile_picture" accept="image/*" class="w-full border border-[#ccc] px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
          </div>
          <div class="flex justify-end space-x-2">
            <button type="button" onclick="toggleModal()" class="px-4 py-2 bg-gray-300 text-[#443627] rounded-lg hover:bg-gray-400 transition">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-[#D98324] text-white rounded-lg hover:bg-[#443627] transition">Save Changes</button>
          </div>
        </form>
        <button onclick="toggleModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-lg">&times;</button>
      </div>
    </div>

<!-- Profile Overview -->
<div class="flex flex-col md:flex-row items-center md:items-center md:space-x-8 mt-4">
  <div class="relative">
    <img src="../<?= htmlspecialchars($profile_picture) ?>" alt="User Avatar" class="w-32 h-32 rounded-full border-4 border-[#D98324] object-cover shadow-md transition-transform hover:scale-105">
    <button onclick="openModal()" class="absolute bottom-0 right-0 bg-[#D98324] text-white p-2 rounded-full shadow-md hover:bg-[#443627]">
      <img src="../images/icons/edit.svg" class="h-6" alt="Edit">
    </button>
  </div>
  <div class="text-center md:text-left mt-4 md:mt-0">
    <h2 class="text-3xl font-bold"><?= htmlspecialchars($full_name) ?></h2>
  </div>
</div>

    <!-- Account Info -->
    <div class="mt-6">
      <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
      <p><strong>Contact Number:</strong>
        <?php if (!empty($contact_number)): ?>
          <?= htmlspecialchars($contact_number) ?>
        <?php else: ?>
          <span class="italic text-gray-500">No contact number set, <a href="#" onclick="toggleModal()" class="text-[#D98324] hover:underline">want to set your number?</a></span>
        <?php endif; ?>
      </p>
      <p><strong>Shipping Address:</strong>
        <?php if (!empty($address)): ?>
          <?= htmlspecialchars($address) ?>
        <?php else: ?>
          <span class="italic text-gray-500">Shipping address not set, <a href="#" onclick="toggleModal()" class="text-[#D98324] hover:underline">want to set your address?</a></span>
        <?php endif; ?>
      </p>
      <p><strong>Member Since:</strong> <?= date("F Y", strtotime($created_at)) ?></p>
    </div>

    <!-- Order History -->
    <div class="mt-6">
      <h3 class="text-xl font-semibold mb-4">Recent Orders</h3>
      <div class="space-y-4">
        <?php if (count($recent_orders) > 0): ?>
          <?php foreach ($recent_orders as $order): ?>
            <div class="bg-[#F2F6D0] p-4 rounded-lg shadow-sm hover:shadow-lg transition">
              <p class="font-semibold">Order #<?= htmlspecialchars($order['order_number']) ?></p>
              <p class="text-sm opacity-80">
                <?= htmlspecialchars($order['total_items']) ?> items • ₱<?= number_format($order['total_price'], 2) ?> • <?= date("F j, Y", strtotime($order['order_date'])) ?>
              </p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-sm italic text-gray-500">No recent orders found.</p>
        <?php endif; ?>
        <a href="my_orders.php" class="text-[#D98324] hover:underline text-sm">View All Orders</a>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="mt-10 text-sm text-[#443627] opacity-70">
    &copy; 2025 Thrifted Threads
  </footer>

<script>
function openModal() {
  document.getElementById('editModal').classList.remove('hidden');
}

function toggleModal() {
  document.getElementById('editModal').classList.toggle('hidden');
}
</script>

</body>
</html>