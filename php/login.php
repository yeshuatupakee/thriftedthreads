<?php
session_start();
include 'db_conn.php'; // Include your DB connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare the query to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // If email found
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $full_name, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Login success
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['role'] = $role; // Store the user's role in the session

            // Redirect based on role
            if ($role == 'admin') {
                header("Location: ../admin/admin_dashboard.php"); // Redirect to the admin dashboard if the role is admin
                exit;
            } else {
                header("Location: homepage.php"); // Redirect to homepage for regular users
                exit;
            }
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Email not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- Display error -->
<?php if (isset($error)): ?>
<script>alert("<?= $error ?>");</script>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Thrifted Threads</title>
  <link rel="icon" href="../images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFDCAB] text-[#443627] min-h-screen flex items-center justify-center">
  <div class="bg-white rounded-2xl shadow-xl px-8 py-10 max-w-md w-full">
    
    <!-- Logo -->
    <div class="text-center mb-6">
      <img src="../images/logo/logo.png" alt="Thrift Hive Logo" class="h-16 mx-auto mb-2">
      <h1 class="text-3xl font-bold">Login</h1>
      <p class="text-sm mt-1 text-[#443627]/80">Welcome back to sustainable fashion ✨</p>
    </div>

    <!-- Login Form -->
    <form action="login.php" method="POST" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-medium mb-1">Email</label>
        <input type="email" id="email" name="email" required
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>
      <div>
        <label for="password" class="block text-sm font-medium mb-1">Password</label>
        <input type="password" id="password" name="password" required
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>
      <div class="flex justify-between items-center text-sm">
        <label class="flex items-center space-x-2">
          <input type="checkbox" class="accent-[#D98324]">
          <span>Remember me</span>
        </label>
        <a href="forgotpass.php" class="text-[#D98324] hover:underline">Forgot password?</a>
      </div>
      <button type="submit"
              class="w-full bg-[#D98324] text-white font-semibold py-3 rounded-lg hover:bg-[#443627] transition duration-300">
        Login
      </button>
      <a href="landingpage.php" class="block w-full px-6 py-3 bg-[#443627] text-white font-semibold rounded-lg hover:bg-[#D98324] transition duration-300">
            ← Back to Home
        </a>
    </form>

    <!-- Divider -->
    <div class="my-6 border-t border-gray-300"></div>

    <!-- Signup Redirect -->
    <p class="text-sm text-center mt-6">Don't have an account?
      <a href="signup.php" class="text-[#D98324] font-medium hover:underline">Sign up</a>
    </p>

    <!-- Footer -->
    <footer class="mt-8 text-xs text-center text-[#443627]/70">
      &copy; 2025 Thrifted Threads • All rights reserved
    </footer>
  </div>
</body>
</html>
