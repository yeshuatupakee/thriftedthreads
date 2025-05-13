<?php
include 'db_conn.php'; // Your DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $items = trim($_POST['item_description']);
    $method = $_POST['method'];
    $contact = trim($_POST['contact']); // Capture contact number from form
    $photo = null;

    // Handle file upload (photo)
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../donation_uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create upload folder if not exists
        }

        // Create a unique file name
        $filename = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $target = $upload_dir . $filename;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target)) {
            $photo = $target; // Store file path in $photo variable
        }
    }

    // Insert donation into DB (including the contact number and photo path)
    $stmt = $conn->prepare("INSERT INTO donations (full_name, email, item_description, donation_method, photo_path, contact) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $items, $method, $photo, $contact);

    if ($stmt->execute()) {
        echo "<script>alert('Thank you for your donation!'); window.location.href='donate.php';</script>";
    } else {
        echo "<script>alert('Error: Donation not submitted. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>