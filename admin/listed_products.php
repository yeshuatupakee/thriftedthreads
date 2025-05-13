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
              header("Location: listed_products.php");
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
                  header("Location: listed_products.php");
                  exit();
              } else {
                  echo "Error adding product: " . $stmt->error;
              }
          }
      }
  }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

$conn->query("DELETE FROM order_details WHERE product_id = $id");
$conn->query("DELETE FROM cart WHERE product_id = $id");
$conn->query("DELETE FROM products WHERE id = $id");


    header("Location: listed_products.php");
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
<!-- Product Listings -->
<section class="max-w-7xl mx-auto px-4 py-8">
  <h2 class="text-3xl font-bold text-gray-800 mb-6">Product Listings</h2>
  
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    $result = $conn->query("SELECT * FROM products");
    while ($row = $result->fetch_assoc()) {
      echo '
      <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform hover:scale-[1.02]">
          <img src="' . $row['image'] . '" class="h-48 w-full object-cover" alt="' . htmlspecialchars($row['name']) . '">
          <div class="p-5">
              <h3 class="text-lg font-semibold text-gray-800 mb-1">' . htmlspecialchars($row['name']) . '</h3>
              <p class="text-sm text-gray-600 mb-2">' . htmlspecialchars($row['description']) . '</p>
              <ul class="text-sm text-gray-600 space-y-1 mb-3">
                  <li><strong>Material:</strong> ' . htmlspecialchars($row['material']) . '</li>
                  <li><strong>Condition:</strong> ' . htmlspecialchars($row['condition_note']) . '</li>
                  <li><strong>Care:</strong> ' . htmlspecialchars($row['care_instructions']) . '</li>
                  <li><strong>Fit/Style:</strong> ' . htmlspecialchars($row['fit_style']) . '</li>
              </ul>
              <p class="text-lg font-bold text-[#D98324] mb-1">₱' . number_format($row['price'], 2) . '</p>
              <p class="text-sm text-gray-700 mb-4">Stock: ' . (int)$row['stock'] . '</p>
              <div class="flex justify-between text-sm">
                  <a href="?delete=' . $row['id'] . '" onclick="return confirm(\'Delete this product?\')" class="text-red-500 hover:underline">Delete</a>
                  <button 
                      onclick="openEditModal(' . 
                          $row['id'] . ', \'' . 
                          addslashes($row['name']) . '\', \'' . 
                          addslashes($row['description']) . '\', \'' . 
                          addslashes($row['material']) . '\', \'' . 
                          addslashes($row['condition_note']) . '\', \'' . 
                          addslashes($row['care_instructions']) . '\', \'' . 
                          addslashes($row['fit_style']) . '\', ' . 
                          $row['price'] . ', ' . 
                          $row['stock'] . 
                      ')" 
                      class="text-indigo-600 hover:underline">
                      Edit
                  </button>
              </div>
          </div>
      </div>';      
    }
    ?>
  </div>
</section>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center px-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
    <h2 class="text-2xl font-semibold mb-4 text-[#D98324]">Edit Product</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="id" id="productId">
      <div>
        <label class="block text-sm font-medium mb-1" for="productName">Product Name</label>
        <input type="text" name="name" id="productName" required class="w-full px-4 py-2 border rounded-md focus:ring focus:ring-[#D98324]">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1" for="productDescription">Description</label>
        <textarea name="description" id="productDescription" required class="w-full px-4 py-2 border rounded-md focus:ring focus:ring-[#D98324]"></textarea>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1" for="productMaterial">Material</label>
          <input type="text" name="material" id="productMaterial" class="w-full px-4 py-2 border rounded-md">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1" for="productCondition">Condition</label>
          <input type="text" name="condition" id="productCondition" class="w-full px-4 py-2 border rounded-md">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1" for="productCare">Care Instructions</label>
        <textarea name="care_instructions" id="productCare" class="w-full px-4 py-2 border rounded-md"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1" for="productFit">Fit Style</label>
        <input type="text" name="fit_style" id="productFit" class="w-full px-4 py-2 border rounded-md">
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1" for="productPrice">Price (₱)</label>
          <input type="number" name="price" id="productPrice" step="0.01" class="w-full px-4 py-2 border rounded-md">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1" for="productStock">Stock</label>
          <input type="number" name="stock" id="productStock" class="w-full px-4 py-2 border rounded-md">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1" for="productImage">Change Image (optional)</label>
        <input type="file" name="image" id="productImage" class="w-full px-4 py-2 border rounded-md">
      </div>
      <div class="flex justify-end gap-3 mt-6">
        <button type="button" onclick="closeEditModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">Cancel</button>
        <button type="submit" name="update" class="bg-[#D98324] hover:bg-[#b86112] text-white px-6 py-2 rounded-md">Update</button>
      </div>
    </form>
    <button onclick="closeEditModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
  </div>
</div>
    </main>
  </div>
</div>
<script>
function openEditModal(id, name, description, material, condition, care, fit, price, stock) {
  document.getElementById('editModal').classList.remove('hidden');
  document.getElementById('productId').value = id;
  document.getElementById('productName').value = name;
  document.getElementById('productDescription').value = description;
  document.getElementById('productMaterial').value = material;
  document.getElementById('productCondition').value = condition;
  document.getElementById('productCare').value = care;
  document.getElementById('productFit').value = fit;
  document.getElementById('productPrice').value = price;
  document.getElementById('productStock').value = stock;
}

function closeEditModal() {
  document.getElementById('editModal').classList.add('hidden');
}
</script>

</body>
</html>