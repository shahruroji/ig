<?php
session_start();
include 'config.php';

if (isset($_POST['comment_id'])) {
    $comment_id = $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];

    // Pastikan komentar milik pengguna yang login sebelum menghapus
    $query = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $query->bind_param("ii", $comment_id, $user_id);

    if ($query->execute()) {
        echo "success"; // Untuk AJAX response
    } else {
        echo "error";
    }
}
?>
