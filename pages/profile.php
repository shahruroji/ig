<?php
include '../config.php'; // Koneksi database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil username dari URL
$username = $_GET['username'] ?? '';

// Ambil data pengguna dari database
$queryUser = $conn->prepare("SELECT id, username, profile_picture, bio FROM users WHERE username = ?");
$queryUser->bind_param("s", $username);
$queryUser->execute();
$resultUser = $queryUser->get_result();
$user = $resultUser->fetch_assoc();

if (!$user) {
    die("Pengguna tidak ditemukan");
}

$user_id = $user['id'];

// Hitung jumlah postingan
$queryPosts = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$queryPosts->bind_param("i", $user_id);
$queryPosts->execute();
$resultPosts = $queryPosts->get_result();
$totalPosts = $resultPosts->fetch_row()[0];

// Hitung followers dan following
$queryFollowers = $conn->prepare("SELECT COUNT(*) FROM followers WHERE following_id = ?");
$queryFollowers->bind_param("i", $user_id);
$queryFollowers->execute();
$resultFollowers = $queryFollowers->get_result();
$totalFollowers = $resultFollowers->fetch_row()[0];

$queryFollowing = $conn->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ?");
$queryFollowing->bind_param("i", $user_id);
$queryFollowing->execute();
$resultFollowing = $queryFollowing->get_result();
$totalFollowing = $resultFollowing->fetch_row()[0];

// Cek apakah pengguna sudah mengikuti
$current_user_id = $_SESSION['user_id'] ?? 0;
$queryFollowCheck = $conn->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ? AND following_id = ?");
$queryFollowCheck->bind_param("ii", $current_user_id, $user_id);
$queryFollowCheck->execute();
$resultFollowCheck = $queryFollowCheck->get_result();
$isFollowing = $resultFollowCheck->fetch_row()[0] > 0;

