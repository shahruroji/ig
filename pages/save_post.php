<?php
session_start();
require 'config.php'; // Koneksi ke database

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Harap login"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// Cek apakah postingan sudah disimpan
$check_query = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ?");
$check_query->bind_param("ii", $user_id, $post_id);
$check_query->execute();
$check_result = $check_query->get_result();

if ($check_result->num_rows > 0) {
    // Hapus jika sudah disimpan
    $delete_query = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
    $delete_query->bind_param("ii", $user_id, $post_id);
    $delete_query->execute();
    echo json_encode(["success" => true, "saved" => false]);
} else {
    // Simpan postingan
    $insert_query = $conn->prepare("INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)");
    $insert_query->bind_param("ii", $user_id, $post_id);
    $insert_query->execute();
    echo json_encode(["success" => true, "saved" => true]);
}
?>
