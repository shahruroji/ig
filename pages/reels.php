<?php
session_start();
include "../config.php"; // Sesuaikan dengan koneksi database

// Query untuk mengambil reels dan informasi pengguna
$reels = $conn->query("
    SELECT posts.media_paths, users.username, users.profile_picture 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.type = 'video' 
    ORDER BY posts.created_at DESC
");

// Simpan hasil query dalam array
$rows = [];
while ($row = $reels->fetch_assoc()) {
    $rows[] = $row;
}

// Jika tidak ada reels ditemukan
if (empty($rows)) {
    echo "<p>Tidak ada reels ditemukan.</p>";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reels</title>
    <link rel="stylesheet" href="../assets/css/reels.css">
   

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
<?php include '../includes/sidebar.php'; ?>

<?php include '../includes/right_sidebar.php'; ?>



<div class="reels-container">
    <?php foreach ($rows as $reel): ?>
        <?php 
            $media = json_decode($reel['media_paths'], true);
            if (!$media) continue; 
        ?>
        <?php foreach ($media as $video): ?>
            <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $video)): ?>
                <div class="reel">
                    <!-- Video -->
                    <video class="reel-video" src="<?= htmlspecialchars($video) ?>" autoplay loop muted></video>
                    
                    <!-- Overlay untuk username, foto profil, dan tombol mute -->
                    <div class="video-overlay">
                      
                    </div>

                    <!-- Ikon Aksi -->
                    <div class="actions">
                    <div class="profile-info">
                            <img src="<?= !empty($reel['profile_picture']) ? '../uploads/profile_pictures/' . $reel['profile_picture'] : '../images/default_profile.png' ?>">
                        </div>
                        
                        <div class="actions">
                            <button class="mute-btn">🔇</button> <!-- Tombol Mute -->
                        </div>
                        <div class="icon-wrapper">
                            <svg class="icon like-icon" viewBox="0 0 24 24">
                                <path d="M16.792 3.904A4.989 4.989 0 0 1 21.5 9.122c0 3.072-2.652 4.959-5.197 7.222-2.512 2.243-3.865 3.469-4.303 3.752-.477-.309-2.143-1.823-4.303-3.752C5.141 14.072 2.5 12.167 2.5 9.122a4.989 4.989 0 0 1 4.708-5.218 4.21 4.21 0 0 1 3.675 1.941c.84 1.175.98 1.763 1.12 1.763s.278-.588 1.11-1.766a4.17 4.17 0 0 1 3.679-1.938"></path>
                            </svg>
                        </div>

                        <div class="icon-wrapper">
                            <svg class="icon share-icon" viewBox="0 0 24 24">
                                <path d="M18 8a2 2 0 1 0-1.92-2.58l-5.4 2.7a2 2 0 1 0 0 3.76l5.4 2.7A2 2 0 1 0 18 14a2 2 0 1 0-1.92-2.58l-5.4-2.7a2 2 0 1 0 0-3.76l5.4-2.7A2 2 0 1 0 18 8z"></path>
                            </svg>
                        </div>

                        <div class="icon-wrapper">
                            <svg class="icon bookmark-icon" viewBox="0 0 24 24">
                                <path d="M6 2c-1.1 0-2 .9-2 2v18l8-3 8 3V4c0-1.1-.9-2-2-2H6zm12 16l-6-2.73L6 18V4h12v14z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<script >document.addEventListener("DOMContentLoaded", function () {
    let videos = document.querySelectorAll(".reel-video");

    // Intersection Observer untuk mendeteksi video yang terlihat di layar
    let observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            let video = entry.target;

            if (entry.isIntersecting) {
                video.play();  // Putar video yang terlihat
            } else {
                video.pause(); // Hentikan video yang tidak terlihat
                video.currentTime = 0; // Reset video ke awal
            }
        });
    }, { threshold: 0.5 }); // 50% video harus terlihat sebelum diputar

    videos.forEach(video => {
        observer.observe(video);
    });

    // Event untuk Mute dan Unmute
    document.querySelectorAll(".reel").forEach(reel => {
        let video = reel.querySelector(".reel-video");
        let muteBtn = reel.querySelector(".mute-btn");

        muteBtn.addEventListener("click", function () {
            if (video.muted) {
                video.muted = false;
                muteBtn.textContent = "🔊"; // Unmute
            } else {
                video.muted = true;
                muteBtn.textContent = "🔇"; // Mute
            }
        });
    });
});

</script>

</body>
</html>
