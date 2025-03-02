<?php
session_start();
require '../db_connection.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Ambil story berdasarkan ID
if (isset($_GET['story_id'])) {
    $story_id = intval($_GET['story_id']);
    $stmt = $conn->prepare("SELECT stories.file_path, stories.uploaded_at, users.username 
                            FROM stories 
                            JOIN users ON stories.user_id = users.id 
                            WHERE stories.id = ?");
    $stmt->bind_param('i', $story_id);
    $stmt->execute();
    $story = $stmt->get_result()->fetch_assoc();

    if (!$story) {
        die('Story tidak ditemukan.');
    }
} else {
    die('ID story tidak diberikan.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Story</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #000;
        }
        .story-view {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: #fff;
        }
        .story-content {
            max-width: 90%;
            max-height: 80%;
            margin-bottom: 20px;
        }
        img, video {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="story-view">
        <?php if (preg_match('/\.(mp4|mov|avi)$/i', $story['file_path'])): ?>
            <video class="story-content" controls autoplay>
                <source src="<?php echo $story['file_path']; ?>" type="video/mp4">
                Browser Anda tidak mendukung pemutar video.
            </video>
        <?php else: ?>
            <img class="story-content" src="<?php echo $story['file_path']; ?>" alt="Story">
        <?php endif; ?>
        <p><?php echo htmlspecialchars($story['username']); ?> • 
            <?php echo date('d M Y, H:i', strtotime($story['uploaded_at'])); ?></p>
    </div>
</body>
</html>
