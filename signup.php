<?php
session_start();
include 'db_conn.php'; // Include your DB connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm'];

    // Validate password and confirm password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, contact) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $contact);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                // Flag successful signup for JavaScript to show the alert
                $_SESSION['signup_success'] = true;
                header("Location: signup.php"); // Refresh the page to show the popup
                exit;
            } else {
                $error = "There was an issue creating your account. Please try again.";
            }
        }

        $stmt->close();
    }
    $conn->close();
}
?>

<!-- Display error -->
<?php if (isset($error)): ?>
<script>alert("<?= $error ?>");</script>
<?php endif; ?>

<!-- Check if signup is successful and trigger pop-up -->
<?php if (isset($_SESSION['signup_success']) && $_SESSION['signup_success'] == true): ?>
<script>
    alert("Signup successful! Please log in.");
    window.location.href = "login.php"; // Redirect to login page
</script>
<?php
    // Unset the session flag after handling it
    unset($_SESSION['signup_success']);
endif;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Thrifted Threads</title>
  <link rel="icon" href="images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFDCAB] text-[#443627] min-h-screen flex items-center justify-center">
  <div class="bg-white rounded-2xl shadow-xl px-8 py-10 max-w-md w-full">
    
    <!-- Logo -->
    <div class="text-center mb-6">
      <img src="images/logo/logo.png" alt="Thrift Hive Logo" class="h-16 mx-auto mb-2">
      <h1 class="text-3xl font-bold">Create an Account</h1>
      <p class="text-sm mt-1 text-[#443627]/80">Join the hive and start thrifting sustainably 🐝</p>
    </div>

    <!-- Signup Form -->
    <form action="signup.php" method="POST" class="space-y-4">
      <div>
        <label for="name" class="block text-sm font-medium mb-1">Full Name</label>
        <input type="text" id="name" name="name" required
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>
      <div>
        <label for="email" class="block text-sm font-medium mb-1">Email</label>
        <input type="email" id="email" name="email" required
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>
      <div>
        <label for="contact" class="block text-sm font-medium mb-1">Contact Number</label>
        <input type="text" id="contact" name="contact" required
              class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>
      <div>
        <label for="password" class="block text-sm font-medium mb-1">Password</label>
        <input type="password" id="password" name="password" required
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>
      <div>
        <label for="confirm" class="block text-sm font-medium mb-1">Confirm Password</label>
        <input type="password" id="confirm" name="confirm" required
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D98324]">
      </div>
      <button type="submit"
              class="w-full bg-[#D98324] text-white font-semibold py-3 rounded-lg hover:bg-[#443627] transition duration-300">
        Sign Up
      </button>
        <a href="landingpage.php" class="block w-full px-6 py-3 bg-[#443627] text-white font-semibold rounded-lg hover:bg-[#D98324] transition duration-300">
                ← Back to Home
            </a>
    </form>

    <!-- Divider -->
    <div class="my-6 border-t border-gray-300"></div>

    <!-- Social Signup -->
    <div class="text-center space-y-3">
      <p class="text-sm text-[#443627]/80">Or sign up with</p>
      <div class="flex justify-center space-x-4">
        <button class="px-4 py-2 border border-[#443627] text-[#443627] rounded-lg hover:bg-[#D98324] hover:text-white transition">
          Google
        </button>
        <button class="px-4 py-2 border border-[#443627] text-[#443627] rounded-lg hover:bg-[#D98324] hover:text-white transition">
          Facebook
        </button>
      </div>
    </div>

    <!-- Redirect to Login -->
    <p class="text-sm text-center mt-6">Already have an account?
      <a href="login.php" class="text-[#D98324] font-medium hover:underline">Login</a>
    </p>

    <!-- Footer -->
    <footer class="mt-8 text-xs text-center text-[#443627]/70">
      &copy; 2025 Thrifted Threads • All rights reserved
    </footer>
  </div>
</body>
</html>
