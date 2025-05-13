<?php
session_start();
$successMessage = "";
if (isset($_GET['order']) && $_GET['order'] === 'success') {
    $successMessage = "<div class='bg-green-500 text-white p-4 rounded-lg text-center mb-6'>
        <h3 class='text-2xl font-semibold'>Order Placed Successfully!</h3>
        <p class='mt-2'>Your order has been successfully placed. You will be contacted for confirmation shortly.</p>
    </div>";
}
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

// Fetch user details for pre-filling address and contact number
$user_query = "SELECT address, contact_number FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

$address = $user_data['address'];
$contact_number = $user_data['contact_number'];

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

// Process order when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect order details from POST request
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    
    if (!$address || !$contact_number) {
        $error = "Please provide your address and contact number.";
    } else {
        // Insert order into the database
        $order_number = uniqid("ORD"); // generates unique order number like ORD64789458ab3d2
        $total_items = array_sum(array_column($cart_items, 'quantity'));
        date_default_timezone_set('Asia/Manila');
        $order_date = date("Y-m-d H:i:s");

        $order_query = "INSERT INTO orders (user_id, order_number, total_items, total_price, order_date) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("isids", $user_id, $order_number, $total_items, $total, $order_date);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        
        // Insert order details
        foreach ($cart_items as $item) {
            // Insert into order_details
            $order_details_query = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($order_details_query);
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();

            // Update product stock
            $update_stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmt = $conn->prepare($update_stock_query);
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }

        // Clear cart after successful order
        $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($clear_cart_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Redirect to checkout with success message to clear cart display
        header("Location: checkout.php?order=success");
        exit();
        
        // Show success message on the same page
        echo "<div class='bg-green-500 text-white p-4 rounded-lg text-center mb-6'>
                <h3 class='text-2xl font-semibold'>Order Placed Successfully!</h3>
                <p class='mt-2'>Your order has been successfully placed. You will be contacted for confirmation shortly.</p>
              </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Thrifted Threads</title>
  <link rel="icon" href="../images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFDCAB] text-[#443627] min-h-screen font-sans">

<!-- Navigation -->
<nav class="bg-[#F2F6D0] shadow px-6 py-4 flex justify-between items-center sticky top-0 z-50">
  <a href="homepage.php" class="flex items-center gap-3">
    <img src="../images/logo/logo.png" alt="Thrifted Threads Logo" class="h-10">
    <h1 class="text-2xl font-bold">Thrifted Threads</h1>
  </a>
  <div class="flex items-center gap-6">
    <a href="homepage.php" class="hover:text-[#D98324] font-medium transition">Home</a>
    <a href="profile.php" class="hover:text-[#D98324] font-medium transition">Profile</a>
    <a href="my_orders.php" class="hover:text-[#D98324] font-medium transition">My Orders</a>
    <form method="POST" class="inline">
      <button type="submit" name="logout" class="hover:text-[#D98324] font-medium bg-transparent border-none cursor-pointer transition">Logout</button>
    </form>
    <a href="cart.php" class="relative">
      <img src="../images/icons/shopping_cart_black.svg" alt="Cart" class="h-6">
      <span class="absolute -top-2 -right-2 bg-[#D98324] text-white text-xs px-1.5 py-0.5 rounded-full">
        <?php echo count($cart_items); ?>
      </span>
    </a>
  </div>
</nav>

<!-- Main Content -->
<main class="max-w-4xl mx-auto px-4 py-10">
  <?php echo $successMessage; ?>
  <h2 class="text-3xl font-bold text-center mb-8">Checkout</h2>

  <?php if ($emptyCart): ?>
    <div class="bg-white py-16 px-6 text-center rounded-lg shadow">
      <p class="text-lg text-gray-600 mb-4">Your cart is currently empty.</p>
      <a href="homepage.php" class="text-[#D98324] hover:text-[#443627] underline transition">Continue Shopping</a>
    </div>
  <?php else: ?>

    <!-- Cart Items Summary -->
    <section class="bg-white p-6 rounded-lg shadow mb-8">
      <h3 class="text-xl font-semibold mb-4">Your Items</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead class="text-sm text-[#443627]/80">
            <tr class="border-b">
              <th class="py-3 px-4">Product</th>
              <th class="py-3 px-4">Price</th>
              <th class="py-3 px-4">Quantity</th>
              <th class="py-3 px-4">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart_items as $item): ?>
              <tr class="border-b hover:bg-[#f9f6ec] transition">
                <td class="py-4 px-4 flex items-center gap-4">
                  <img src="../admin/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-16 w-16 rounded object-cover border" />
                  <span><?php echo htmlspecialchars($item['name']); ?></span>
                </td>
                <td class="py-4 px-4">₱<?php echo number_format($item['price'], 2); ?></td>
                <td class="py-4 px-4"><?php echo $item['quantity']; ?></td>
                <td class="py-4 px-4 font-medium">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Checkout Form -->
    <form method="POST" class="bg-white p-6 rounded-lg shadow space-y-6">
      <h3 class="text-xl font-semibold mb-2">Shipping Information</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="col-span-2">
          <label for="address" class="block font-medium mb-1">Shipping Address</label>
          <textarea name="address" id="address" rows="3" class="w-full border rounded px-3 py-2" required><?php echo htmlspecialchars($address); ?></textarea>
        </div>

        <div class="col-span-2 md:col-span-1">
          <label for="contact_number" class="block font-medium mb-1">Contact Number</label>
          <input type="text" name="contact_number" id="contact_number" class="w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($contact_number); ?>" required />
        </div>
      </div>

      <?php if (isset($error)): ?>
        <p class="text-red-600 font-medium"><?php echo $error; ?></p>
      <?php endif; ?>

      <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-4">
        <p class="text-lg font-semibold text-[#443627]">
          Total: <span class="text-[#D98324]">₱<?php echo number_format($total, 2); ?></span>
        </p>
        <button type="submit" class="bg-[#D98324] hover:bg-[#443627] text-white font-medium px-6 py-3 rounded transition w-full md:w-auto">
          Place Order
        </button>
      </div>
    </form>

    <!-- Back to Cart -->
    <div class="text-center mt-6">
      <a href="cart.php" class="text-[#D98324] hover:text-[#443627] font-medium underline transition">← Back to Cart</a>
    </div>

  <?php endif; ?>
</main>

<!-- Footer -->
<footer class="text-center text-sm text-[#443627]/70 py-6 border-t mt-10">
  &copy; 2025 Thrifted Threads • Sustainable fashion made easy
</footer>

</body>
</html>