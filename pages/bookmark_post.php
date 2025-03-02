<?php
session_start();
include "config.php"; // Pastikan ini benar

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

$data = json_decode(file_get_contents("php://input"), true);

// Jika JSON tidak valid
if (!$data) {
    echo json_encode(["success" => false, "error" => "Invalid JSON input"]);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$post_id = $data['post_id'] ?? null;
$action = $data['action'] ?? null;

// Jika data kurang
if (!$user_id || !$post_id || !$action) {
    echo json_encode(["success" => false, "error" => "Missing parameters"]);
    exit;
}

// Debugging log (cek apakah data diterima)
error_log("User ID: $user_id, Post ID: $post_id, Action: $action");

if ($action === "bookmark") {
    $query = "INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)";
} else {
    $query = "DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $post_id);
$success = $stmt->execute();

// Jika query gagal
if (!$success) {
    error_log("Query error: " . $stmt->error);
    echo json_encode(["success" => false, "error" => "Database error"]);
    exit;
}

// Berhasil
echo json_encode(["success" => true]);
?>
