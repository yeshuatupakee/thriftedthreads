<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_conn.php';

// Image upload handler
function handleImageUpload($file) {
    $target_dir = "uploads/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $uniqueName = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $uniqueName;

    if ($file["size"] > 10 * 1024 * 1024) return false;
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) return false;
    if (!getimagesize($file["tmp_name"])) return false;

    return move_uploaded_file($file["tmp_name"], $target_file) ? $target_file : false;
}

// Handle Add or Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'] ?? '';
  $desc = $_POST['description'] ?? '';
  $material = $_POST['material'] ?? '';
  $condition = $_POST['condition'] ?? '';
  $care_instructions = $_POST['care_instructions'] ?? '';
  $fit_style = $_POST['fit_style'] ?? '';
  $price = $_POST['price'] ?? 0;
  $stock = $_POST['stock'] ?? 0;
  $id = $_POST['id'] ?? null;

  if (isset($_POST['update_donation_status']) && isset($_POST['donation_id']) && isset($_POST['new_status'])) {
    $donationId = intval($_POST['donation_id']);
    $newStatus = $_POST['new_status'];

    if (in_array($newStatus, ['pending', 'accepted', 'declined'])) {
        $stmt = $conn->prepare("UPDATE donations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $donationId);
        $stmt->execute();
    }

    header("Location: admin_dashboard.php");
    exit();
}

  if (empty($name) || empty($desc) || empty($price)) {
      echo "Please fill in all fields.";
  } else {
      $imagePath = null;

      if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
          $imagePath = handleImageUpload($_FILES["image"]);
          if (!$imagePath) echo "Image upload failed or invalid.<br>";
      }

      if (isset($_POST['update']) && $id) {
          // Update product
          if ($imagePath) {
              $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, material = ?, condition_note = ?, care_instructions = ?, fit_style = ?, price = ?, stock = ?, image = ? WHERE id = ?");
              $stmt->bind_param("sssssdsdsi", $name, $desc, $material, $condition, $care_instructions, $fit_style, $price, $stock, $imagePath, $id);
          } else {
              $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, material = ?, condition_note = ?, care_instructions = ?, fit_style = ?, price = ?, stock = ? WHERE id = ?");
              $stmt->bind_param("sssssdssi", $name, $desc, $material, $condition, $care_instructions, $fit_style, $price, $stock, $id);
          }

          if ($stmt->execute()) {
              header("Location: admin_dashboard.php");
              exit();
          } else {
              echo "Error updating product: " . $stmt->error;
          }

      } elseif (isset($_POST['add'])) {
          // Add new product
          if (!$imagePath) {
              echo "Image is required.";
          } else {
              $stmt = $conn->prepare("INSERT INTO products (name, description, material, condition_note, care_instructions, fit_style, price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
              $stmt->bind_param("ssssssdis", $name, $desc, $material, $condition, $care_instructions, $fit_style, $price, $stock, $imagePath);
            if ($stmt->execute()) {
                $successMessage = "✅ Product added successfully!";
            } else {
                $errorMessage = "❌ Error adding product: " . $stmt->error;
            }
          }
      }
  }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // First delete from cart to satisfy foreign key constraint
    $conn->query("DELETE FROM cart WHERE product_id = $id");

    // Now delete from products
    $conn->query("DELETE FROM products WHERE id = $id");

    header("Location: admin_dashboard.php");
    exit();
}

// Handle Logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Thrifted Threads</title>
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
<!-- Add Product Section -->
<div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-4xl mx-auto mt-6">
    <h2 class="text-3xl font-bold text-[#443627] mb-6 border-b pb-3">🛍️ Add New Product</h2>
    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
            <input type="text" name="name" placeholder="Vintage Jacket" required class="w-full px-4 py-2 border rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#D98324]">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Material</label>
            <input type="text" name="material" placeholder="Denim, Cotton" class="w-full px-4 py-2 border rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#D98324]">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
            <input type="text" name="condition" placeholder="Like New / Gently Used" class="w-full px-4 py-2 border rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#D98324]">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fit/Style Notes</label>
            <input type="text" name="fit_style" placeholder="Oversized Fit" class="w-full px-4 py-2 border rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#D98324]">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" placeholder="A cool vintage jacket perfect for layering..." required class="w-full px-4 py-2 border rounded-2xl h-24 focus:outline-none focus:ring-2 focus:ring-[#D98324]"></textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Care Instructions</label>
            <textarea name="care_instructions" placeholder="Hand wash cold, hang dry." class="w-full px-4 py-2 border rounded-2xl h-20 focus:outline-none focus:ring-2 focus:ring-[#D98324]"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Price (₱)</label>
            <input type="number" name="price" step="0.01" placeholder="799.00" required class="w-full px-4 py-2 border rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#D98324]">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
            <input type="number" name="stock" placeholder="10" required class="w-full px-4 py-2 border rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#D98324]">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
            <input type="file" name="image" class="w-full px-4 py-2 border rounded-2xl bg-white focus:outline-none focus:ring-2 focus:ring-[#D98324]" required>
        </div>

        <div class="md:col-span-2 flex justify-end">
            <button type="submit" name="add" class="bg-[#D98324] hover:bg-[#443627] text-white font-semibold px-6 py-2 rounded-2xl transition-all duration-200">
                ➕ Add Product
            </button>
        </div>
    </form>
</div>
    </main>
  </div>
</div>
</body>
</html>