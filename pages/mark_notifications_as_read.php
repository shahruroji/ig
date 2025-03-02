<?php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'];

$query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo "success";
?>
