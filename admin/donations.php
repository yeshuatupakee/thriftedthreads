<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/login.php");
    exit();
}
include 'db_conn.php';

// Handle Logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../php/login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['donation_id'])) {
        $action = $_POST['action'];
        $donationId = intval($_POST['donation_id']);

        if (in_array($action, ['accepted', 'declined', 'pending'])) {
            $update = $conn->prepare("UPDATE donations SET status = ? WHERE id = ?");
            $update->bind_param("si", $action, $donationId);
            $update->execute();
        }
    }

    // Handle donation deletion
    if (isset($_POST['delete_donation_id'])) {
        $deleteId = intval($_POST['delete_donation_id']);

        // First, try to get the photo path
        $stmt = $conn->prepare("SELECT photo_path FROM donations WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $photoPath = $row['photo_path'];
            if (!empty($photoPath) && file_exists($photoPath)) {
                unlink($photoPath); // Delete the photo file
            }
        }

        // Now delete the donation from the database
        $deleteStmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
        $deleteStmt->bind_param("i", $deleteId);
        $deleteStmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donations | Thrifted Threads</title>
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
                <?php
        // Handle Accept/Decline actions
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['action']) && isset($_POST['donation_id'])) {
                $action = $_POST['action'];
                $donationId = intval($_POST['donation_id']);

            if (in_array($action, ['accepted', 'declined', 'pending'])) {
                    $update = $conn->prepare("UPDATE donations SET status = ? WHERE id = ?");
                    $update->bind_param("si", $action, $donationId);
                    $update->execute();
                }
            }
        }

        // Fetch donations
        $donation_query = "SELECT * FROM donations ORDER BY donated_at DESC";
        $donation_result = $conn->query($donation_query);
        ?>

        <h2 class="text-2xl font-bold text-[#443627] mb-6">Donation Requests</h2>

        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full table-auto text-sm text-left text-gray-700">
                <thead class="bg-[#D98324] text-white uppercase">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Contact</th>
                        <th class="px-6 py-3">Item</th>
                        <th class="px-6 py-3">Method</th>
                        <th class="px-6 py-3">Photo</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $donation_result->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-[#f9f9f9]">
                        <td class="px-6 py-4"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['contact']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['item_description']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['donation_method']) ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $photoFolder = '';
                            $photoPath = $row['photo_path'];
                            if (!empty($photoPath)) {
                                $fullPhotoPath = $photoFolder . $photoPath;
                                ?>
                                <button onclick="openModal('<?= htmlspecialchars($fullPhotoPath) ?>')"
                                        class="text-white bg-blue-500 hover:bg-blue-600 text-xs px-3 py-1 rounded">
                                    View Photo
                                </button>
                            <?php
                            } else {
                                ?>
                                <span class="text-gray-400 italic">No photo</span>
                            <?php
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4"><?= date("M d, Y", strtotime($row['donated_at'])) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                <?= $row['status'] === 'accepted' ? 'bg-green-100 text-green-700' : 
                                    ($row['status'] === 'declined' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-row items-center gap-3">
                            <!-- Dropdown (left side) -->
                            <form method="POST" class="flex flex-col gap-1">
                                <input type="hidden" name="donation_id" value="<?= $row['id'] ?>">
                                <select name="action" class="border rounded px-2 py-1 text-sm mb-1">
                                    <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="accepted" <?= $row['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                    <option value="declined" <?= $row['status'] === 'declined' ? 'selected' : '' ?>>Declined</option>
                                </select>

                            <!-- Vertical button stack (right side) -->
                            <div class="flex flex-col gap-1">
                                <!-- Update button -->
                                <form method="POST">
                                    <input type="hidden" name="donation_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded w-full">
                                        Update
                                    </button>
                                </form>

                                <!-- Delete button -->
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this donation?');">
                                    <input type="hidden" name="delete_donation_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded w-full">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
                <!-- Photo Modal -->
        <div id="photoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden max-w-sm w-full shadow-lg relative">
            <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900 text-xl">&times;</button>
            <img id="modalImage" src="" alt="Donation Photo" class="w-full h-auto object-contain p-4">
        </div>
        </div>
    </main>
  </div>
</div>
<script>
function openModal(imageSrc) {
    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('modalImage');
    modalImg.src = imageSrc;
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('photoModal').classList.add('hidden');
    document.getElementById('modalImage').src = '';
}
</script>
</body>
</html>