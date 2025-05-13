<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

include 'db_conn.php';

// Dashboard Stats
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_price) as sum FROM orders")->fetch_assoc()['sum'] ?? 0;
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

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
                  header("Location: admin_dashboard.php");
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

    // First delete from cart to satisfy foreign key constraint
    $conn->query("DELETE FROM cart WHERE product_id = $id");

    // Now delete from products
    $conn->query("DELETE FROM products WHERE id = $id");

    header("Location: admin_dashboard.php");
    exit();
}

// Handle Accept/Decline Donation
if (isset($_GET['donation_action']) && isset($_GET['id'])) {
    $donationId = intval($_GET['id']);
    $action = $_GET['donation_action'];

    if (in_array($action, ['accept', 'decline'])) {
        $newStatus = $action === 'accept' ? 'accepted' : 'declined';
        $stmt = $conn->prepare("UPDATE donations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $donationId);
        $stmt->execute();
    }

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
    <style>
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); }
        .modal-content { background-color: white; margin: 10% auto; padding: 20px; border-radius: 10px; width: 80%; max-width: 600px; }
    </style>
</head>
<body class="bg-[#F2F6D0] text-[#443627] min-h-screen px-6 py-8 font-sans">

<header class="flex justify-between items-center mb-10">
    <h1 class="text-3xl font-bold">Thrifted Threads Admin</h1>
    <form method="POST">
        <button name="logout" class="bg-red-500 text-white px-5 py-2 rounded-lg hover:bg-red-600">Logout</button>
    </form>
</header>

