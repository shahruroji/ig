<?php
session_start();
require 'config.php'; // Pastikan file koneksi database sudah dibuat

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];
$post_id = $data['post_id'];

// Cek apakah postingan sudah di-bookmark
$check_query = $conn->prepare("SELECT * FROM bookmarks WHERE user_id = ? AND post_id = ?");
$check_query->execute([$user_id, $post_id]);

if ($check_query->rowCount() > 0) {
    // Hapus bookmark jika sudah ada
    $delete_query = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
    $delete_query->execute([$user_id, $post_id]);
    echo json_encode(["status" => "removed"]);
} else {
    // Tambahkan bookmark jika belum ada
    $insert_query = $conn->prepare("INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)");
    $insert_query->execute([$user_id, $post_id]);
    echo json_encode(["status" => "added"]);
}
?>
