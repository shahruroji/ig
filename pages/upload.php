<?php
session_start();
require '../db_connection.php'; // Ganti dengan file koneksi database Anda

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle unggahan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $user_id = $_SESSION['user_id'];
    $caption = $_POST['caption'];
    $upload_dir = '../uploads/posts/';
    $media_paths = [];
    $type = 'image'; // Default sebagai gambar

    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $file_name = uniqid() . '_' . basename($_FILES['files']['name'][$key]);
        $target_file = $upload_dir . $file_name;

        // Deteksi tipe file berdasarkan ekstensi atau MIME type
        $file_type = mime_content_type($tmp_name);
        if (strpos($file_type, 'video') !== false) {
            $type = 'video';
        }

        // Pindahkan file yang diunggah
        if (move_uploaded_file($tmp_name, $target_file)) {
            $media_paths[] = $target_file;
        }
    }

    // Simpan ke database
    $media_json = json_encode($media_paths);
    $stmt = $conn->prepare("INSERT INTO posts (user_id, caption, media_paths, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $user_id, $caption, $media_json, $type);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Postingan diunggah!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan postingan.']);
    }
}

?>
