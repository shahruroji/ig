<?php
include '../config.php'; // Koneksi database
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$follower_id = $_SESSION['user_id'];
$following_id = $data['user_id'] ?? 0;

if ($following_id == 0 || $follower_id == $following_id) {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit;
}

// Cek apakah sudah follow
$query = $conn->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ? AND following_id = ?");
$query->bind_param("ii", $follower_id, $following_id);
$query->execute();
$result = $query->get_result();
$isFollowing = $result->fetch_row()[0] > 0;

if ($isFollowing) {
    // Unfollow (hapus dari database)
    $query = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND following_id = ?");
    $query->bind_param("ii", $follower_id, $following_id);
    $query->execute();
    echo json_encode(["success" => true, "following" => false]);
} else {
    // Follow (tambahkan ke database)
    $query = $conn->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)");
    $query->bind_param("ii", $follower_id, $following_id);
    $query->execute();
    echo json_encode(["success" => true, "following" => true]);
}
?>
