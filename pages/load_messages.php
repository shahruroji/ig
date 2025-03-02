<?php
include '../config.php';
session_start();

$current_user_id = $_SESSION['user_id'];
$receiver_id = $_GET['user_id'] ?? null;

if (!$receiver_id) {
    die("Penerima tidak valid.");
}

// Ambil pesan dari database
$query = $conn->prepare("
    SELECT sender_id, message, timestamp 
    FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY timestamp ASC
");
$query->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$query->execute();
$messages = $query->get_result();

while ($row = $messages->fetch_assoc()) {
    $class = ($row['sender_id'] == $current_user_id) ? 'sent' : 'received';
    echo "<div class='message $class'><p>" . htmlspecialchars($row['message']) . "</p></div>";
}
?>
