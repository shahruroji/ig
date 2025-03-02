<?php
include '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_GET['user_id'] ?? 0;
$type = $_GET['type'] ?? '';

if (!$user_id || !in_array($type, ['followers', 'following'])) {
    echo json_encode(['error' => 'Data tidak valid']);
    exit();
}

if ($type === 'followers') {
    $query = $conn->prepare("
        SELECT users.id, users.username, users.profile_picture 
        FROM followers 
        JOIN users ON followers.follower_id = users.id 
        WHERE followers.following_id = ?
    ");
} else {
    $query = $conn->prepare("
        SELECT users.id, users.username, users.profile_picture 
        FROM followers 
        JOIN users ON followers.following_id = users.id 
        WHERE followers.follower_id = ?
    ");
}
// Simpan notifikasi follow
$notif_query = "INSERT INTO notifications (user_id, sender_id, type) VALUES (?, ?, 'follow')";
$notif_stmt = $conn->prepare($notif_query);
$notif_stmt->bind_param("ii", $following_id, $user_id);
$notif_stmt->execute();


$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data);