// Ambil semua postingan pengguna
$queryUserPosts = $conn->prepare("SELECT media_paths FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$queryUserPosts->bind_param("i", $user_id);
$queryUserPosts->execute();
$resultUserPosts = $queryUserPosts->get_result();
$posts = $resultUserPosts->fetch_all(MYSQLI_ASSOC);

$queryReels = $conn->prepare("
    SELECT media_paths FROM posts 
    WHERE user_id = ? 
    AND (media_paths LIKE '%mp4%' OR media_paths LIKE '%mov%' OR media_paths LIKE '%avi%')
");
$queryReels->bind_param("i", $user_id);
$queryReels->execute();
$resultReels = $queryReels->get_result();
$reels = $resultReels->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@<?= htmlspecialchars($user['username']) ?> | Instagram</title>
    <link rel="stylesheet" href="../profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>
<div class="profile-container">
    <div class="profile-header">
        <img class="profile-picture" src="<?= !empty($user['profile_picture']) ? '../uploads/profile_pictures/' . $user['profile_picture'] : '../images/default_profile.png' ?>" alt="Profile Picture">
        <div class="profile-info">
            <h2><?= htmlspecialchars($user['username']) ?></h2>
            <div class="profile-stats">
                <span><strong><?= $totalPosts ?></strong> posts</span>
                <span><strong><?= $totalFollowers ?></strong> <a href="#" class="show-followers" data-user="<?= $user_id ?>">followers</a></span>
                <span><strong><?= $totalFollowing ?></strong> <a href="#" class="show-following" data-user="<?= $user_id ?>">following</a></span>
            </div>
            <?php if ($current_user_id && $current_user_id !== $user_id): ?>
                <button class="follow-btn" data-user="<?= $user_id ?>">
                    <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
                </button>
                <a href="chat.php?user_id=<?= $user_id ?>" class="message-btn">Kirim Pesan</a>


            <?php endif; ?>
        </div>
    </div>

    <div class="post-tabs">
    <button class="tab-btn active" data-target="posts">📸 Postingan</button>
    <button class="tab-btn" data-target="reels">🎥 Reels</button>
</div>

<div class="post-gallery active" data-target="posts">
    <?php foreach ($posts as $post): ?>
        <?php 
            $media_paths = json_decode(stripslashes($post['media_paths']), true) ?? [];
            foreach ($media_paths as $media):
                $ext = pathinfo($media, PATHINFO_EXTENSION);
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <img class='post-item' src="<?= htmlspecialchars($media) ?>" alt='Post'>
                <?php elseif (in_array($ext, ['mp4', 'mov', 'avi'])): ?>
                    <video class='post-item' controls>
                        <source src="<?= htmlspecialchars($media) ?>" type='video/<?= $ext ?>'>
                    </video>
                <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<div class="post-gallery" data-target="reels">
    <?php foreach ($reels as $reel): ?>
        <?php 
            $media_paths = json_decode($reel['media_paths'], true);
            foreach ($media_paths as $media):
                $ext = pathinfo($media, PATHINFO_EXTENSION);
                if (in_array($ext, ['mp4', 'mov', 'avi'])): ?>
                    <video class='reel-item' controls>
                        <source src="<?= htmlspecialchars($media) ?>" type='video/<?= $ext ?>'>
                    </video>
                <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>


</div>
<div id="popup-container" class="popup-container">
    <div class="popup-content">
        <span class="close-popup">&times;</span>
        <h3 id="popup-title"></h3>
        <ul id="popup-list"></ul>
    </div>
</div>
<script>
document.querySelector('.follow-btn')?.addEventListener('click', function() {
    let userId = this.getAttribute('data-user');
    fetch('../actions/follow.php', {
        method: 'POST',
        body: JSON.stringify({ user_id: userId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.innerText = data.following ? 'Unfollow' : 'Follow';
        }
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const tabButtons = document.querySelectorAll(".tab-btn");
    const galleries = document.querySelectorAll(".post-gallery");

    tabButtons.forEach(button => {
        button.addEventListener("click", function () {
            // Reset semua tab
            tabButtons.forEach(btn => btn.classList.remove("active"));
            galleries.forEach(gallery => gallery.classList.remove("active"));

            // Tambahkan kelas aktif hanya pada tab & galeri yang dipilih
            this.classList.add("active");
            let targetGallery = document.querySelector(`.post-gallery[data-target="${this.dataset.target}"]`);
            if (targetGallery) targetGallery.classList.add("active");
        });
    });
});


</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelector('.follow-btn')?.addEventListener('click', function () {
        let userId = this.getAttribute('data-user');
        let button = this;

        fetch('../action/follow.php', {
            method: 'POST',
            body: JSON.stringify({ user_id: userId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.innerText = data.following ? 'Unfollow' : 'Follow';
            }
        });
    });
});

</script>
<script>document.getElementById('send-button').addEventListener('click', function() {
    let message = document.getElementById('message-input').value;
    let receiver_id = 2; // ID pengguna yang menerima pesan (harus dinamis)

    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `receiver_id=${receiver_id}&message=${encodeURIComponent(message)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('message-input').value = '';
            loadMessages(receiver_id);
        }
    });
});

function loadMessages(receiver_id) {
    fetch(`fetch_messages.php?receiver_id=${receiver_id}`)
        .then(response => response.json())
        .then(messages => {
            let chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = '';
            messages.forEach(msg => {
                let div = document.createElement('div');
                div.textContent = msg.message;
                div.className = msg.sender_id == <?= $_SESSION['user_id'] ?> ? 'sent' : 'received';
                chatBox.appendChild(div);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        });
}

setInterval(() => loadMessages(2), 2000); // Auto-refresh setiap 2 detik
</script>
<script>document.addEventListener("DOMContentLoaded", function () {
    function showPopup(type, userId) {
        fetch(`../pages/followers.php?user_id=${userId}&type=${type}`)
            .then(response => response.json())
            .then(data => {
                let popupTitle = document.getElementById("popup-title");
                let popupList = document.getElementById("popup-list");
                let popupContainer = document.getElementById("popup-container");

                popupList.innerHTML = ""; // Kosongkan list

                popupTitle.textContent = type === "followers" ? "Followers" : "Following";

                if (data.length > 0) {
                    data.forEach(user => {
                        let li = document.createElement("li");
                        li.innerHTML = `<img src="../uploads/profile_pictures/${user.profile_picture || 'default_profile.png'}" width="30" style="border-radius:50%"> 
                                        ${user.username}`;
                        popupList.appendChild(li);
                    });
                } else {
                    popupList.innerHTML = "<p>Belum ada data</p>";
                }

                popupContainer.classList.add("show"); // Tampilkan popup
            });
    }

    document.querySelectorAll(".show-followers").forEach(el => {
        el.addEventListener("click", function (e) {
            e.preventDefault();
            showPopup("followers", this.getAttribute("data-user"));
        });
    });

    document.querySelectorAll(".show-following").forEach(el => {
        el.addEventListener("click", function (e) {
            e.preventDefault();
            showPopup("following", this.getAttribute("data-user"));
        });
    });

    // Tutup popup saat tombol close diklik
    document.querySelector(".close-popup")?.addEventListener("click", function () {
        document.getElementById("popup-container").classList.remove("show");
    });

    // Tutup popup jika klik di luar area popup
    document.getElementById("popup-container")?.addEventListener("click", function (e) {
        if (e.target === this) {
            this.classList.remove("show");
        }
    });
});

</script>
</body>
</html>