<!-- Stats Section -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-white p-6 rounded-xl shadow-md"><h3>Total Products</h3><p class="text-3xl text-[#D98324]"><?php echo $totalProducts; ?></p></div>
    <div class="bg-white p-6 rounded-xl shadow-md"><h3>Total Orders</h3><p class="text-3xl text-[#D98324]"><?php echo $totalOrders; ?></p></div>
    <div class="bg-white p-6 rounded-xl shadow-md"><h3>Total Revenue</h3><p class="text-3xl text-[#D98324]">₱<?php echo number_format($totalRevenue, 2); ?></p></div>
    <div class="bg-white p-6 rounded-xl shadow-md"><h3>Registered Users</h3><p class="text-3xl text-[#D98324]"><?php echo $totalUsers; ?></p></div>
</div>

<!-- Add Product + Donations Side-by-Side -->
<section class="flex flex-col lg:flex-row gap-8 mb-10 max-w-7xl mx-auto">
    
    <!-- Add New Product Form -->
    <div class="bg-white rounded-2xl shadow-lg p-8 flex-1">
    <h2 class="text-2xl font-semibold mb-6 border-b pb-2">Add New Product</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="text" name="name" placeholder="Product Name" required class="w-full px-4 py-2 border rounded">
            <textarea name="description" placeholder="Description" required class="w-full px-4 py-2 border rounded"></textarea>
            <input type="text" name="material" placeholder="Material" class="w-full px-4 py-2 border rounded">
            <input type="text" name="condition" placeholder="Condition" class="w-full px-4 py-2 border rounded">
            <textarea name="care_instructions" placeholder="Care Instructions" class="w-full px-4 py-2 border rounded"></textarea>
            <input type="text" name="fit_style" placeholder="Fit/Style Notes" class="w-full px-4 py-2 border rounded">
            <input type="number" name="price" step="0.01" placeholder="Price" required class="w-full px-4 py-2 border rounded">
            <input type="number" name="stock" placeholder="Stock" required class="w-full px-4 py-2 border rounded">
            <input type="file" name="image" class="w-full px-4 py-2 border rounded" required>
            <button type="submit" name="add" class="bg-[#D98324] text-white px-6 py-2 rounded hover:bg-[#443627]">Add Product</button>
        </form>
    </div>

<!-- Submitted Donations -->
<div class="bg-white rounded-2xl shadow-lg p-8 flex-1">
    <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Submitted Donations</h2>
            <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b">
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Contact</th>
                    <th class="px-4 py-2">Method</th>
                    <th class="px-4 py-2">Photo</th>
                    <th class="px-4 py-2 w-32">Status/Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sortField = $_GET['sort_field'] ?? 'donated_at';
            $sortOrder = $_GET['sort_order'] ?? 'DESC';
            $allowedFields = ['status', 'donated_at'];
            $allowedOrder = ['ASC', 'DESC'];
            
            if (!in_array($sortField, $allowedFields)) $sortField = 'donated_at';
            if (!in_array($sortOrder, $allowedOrder)) $sortOrder = 'DESC';
            
            $donations = $conn->query("SELECT * FROM donations ORDER BY $sortField $sortOrder LIMIT 5");            
            $donations = $conn->query("SELECT * FROM donations ORDER BY donated_at DESC LIMIT 5");
            while ($d = $donations->fetch_assoc()) {
                echo '<tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">' . htmlspecialchars($d['full_name']) . '</td>
                    <td class="px-4 py-2">' . htmlspecialchars($d['email']) . '</td>
                    <td class="px-4 py-2">' . htmlspecialchars($d['contact']) . '</td>
                    <td class="px-4 py-2">' . htmlspecialchars(ucfirst($d['donation_method'])) . '</td>
                    <td class="px-4 py-2">';
                    if (!empty($d['photo_path'])) {
                        echo '<img src="' . htmlspecialchars($d['photo_path']) . '" class="h-10 w-10 object-cover rounded-full">';
                    } else {
                        echo '—';
                    }
                    echo '</td>
                    <td class="px-4 py-2 text-sm">';
                    echo '<form method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="donation_id" value="' . $d['id'] . '">
                    <select name="new_status" class="border px-2 py-1 rounded text-sm">
                        <option value="pending"' . ($d['status'] === 'pending' ? ' selected' : '') . '>Pending</option>
                        <option value="accepted"' . ($d['status'] === 'accepted' ? ' selected' : '') . '>Accepted</option>
                        <option value="declined"' . ($d['status'] === 'declined' ? ' selected' : '') . '>Declined</option>
                    </select>
                    <button type="submit" name="update_donation_status" class="text-sm bg-[#D98324] text-white px-2 py-1 rounded hover:bg-[#443627]">Save</button>
                    </form>';                
                    echo '</td>
                </tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</section>

<!-- Product Listings -->
<section class="max-w-7xl mx-auto">
    <h2 class="text-2xl font-semibold mb-4">Product Listings</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    $result = $conn->query("SELECT * FROM products");
    while ($row = $result->fetch_assoc()) {
      echo '
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
          <img src="' . $row['image'] . '" class="h-48 w-full object-cover" alt="' . $row['name'] . '">
          <div class="p-4">
              <h3 class="text-lg font-semibold">' . $row['name'] . '</h3>
              <p class="text-sm text-gray-600 mb-2">' . $row['description'] . '</p>
              <p class="text-sm text-gray-600 mb-2"><strong>Material:</strong> ' . $row['material'] . '</p>
              <p class="text-sm text-gray-600 mb-2"><strong>Condition:</strong> ' . $row['condition_note'] . '</p>
              <p class="text-sm text-gray-600 mb-2"><strong>Care Instructions:</strong> ' . $row['care_instructions'] . '</p>
              <p class="text-sm text-gray-600 mb-2"><strong>Fit/Style:</strong> ' . $row['fit_style'] . '</p>
              <p class="font-bold text-[#D98324] mb-1">₱' . number_format($row['price'], 2) . '</p>
              <p class="text-sm text-gray-700 mb-3">Stock: ' . $row['stock'] . '</p>
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
                      class="text-blue-600 hover:underline">
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
<div id="editModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 hidden">
    <div class="modal-content">
        <h2 class="text-xl font-semibold mb-4">Edit Product</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id" id="productId">
            <input type="text" name="name" id="productName" placeholder="Product Name" required class="w-full px-4 py-2 border rounded">
            <textarea name="description" id="productDescription" placeholder="Description" required class="w-full px-4 py-2 border rounded"></textarea>
            <input type="text" name="material" id="productMaterial" placeholder="Material" class="w-full px-4 py-2 border rounded">
            <input type="text" name="condition" id="productCondition" placeholder="Condition" class="w-full px-4 py-2 border rounded">
            <textarea name="care_instructions" id="productCareInstructions" placeholder="Care Instructions" class="w-full px-4 py-2 border rounded"></textarea>
            <input type="text" name="fit_style" id="productFitStyle" placeholder="Fit/Style Notes" class="w-full px-4 py-2 border rounded">
            <input type="number" name="price" id="productPrice" step="0.01" placeholder="Price" required class="w-full px-4 py-2 border rounded">
            <input type="number" name="stock" id="productStock" placeholder="Stock" required class="w-full px-4 py-2 border rounded">
            <input type="file" name="image" class="w-full px-4 py-2 border rounded">
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                <button type="submit" name="update" class="px-4 py-2 rounded bg-[#D98324] text-white hover:bg-[#443627]">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, description, material, condition, careInstructions, fitStyle, price, stock) {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('productId').value = id;
    document.getElementById('productName').value = name;
    document.getElementById('productDescription').value = description;
    document.getElementById('productMaterial').value = material;
    document.getElementById('productCondition').value = condition;
    document.getElementById('productCareInstructions').value = careInstructions;
    document.getElementById('productFitStyle').value = fitStyle;
    document.getElementById('productPrice').value = price;
    document.getElementById('productStock').value = stock;
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
</body>
</html>
