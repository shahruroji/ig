<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$current_user_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message'] ?? '');

if (!$receiver_id || empty($message)) {
    die("Data tidak valid.");
}

// Simpan pesan ke database
$query = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$query->bind_param("iis", $current_user_id, $receiver_id, $message);
$query->execute();

if ($query->affected_rows > 0) {
    echo "Pesan terkirim.";
} else {
    echo "Gagal mengirim pesan.";
}
?>
