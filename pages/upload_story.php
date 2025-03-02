<?php
session_start();
require 'db.php';


// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan pengguna ke halaman login
    header('Location: ../index.php');
    exit();
}

// Jika sudah login, ambil user_id dari session
$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $time_shown = $_POST['timeShown'];
    $tags = $_POST['tags'];

    // Handle File Upload
    $target_dir = "uploads/stories/";
    $file_name = basename($_FILES["storyImage"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["storyImage"]["tmp_name"], $target_file)) {
        // Calculate Expiry Time
        $expires_at = date('Y-m-d H:i:s', strtotime("+$time_shown hours"));

        // Save Story to Database
        $stmt = $conn->prepare("INSERT INTO stories (user_id, image_path, time_shown, tags, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $target_file, $time_shown, $tags, $expires_at);

        if ($stmt->execute()) {
            header('Location: home.php?success=Story uploaded successfully');
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Failed to upload image.";
    }
}
?>
