<?php
session_start();
require '../config.php';

if (!isset($conn)) {
    die("Koneksi tidak ditemukan. Pastikan config.php sudah dipanggil.");
}

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data pengguna
$stmt = $conn->prepare("SELECT username, first_name, last_name, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Foto profil default
$profile_picture = !empty($user['profile_picture']) ? "../uploads/profile_pictures/" . $user['profile_picture'] : "../uploads/profile_pictures/default-profile.jpg";

// Ambil jumlah postingan, pengikut, dan mengikuti
$post_count = $conn->query("SELECT COUNT(*) as total FROM posts WHERE user_id = $user_id")->fetch_assoc()['total'] ?? 0;
$follower_count = $conn->query("SELECT COUNT(*) as total FROM followers WHERE following_id = $user_id")->fetch_assoc()['total'] ?? 0;
$following_count = $conn->query("SELECT COUNT(*) as total FROM followers WHERE follower_id = $user_id")->fetch_assoc()['total'] ?? 0;

// Ambil daftar pengikut
$followers = $conn->query("SELECT users.username, users.profile_picture FROM followers JOIN users ON followers.follower_id = users.id WHERE followers.following_id = $user_id");

// Ambil daftar yang diikuti
$followings = $conn->query("SELECT users.username, users.profile_picture FROM followers JOIN users ON followers.following_id = users.id WHERE followers.follower_id = $user_id");
// Query untuk mengambil semua postingan (gambar & video)
$posts = $conn->query("SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC");


// Query untuk mengambil hanya video (khusus reels)
$reels = $conn->query("
    SELECT * FROM posts 
    WHERE user_id = $user_id 
    AND (media_paths LIKE '%.mp4%' OR media_paths LIKE '%.webm%' OR media_paths LIKE '%.ogg%') 
    ORDER BY created_at DESC
");

// Ambil postingan tersimpan
$saved = $conn->query("
    SELECT posts.media_paths FROM bookmarks 
    JOIN posts ON bookmarks.post_id = posts.id 
    WHERE bookmarks.user_id = $user_id
");
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username']) ?> - Instagram</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css"> 
    <script>
        function showTab(tab) {
            document.getElementById('posts').style.display = (tab === 'posts') ? 'grid' : 'none';
            document.getElementById('reels').style.display = (tab === 'reels') ? 'grid' : 'none';
            document.getElementById('saved').style.display = (tab === 'saved') ? 'grid' : 'none';

            document.getElementById('btn-posts').classList.toggle('active', tab === 'posts');
            document.getElementById('btn-reels').classList.toggle('active', tab === 'reels');
            document.getElementById('btn-saved').classList.toggle('active', tab === 'saved');
        }
            function showPopup(type) {
            document.getElementById(type + '-popup').style.display = 'flex';
        }
        function closePopup(type) {
            document.getElementById(type + '-popup').style.display = 'none';
        }
        
    </script>
</head>
<body>
    
    <div class="container">
        <!-- Header Profil -->
        <div class="profile-header">
            <div class="profile-picture">
                <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture">
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['username']) ?></h2>
                <p>
                    <strong><?= $post_count ?></strong> Posting |
                    <strong onclick="showPopup('followers')" style="cursor:pointer;"> <?= $follower_count ?> Pengikut</strong> |
                    <strong onclick="showPopup('following')" style="cursor:pointer;"> <?= $following_count ?> Mengikuti</strong>
                </p>
                <form action="upload_profile.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="profile_picture" accept="image/*">
                    <button type="submit">Ganti Foto Profil</button>
                </form>
            </div>
        </div>
        <div id="followers-popup" class="popup-overlay">
            <div class="popup-content">
                <span class="popup-close" onclick="closePopup('followers')">&times;</span>
                <h3>Pengikut</h3>
                <?php if ($followers->num_rows > 0): ?>
                    <?php while ($follower = $followers->fetch_assoc()): ?>
                <p>
                    <img class="profile-picture" 
                        src="<?= !empty($follower['profile_picture']) ? '../uploads/profile_pictures/' . $follower['profile_picture'] : '../images/default_profile.png' ?>" 
                        alt="Profile Picture"> 
                    <?= htmlspecialchars($follower['username']) ?>
                </p>
            <?php endwhile; ?>
                <?php else: ?>
                    <p>Tidak ada pengikut</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="following-popup" class="popup-overlay">
            <div class="popup-content">
                <span class="popup-close" onclick="closePopup('following')">&times;</span>
                <h3>Mengikuti</h3>
                <?php if ($followings->num_rows > 0): ?>
                    <?php while ($following = $followings->fetch_assoc()): ?>
                <p>
                    <img class="profile-picture" 
                        src="<?= !empty($following['profile_picture']) ? '../uploads/profile_pictures/' . $following['profile_picture'] : '../images/default_profile.png' ?>" 
                        alt="Profile Picture"> 
                    <?= htmlspecialchars($following['username']) ?>
                </p>
            <?php endwhile; ?>
                <?php else: ?>
                    <p>Tidak mengikuti siapapun</p>
                <?php endif; ?>
            </div>
        </div>

        <?php include '../includes/sidebar.php'; ?>
        <?php include '../includes/right_sidebar.php'; ?> 
        <!-- Navigasi Tab -->
        <div class="profile-tabs">
            <button id="btn-posts" class="active" onclick="showTab('posts')"><i class="fas fa-th"></i> Postingan</button>
            <button id="btn-reels" onclick="showTab('reels')"><i class="fas fa-video"></i> Reels</button>
            <button id="btn-saved" onclick="showTab('saved')"><i class="fas fa-bookmark"></i> Tersimpan</button>
        </div>
        
        <div id="posts" class="post-grid">
    <?php while ($post = $posts->fetch_assoc()): ?>
        <?php 
            $media = json_decode($post['media_paths'], true); 
            if (!$media) continue; // Jika gagal decode, skip
        ?>
        <?php foreach ($media as $file): ?>
            <div class="post-item">
                <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $file)): ?>
                    <video src="<?= htmlspecialchars($file) ?>" controls></video>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($file) ?>" alt="Post">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endwhile; ?>
</div>

<!-- Grid Reels -->
<div id="reels" class="post-grid" style="display: none;">
    <?php while ($reel = $reels->fetch_assoc()): ?>
        <?php 
            $media = json_decode($reel['media_paths'], true); 
            if (!$media || !is_array($media)) continue; // Jika gagal decode atau bukan array, skip
        ?>
        <?php foreach ($media as $video): ?>
            <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $video)): ?>
                <div class="post-item">
                    <video src="<?= htmlspecialchars($video) ?>" controls></video>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endwhile; ?>
</div>



       <!-- Grid Tersimpan -->
<div id="saved" class="post-grid" style="display: none;">
    <?php while ($save = $saved->fetch_assoc()): ?>
        <?php 
            $media = json_decode($save['media_paths'], true);
            if (!$media) continue; // Skip jika gagal decode
        ?>
        <?php foreach ($media as $file): ?>
            <div class="post-item">
                <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $file)): ?>
                    <video src="<?= htmlspecialchars($file) ?>" controls></video>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($file) ?>" alt="Saved Post">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endwhile; ?>
</div>

    </div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("postModal");
        const modalImg = document.getElementById("modalImage");
        const closeBtn = document.querySelector(".close");

        // Event listener untuk semua gambar postingan
        document.querySelectorAll(".post-item img").forEach(img => {
            img.addEventListener("click", function () {
                modal.style.display = "flex";
                modalImg.src = this.src; // Set gambar modal sesuai yang diklik
            });
        });

        // Tutup modal saat tombol close diklik
        closeBtn.addEventListener("click", function () {
            modal.style.display = "none";
        });

        // Tutup modal saat klik di luar gambar
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
    });
</script>

</html>
