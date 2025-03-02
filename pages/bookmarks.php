<?php
session_start();
include '../config.phpp';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil postingan yang telah dibookmark oleh pengguna
$query = $conn->prepare("SELECT posts.* FROM posts 
                         JOIN bookmarks ON posts.id = bookmarks.post_id 
                         WHERE bookmarks.user_id = ? 
                         ORDER BY bookmarks.created_at DESC");
$query->bind_param("i", $user_id);
$query->execute();
$bookmarked_posts = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmarks</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Postingan yang Disimpan</h2>
    <div class="feed-container">
        <?php while ($post = $bookmarked_posts->fetch_assoc()): ?>
            <div class="post">
                <div class="post-header">
                    <img src="<?php echo !empty($post['profile_picture']) ? '../uploads/profile_pictures/' . $post['profile_picture'] : '../uploads/profile_pictures/default-profile.png'; ?>" class="profile-picture">
                    <span class="username"><?php echo htmlspecialchars($post['username']); ?></span>
                </div>

                <?php 
                $media_paths = json_decode($post['media_paths'], true);
                if (is_array($media_paths) && !empty($media_paths)): ?>
                    <?php if (count($media_paths) > 1): ?>
                        <div class="media-slider">
                            <?php foreach ($media_paths as $media): ?>
                                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $media)): ?>
                                    <img src="<?php echo htmlspecialchars($media); ?>" class="post-media">
                                <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $media)): ?>
                                    <video src="<?php echo htmlspecialchars($media); ?>" class="post-media" controls></video>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo htmlspecialchars($media_paths[0]); ?>" class="post-media">
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
