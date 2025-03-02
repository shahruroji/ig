<?php
session_start();
include 'db.php'; // Pastikan file koneksi database dimasukkan

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? null;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

// Cek apakah sudah dibookmark sebelumnya
$query = $conn->prepare("SELECT * FROM bookmarks WHERE user_id = ? AND post_id = ?");
$query->bind_param("ii", $user_id, $post_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    // Jika sudah dibookmark, hapus bookmark
    $delete_query = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
    $delete_query->bind_param("ii", $user_id, $post_id);
    $delete_query->execute();

    echo json_encode(['success' => true, 'bookmarked' => false]);
} else {
    // Jika belum dibookmark, tambahkan ke database
    $insert_query = $conn->prepare("INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)");
    $insert_query->bind_param("ii", $user_id, $post_id);
    $insert_query->execute();

    echo json_encode(['success' => true, 'bookmarked' => true]);
}
?>
