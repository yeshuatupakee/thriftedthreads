<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_conn.php';

$imageBasePath = '../admin/';
$cart_count = 0;

$user_id = $_SESSION['user_id'];
$cart_query = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$cart_result = $cart_query->get_result();
$cart_data = $cart_result->fetch_assoc();
$cart_count = $cart_data['total_items'] ?? 0;

// Log out if the logout button is clicked
if (isset($_POST['logout'])) {
    session_destroy();
    echo "<script>
        alert('You have successfully logged out!');
        window.location.href = 'landingpage.php'; // Redirect to landing page
    </script>";
    exit();  // Stop further script execution after redirect
}

// Function to check if item is already in cart
function isInCart($productId) {
  return isset($_SESSION['cart']) && in_array($productId, array_column($_SESSION['cart'], 'id'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home | Thrifted Threads</title>
  <link rel="icon" href="../images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
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

<!-- Search Bar -->
<!-- Search + Filter Form -->
<div class="max-w-6xl mx-auto mt-6 px-4">
  <form method="GET" action="homepage.php" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <!-- Search -->
    <input 
      type="text" 
      name="search" 
      placeholder="Search for thrift items..." 
      value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
      class="px-4 py-2 border border-[#ccc] rounded-lg col-span-2"
    >

    <!-- Price Range -->
    <input 
      type="number" 
      name="min_price" 
      placeholder="Min Price" 
      value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>" 
      class="px-4 py-2 border border-[#ccc] rounded-lg"
    >
    <input 
      type="number" 
      name="max_price" 
      placeholder="Max Price" 
      value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>" 
      class="px-4 py-2 border border-[#ccc] rounded-lg"
    >

    <!-- Sort -->
    <select name="sort" class="px-4 py-2 border border-[#ccc] rounded-lg sm:col-span-1">
      <option value="">Sort by</option>
      <option value="price_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'price_asc') echo 'selected'; ?>>Price: Low to High</option>
      <option value="price_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'price_desc') echo 'selected'; ?>>Price: High to Low</option>
      <option value="name_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'name_asc') echo 'selected'; ?>>Name: A-Z</option>
      <option value="name_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'name_desc') echo 'selected'; ?>>Name: Z-A</option>
    </select>

    <button type="submit" class="col-span-full sm:col-span-1 bg-[#D98324] text-white px-4 py-2 rounded hover:bg-[#443627] transition mt-2 sm:mt-0">
      Apply
    </button>

    <!-- Reset Button -->
    <a href="homepage.php" class="col-span-full sm:col-span-1 text-center bg-[#F2F6D0] text-[#443627] border border-[#ccc] px-4 py-2 rounded hover:bg-[#D98324] hover:text-white transition mt-2 sm:mt-0">
      Reset Filters
    </a>

  </form>
</div>

  <!-- Product Grid -->
  <?php
$search = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? '';

$conditions = [];

if (!empty($search)) {
    $searchSafe = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(name LIKE '%$searchSafe%' OR description LIKE '%$searchSafe%')";
}

if (is_numeric($min_price)) {
    $conditions[] = "price >= " . floatval($min_price);
}

if (is_numeric($max_price)) {
    $conditions[] = "price <= " . floatval($max_price);
}

$whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

$orderBy = '';
switch ($sort) {
    case 'price_asc':
        $orderBy = 'ORDER BY price ASC';
        break;
    case 'price_desc':
        $orderBy = 'ORDER BY price DESC';
        break;
    case 'name_asc':
        $orderBy = 'ORDER BY name ASC';
        break;
    case 'name_desc':
        $orderBy = 'ORDER BY name DESC';
        break;
}

$query = "SELECT * FROM products $whereClause $orderBy";
$result = mysqli_query($conn, $query);
?>

<section class="max-w-6xl mx-auto px-4 py-10 grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
<?php
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition duration-300 hover:shadow-xl hover:scale-105 hover:-translate-y-1">
                <img src="' . $imageBasePath . $row['image'] . '" alt="' . htmlspecialchars($row['name']) . '" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-lg font-bold">' . htmlspecialchars($row['name']) . '</h3>
                    <p class="text-sm opacity-80 mt-1">' . htmlspecialchars($row['description']) . '</p>
                    <p class="mt-2 font-semibold">₱' . number_format($row['price'], 2) . '</p>
                    <div class="mt-4 flex justify-between items-center">
                        ' . ($row['stock'] > 0 ? '
                        <form method="POST" action="add_to_cart.php" class="inline">
                            <input type="hidden" name="product_id" value="' . $row['id'] . '">
                            <button type="submit" name="add_to_cart" class="text-sm bg-[#D98324] text-white px-3 py-1 rounded hover:bg-[#443627] transition">Add to Cart</button>
                        </form>' : '
                        <span class="text-sm text-red-500 font-medium">Out of Stock</span>') . '
                        <a href="item_details.php?id=' . $row['id'] . '" class="text-sm text-[#D98324] hover:underline">View Details</a>
                    </div>
                </div>
            </div>';
    }
} else {
    echo '<p class="col-span-full text-center text-lg text-gray-500">No items are listed.</p>';
}
?>
</section>

  <!-- Optional: Newsletter / Promo -->
  <section class="bg-[#F2F6D0] py-10 text-center">
    <h2 class="text-2xl font-semibold mb-2">Get 10% off your first order</h2>
    <p class="mb-4">Sign up to our newsletter for exclusive thrift finds!</p>
    <form class="flex justify-center gap-2 max-w-md mx-auto px-4">
      <input type="email" placeholder="Your email" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      <button class="bg-[#D98324] text-white px-4 py-2 rounded-lg hover:bg-[#443627] transition">Subscribe</button>
    </form>
  </section>

  <!-- Footer -->
  <footer class="text-center text-sm text-[#443627]/70 py-6">
    &copy; 2025 Thrifted Threads • Sustainable fashion made easy
  </footer>

</body>
</html>
