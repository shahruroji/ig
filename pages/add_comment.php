<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        die("Anda harus login untuk berkomentar.");
    }

    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $comment_text = trim($_POST['comment']);
    $parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : NULL; // Parent ID jika ini balasan

    if (!empty($comment_text)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text, parent_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $post_id, $user_id, $comment_text, $parent_id);
        $stmt->execute();
    }
}

header("Location: home.php");
exit();
?>
