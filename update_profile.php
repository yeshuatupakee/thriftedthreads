<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$contact_number = $_POST['contact_number'] ?? '';
$address = $_POST['address'];

$profile_picture_path = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $target_dir = "images/profile/";
    $filename = uniqid() . "_" . basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . $filename;
    move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
    $profile_picture_path = $target_file;
}

// Prepare the update query
if ($profile_picture_path) {
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, address = ?, profile_picture = ?, contact_number = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $full_name, $email, $address, $profile_picture_path, $contact_number, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, contact_number = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $full_name, $email, $contact_number, $address, $user_id);
}

// Execute and redirect
if ($stmt->execute()) {
    header("Location: profile.php");
    exit();
} else {
    echo "Error updating profile: " . $stmt->error;
}
?>
