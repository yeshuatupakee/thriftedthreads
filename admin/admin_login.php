<?php
session_start();
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Dummy login (you can switch this to DB lookup)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login - Thrifted Threads</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFDCAB] flex items-center justify-center h-screen">
    <form method="POST" class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm space-y-4">
        <h2 class="text-xl font-bold text-center">Admin Login</h2>
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-sm"><?= $error ?></p>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Username" required class="w-full px-3 py-2 border rounded">
        <input type="password" name="password" placeholder="Password" required class="w-full px-3 py-2 border rounded">
        <button type="submit" name="login" class="w-full bg-[#D98324] text-white py-2 rounded hover:bg-[#443627]">Login</button>
    </form>
</body>
</html>