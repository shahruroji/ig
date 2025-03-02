<?php
require '../db_connection.php';

$post_id = $_GET['post_id'];

$stmt = $conn->prepare("
    SELECT comments.comment, comments.created_at, users.username, users.profile_pic
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE comments.post_id = ?
    ORDER BY comments.created_at DESC
");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode($comments);
?>
