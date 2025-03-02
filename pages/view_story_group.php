<?php
require '../db_connection.php';

if (isset($_GET['group_id'])) {
    $group_id = intval($_GET['group_id']);
    $stmt = $conn->prepare("
        SELECT file_path, 
               CASE 
                   WHEN file_path LIKE '%.mp4%' THEN 'video' 
                   ELSE 'image' 
               END AS type 
        FROM stories 
        WHERE group_id = ?
    ");
    $stmt->bind_param('i', $group_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $stories = [];
    while ($row = $result->fetch_assoc()) {
        $stories[] = $row;
    }
    echo json_encode(['stories' => $stories]);
} else {
    echo json_encode(['error' => 'Invalid group_id']);
}
