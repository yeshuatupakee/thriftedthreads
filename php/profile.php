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
$cart_count = 0;

$user_id = $_SESSION['user_id'];
$cart_query = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$cart_result = $cart_query->get_result();
$cart_data = $cart_result->fetch_assoc();
$cart_count = $cart_data['total_items'] ?? 0;

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
<body class="bg-[#EFDCAB] text-[#443627] min-h-screen">

<nav class="bg-[#F2F6D0] shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50 w-full">
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

<div class="flex justify-center px-4">
<!-- Profile Container -->
  <div class="bg-white rounded-2xl shadow-lg w-full max-w-3xl p-8 space-y-10 mt-20">

<!-- Edit Profile Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-all duration-300 ease-in-out">
  <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl transform scale-95 transition-all duration-300 ease-in-out relative">
    <!-- Close Button -->
    <button onclick="toggleModal()" class="absolute top-4 right-4 text-gray-400 hover:text-[#D98324] text-2xl font-bold leading-none">&times;</button>

    <h2 class="text-xl font-bold text-[#443627] mb-6 flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#D98324]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Edit Profile
    </h2>

    <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <!-- Full Name -->
      <div>
        <label class="block text-sm font-medium text-[#443627] mb-1">Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" class="w-full border border-[#D6BFAF] px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]" required>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-[#443627] mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full border border-[#D6BFAF] px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]" required>
      </div>

      <!-- Contact Number -->
      <div>
        <label class="block text-sm font-medium text-[#443627] mb-1">Contact Number</label>
        <input type="tel" name="contact_number" value="<?= htmlspecialchars($contact_number) ?>" 
          pattern="[0-9]*" 
          inputmode="numeric" 
          maxlength="11"
          oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
          class="w-full border border-[#D6BFAF] px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>

      <!-- Shipping Address -->
      <div>
        <label class="block text-sm font-medium text-[#443627] mb-1">Shipping Address</label>
        <textarea name="address" rows="3" class="w-full border border-[#D6BFAF] px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]" required><?= htmlspecialchars($address) ?></textarea>
      </div>

      <!-- Profile Picture -->
      <div>
        <label class="block text-sm font-medium text-[#443627] mb-1">Profile Picture</label>
        <input type="file" name="profile_picture" accept="image/*" class="w-full border border-[#D6BFAF] px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>

      <!-- Buttons -->
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="toggleModal()" class="px-4 py-2 bg-gray-200 text-[#443627] rounded-lg hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-[#D98324] text-white rounded-lg hover:bg-[#443627] transition">Save Changes</button>
      </div>
    </form>
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

<!-- Account Info Section -->
<div class="bg-[#FDF6EC] border border-[#EAD7BB] rounded-2xl p-6 mt-8 shadow-sm">
  <h2 class="text-lg font-bold text-[#443627] mb-4 flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#D98324]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A10.97 10.97 0 0112 15c2.21 0 4.253.64 5.879 1.732M15 12h.01M9 12h.01M12 15h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    Account Information
  </h2>

  <div class="space-y-4 text-sm md:text-base text-[#443627]">

    <!-- Email -->
    <div class="flex flex-col sm:flex-row gap-1 sm:gap-4 items-start">
      <div class="flex items-center w-40 font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-[#D98324]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M4 4h16v16H4V4zm2 4l6 4 6-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Email:
      </div>
      <span><?= htmlspecialchars($email) ?></span>
    </div>

    <!-- Contact Number -->
    <div class="flex flex-col sm:flex-row gap-1 sm:gap-4 items-start">
      <div class="flex items-center w-40 font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-[#D98324]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M3 5a2 2 0 012-2h2.586a1 1 0 01.707.293l1.414 1.414A2 2 0 0011 5v1a2 2 0 01-2 2H9l-2 2 1 1-2 2H6a2 2 0 01-2-2V5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Contact Number:
      </div>
      <?php if (!empty($contact_number)): ?>
        <span><?= htmlspecialchars($contact_number) ?></span>
      <?php else: ?>
        <span class="italic text-gray-500">
          No contact number set, 
          <a href="#" onclick="toggleModal()" class="text-[#D98324] hover:underline">want to set your number?</a>
        </span>
      <?php endif; ?>
    </div>

    <!-- Shipping Address -->
    <div class="flex flex-col sm:flex-row gap-1 sm:gap-4 items-start">
      <div class="flex items-center w-40 font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-[#D98324]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="12" cy="9" r="2.5" />
        </svg>
        Shipping Address:
      </div>
      <?php if (!empty($address)): ?>
        <span><?= htmlspecialchars($address) ?></span>
      <?php else: ?>
        <span class="italic text-gray-500">
          Shipping address not set, 
          <a href="#" onclick="toggleModal()" class="text-[#D98324] hover:underline">want to set your address?</a>
        </span>
      <?php endif; ?>
    </div>

    <!-- Member Since -->
    <div class="flex flex-col sm:flex-row gap-1 sm:gap-4 items-start">
      <div class="flex items-center w-40 font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-[#D98324]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Member Since:
      </div>
      <span><?= date("F Y", strtotime($created_at)) ?></span>
    </div>

  </div>
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
</div>

<!-- Footer -->
<footer class="mt-10 text-sm text-[#443627] opacity-70 text-center">
  &copy; 2025 Thrifted Threads
</footer>


<script>
const modal = document.getElementById('editModal');

function openModal() {
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.querySelector('div').classList.remove('scale-95');
  modal.querySelector('div').classList.add('scale-100');
}

function toggleModal() {
  if (modal.classList.contains('opacity-0')) {
    openModal();
  } else {
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.querySelector('div').classList.remove('scale-100');
    modal.querySelector('div').classList.add('scale-95');
  }
}
</script>
</body>
</html>