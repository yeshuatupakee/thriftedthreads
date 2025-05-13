<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db_conn.php';

// Dashboard Stats
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_price) as sum FROM orders")->fetch_assoc()['sum'] ?? 0;
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

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
    <title>Admin Dashboard | Thrifted Threads</title>
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

    <!-- Dashboard Stats -->
    <main class="flex-1 p-6">
    <section class="flex flex-col gap-6 mb-10">
      <!-- Total Products -->
      <div class="bg-gradient-to-br from-[#F2F6D0] to-white p-8 rounded-2xl shadow-lg border border-[#e6e6e6]">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base uppercase tracking-wide text-gray-500">Total Products</h3>
            <p class="text-5xl font-extrabold text-[#D98324] mt-2"><?= $totalProducts ?></p>
          </div>
          <div class="bg-[#D98324]/10 p-4 rounded-full">
            <svg class="w-7 h-7 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20 13V5a2 2 0 00-2-2H6a2 2 0 00-2 2v8m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Total Orders -->
      <div class="bg-gradient-to-br from-[#F2F6D0] to-white p-8 rounded-2xl shadow-lg border border-[#e6e6e6]">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base uppercase tracking-wide text-gray-500">Total Orders</h3>
            <p class="text-5xl font-extrabold text-[#D98324] mt-2"><?= $totalOrders ?></p>
          </div>
          <div class="bg-[#D98324]/10 p-4 rounded-full">
            <svg class="w-7 h-7 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 10h11M9 21V3m9 4h3m-3 4h3m-3 4h3"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Total Revenue -->
      <div class="bg-gradient-to-br from-[#F2F6D0] to-white p-8 rounded-2xl shadow-lg border border-[#e6e6e6]">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base uppercase tracking-wide text-gray-500">Estimated Total Revenue</h3>
            <p class="text-5xl font-extrabold text-[#D98324] mt-2">₱<?= number_format($totalRevenue, 2) ?></p>
          </div>
          <div class="bg-[#D98324]/10 p-4 rounded-full">
            <svg class="w-7 h-7 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 8c-1.657 0-3 1.343-3 3 0 .795.312 1.516.818 2.037M12 16v2m0-2c1.657 0 3-1.343 3-3 0-.795-.312-1.516-.818-2.037M12 16v-2m0-8v2"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Registered Users -->
      <div class="bg-gradient-to-br from-[#F2F6D0] to-white p-8 rounded-2xl shadow-lg border border-[#e6e6e6]">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base uppercase tracking-wide text-gray-500">Registered Users</h3>
            <p class="text-5xl font-extrabold text-[#D98324] mt-2"><?= $totalUsers ?></p>
          </div>
          <div class="bg-[#D98324]/10 p-4 rounded-full">
            <svg class="w-7 h-7 text-[#D98324]" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 4a4 4 0 00-3-1.38"/>
            </svg>
          </div>
        </div>
      </div>
    </section>
    </main>
  </div>
</div>
</body>
</html>