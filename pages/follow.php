<?php
include '../config.php';

session_start();

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'] ?? 0;
$target_id = $data['user_id'] ?? 0;
$action = $data['action'] ?? '';

if (!$user_id || !$target_id || ($action !== 'follow' && $action !== 'unfollow')) {
    echo json_encode(["success" => false]);
    exit;
}

if ($action === 'follow') {
    $stmt = $conn->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)");
} else {
    $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND following_id = ?");
}

$stmt->bind_param("ii", $user_id, $target_id);
$success = $stmt->execute();
echo json_encode(["success" => $success]);
