<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Harap login terlebih dahulu']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$post_id = isset($data['post_id']) ? intval($data['post_id']) : 0;
$action = $data['action'];

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Post ID tidak valid']);
    exit();
}

if ($action === 'like') {
    // Tambahkan like jika belum ada
    $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE id=id");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();

    // Ambil pemilik postingan
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post_owner = $result->fetch_assoc()['user_id'];

    // Tambahkan notifikasi jika bukan like postingan sendiri
    if ($post_owner != $user_id) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, type, post_id) 
                                VALUES (?, ?, 'like', ?) 
                                ON DUPLICATE KEY UPDATE id=id");
        $stmt->bind_param("iii", $post_owner, $user_id, $post_id);
        $stmt->execute();
    }
} elseif ($action === 'unlike') {
    // Hapus like
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();

    // Hapus notifikasi juga
    $stmt = $conn->prepare("DELETE FROM notifications WHERE sender_id = ? AND post_id = ? AND type = 'like'");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    exit();
}

// Ambil jumlah like terbaru
$stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['success' => true, 'like_count' => $row['like_count']]);

$stmt->close();
$conn->close();
?>
