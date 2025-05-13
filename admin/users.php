<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_conn.php';

// Handle the role update
if (isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    // Validate the role
    $allowed_roles = ['user', 'admin']; // Only admin and user
    if (!in_array($new_role, $allowed_roles)) {
        echo "Invalid role!";
        exit();
    }

    // Update the user's role in the database
    $query = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $new_role, $user_id);
    
    if ($stmt->execute()) {
        echo "Role updated successfully!";
        header("Location: users.php"); // Redirect back to the users page
        exit();
    } else {
        echo "Error updating role.";
    }
}

$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users - Thrifted Threads</title>
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
        <button name="logout" class="bg-[#D98324] hover:bg-[#b86112] text-white font-medium px-5 py-2 rounded-lg transition-all duration-200 shadow-sm">Logout</button>
      </form>
    </header>

    <main class="flex-1 p-6">
        <h2 class="text-xl font-semibold text-[#443627] mb-6">Registered Users</h2>

        <div class="overflow-x-auto rounded-lg shadow bg-white">
          <table class="min-w-full divide-y divide-gray-200 table-auto">
            <thead class="bg-[#D98324] text-white">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Profile</th>
                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Full Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Address</th>
                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Registered On</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#443627]">
              <?php if (mysqli_num_rows($result) > 0): ?>
                <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                  <tr class="hover:bg-[#F2F6D0] transition">
                    <td class="px-6 py-4 whitespace-nowrap"><?= $i++; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <img src="../<?= htmlspecialchars($row['profile_picture']); ?>" alt="Profile"
                           class="w-10 h-10 rounded-full object-cover border border-gray-300">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['full_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['email']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['contact_number'] ?? $row['contact']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['address']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                    <form method="POST" action="">
                        <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                        <select name="role" class="bg-white border border-gray-300 text-[#443627] px-3 py-2 rounded-md">
                        <option value="user" <?= $row['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <button type="submit" name="update_role" class="bg-[#D98324] hover:bg-[#b86112] text-white font-medium px-4 py-2 rounded-md mt-2">Update</button>
                    </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= date("M d, Y", strtotime($row['created_at'])); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
    </main>
  </div>
</div>

</body>
</html>
