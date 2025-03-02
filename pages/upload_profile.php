<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    die("Anda harus login terlebih dahulu.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "../uploads/profile_pictures/";
    $file_name = $user_id . "_" . time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validasi ukuran & format
    if ($_FILES["profile_picture"]["size"] > 5000000) {
        die("Ukuran gambar terlalu besar (maks 5MB).");
    }
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        die("Format tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.");
    }

    // Pindahkan file ke folder uploads
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        $update = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $update->bind_param("si", $file_name, $user_id);
        $update->execute();
        $update->close();

        header("Location: profil.php");
    } else {
        die("Gagal mengunggah gambar.");
    }
}
?>
s