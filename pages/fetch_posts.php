<?php
require '../db_connection.php';

// Query untuk mengambil postingan dengan urutan terbaru
$query = "SELECT posts.*, users.username 
          FROM posts 
          JOIN users ON posts.user_id = users.id 
          ORDER BY posts.created_at DESC";
$result = $conn->query($query);

$posts = [];
while ($row = $result->fetch_assoc()) {
    $row['media_paths'] = json_decode($row['media_paths']); // Decode JSON array
    $posts[] = $row;
}

echo json_encode($posts);
?>
