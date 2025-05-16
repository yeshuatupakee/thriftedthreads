<?php
session_start();
include 'db_conn.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$product = null;
$cart_count = 0;

// Fetch product details
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

// Get cart count
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
    $cart_query->bind_param("i", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_data = $cart_result->fetch_assoc();
    $cart_count = $cart_data['total_items'] ?? 0;
}

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
  <title><?php echo htmlspecialchars($product['name']); ?> | Thrifted Threads</title>
  <link rel="icon" href="../images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFDCAB] text-[#443627] min-h-screen flex flex-col">

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
<main class="flex-grow">
  <div class="max-w-6xl mx-auto p-6">
    <?php if ($product): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="w-full h-full">
          <img src="../admin/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover max-h-[600px]">
        </div>
        <div class="p-6 flex flex-col justify-between">
          <div>
            <h2 class="text-3xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h2>
            <p class="text-2xl text-[#D98324] font-semibold mb-4">₱<?= number_format($product['price'], 2) ?></p>
            <p class="mb-4">
  <?php if ($product['stock'] > 10): ?>
    <span class="text-green-600 font-medium">In Stock</span>
  <?php elseif ($product['stock'] > 0): ?>
    <span class="text-yellow-600 font-medium">Only <?= $product['stock'] ?> left in stock!</span>
  <?php else: ?>
    <span class="text-red-600 font-medium">Out of Stock</span>
  <?php endif; ?>
</p>
<p class="text-base text-[#443627]/80 leading-relaxed mb-6">
  <?= nl2br(htmlspecialchars($product['description'])) ?>
</p>

<!-- Additional Details Section -->
<div class="bg-[#F2F6D0] p-4 rounded-lg text-sm space-y-2">
  <p><span class="font-semibold">Material:</span> <?= htmlspecialchars($product['material'] ?? 'N/A') ?></p>
  <p><span class="font-semibold">Condition:</span> <?= htmlspecialchars($product['condition_note'] ?? 'N/A') ?></p>
  <p><span class="font-semibold">Care Instructions:</span> <?= nl2br(htmlspecialchars($product['care_instructions'] ?? 'N/A')) ?></p>
  <p><span class="font-semibold">Fit/Style Notes:</span> <?= nl2br(htmlspecialchars($product['fit_style'] ?? 'N/A')) ?></p>
</div>
          </div>
          <?php if ($product['stock'] > 0): ?>
  <form method="POST" action="add_to_cart.php" class="mt-4">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <button type="submit" name="add_to_cart" class="w-full bg-[#D98324] hover:bg-[#443627] text-white text-lg font-medium py-3 rounded-md transition duration-200">
      Add to Cart
    </button>
  </form>
<?php else: ?>
  <div class="mt-4 text-center text-red-500 font-medium">This item is currently out of stock.</div>
<?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="text-center py-20">
        <h2 class="text-2xl font-bold text-gray-600 mb-2">Item Not Found</h2>
        <p class="text-gray-500">The product you are looking for may have been removed or does not exist.</p>
        <a href="homepage.php" class="inline-block mt-4 text-[#D98324] hover:underline">← Go back to homepage</a>
      </div>
    <?php endif; ?>
  </div>
</main>

<!-- Footer -->
<footer class="bg-[#F2F6D0] text-center text-sm text-[#443627]/70 py-6 mt-10">
  &copy; 2025 Thrifted Threads • Sustainable fashion made easy
</footer>

</body>
</html>
