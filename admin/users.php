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

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id_to_delete = $_POST['user_id'];

    // Delete the user from the database
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $user_id_to_delete);
    
    if ($stmt->execute()) {
        echo "User deleted successfully!";
        header("Location: users.php"); // Redirect back to the users page
        exit();
    } else {
        echo "Error deleting user.";
    }
}

$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Handle Logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../php/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users | Thrifted Threads</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this user?");
        }
    </script>
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
        <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0h-2v6h2"/></svg>
        Dashboard
      </a>
      <a href="listed_products.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
        <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        Listed Products
      </a>
      <a href="add_product.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
        <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Product
      </a>
      <a href="orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
        <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v4H3zM5 7v13h14V7"/></svg>
        Orders
      </a>
      <a href="donations.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
        <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4z"/></svg>
        Donations
      </a>
      <a href="users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#F2F6D0] hover:text-[#D98324] transition">
        <svg class="w-5 h-5 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
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
    <h2 class="text-2xl font-bold text-[#443627] mb-6">Registered Users</h2>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg w-full">
        <table class="min-w-full table-auto text-sm text-left text-gray-700">
            <thead class="bg-[#D98324] text-white uppercase">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">Profile</th>
                    <th class="px-6 py-3">Full Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Contact</th>
                    <th class="px-6 py-3">Address</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Registered On</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="border-b hover:bg-[#f9f9f9]">
                            <td class="px-6 py-4"><?= $i++; ?></td>
                            <td class="px-6 py-4">
                                <img src="../<?= htmlspecialchars($row['profile_picture']); ?>" alt="Profile"
                                     class="w-10 h-10 rounded-full object-cover border border-gray-300">
                            </td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['full_name']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['email']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['contact_number'] ?? $row['contact']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['address']); ?></td>
                            <td class="px-6 py-4">
                                <form method="POST" action="">
                                    <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                    <select name="role" class="bg-white border border-gray-300 text-[#443627] px-3 py-2 rounded-md">
                                        <option value="user" <?= $row['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <button type="submit" name="update_role" class="bg-[#D98324] hover:bg-[#b86112] text-white font-medium px-4 py-2 rounded-md mt-2">Update Role</button>
                                </form>
                            </td>
                            <td class="px-6 py-4"><?= date("F j, Y, g:i a", strtotime($row['created_at'])); ?></td>
                            <td class="px-6 py-4">
                                <form method="POST" action="" onsubmit="return confirmDelete();">
                                    <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                    <button type="submit" name="delete_user" class="bg-red-500 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-md mt-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center px-6 py-4">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</div>

</div>
</body>
</html>
