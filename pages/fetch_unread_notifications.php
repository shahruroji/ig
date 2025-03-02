<?php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo $row['unread_count']; // Kirim jumlah notifikasi belum dibaca
?>
