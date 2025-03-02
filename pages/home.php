<?php
session_start();


// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

require '../db_connection.php';
$stmt = $conn->prepare("SELECT posts.id, posts.media_paths, posts.caption, posts.created_at, users.username, users.profile_picture,
                        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count,
                        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS is_liked
                        FROM posts
                        JOIN users ON posts.user_id = users.id
                        ORDER BY posts.created_at DESC");

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$posts = $stmt->get_result();



function time_elapsed_string($datetime, $full = false) {
    date_default_timezone_set('Asia/Jakarta'); // Atur zona waktu ke Indonesia
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = [
        'tahun' => $diff->y,
        'bulan' => $diff->m,
        'hari' => $diff->d,
        'jam' => $diff->h,
        'menit' => $diff->i,
        'detik' => $diff->s,
    ];

    foreach ($string as $key => &$value) {
        if ($value) {
            $value .= " $key";
        } else {
            unset($string[$key]);
        }
    }

    return $string ? implode(', ', array_slice($string, 0, ($full ? count($string) : 1))) . ' yang lalu' : 'Baru saja';
}


// Proses upload story
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['story_file'], $_POST['story_duration'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['story_file'];
    $duration = (int)$_POST['story_duration']; // Durasi dalam jam
    $upload_dir = '../uploads/stories/';
    $file_name = uniqid() . '_' . basename($file['name']);
    $target_file = $upload_dir . $file_name;
    $expiration_time = date('Y-m-d H:i:s', strtotime("+$duration hours")); // Waktu kedaluwarsa

    // Periksa dan unggah file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Simpan ke database
        $stmt = $conn->prepare("INSERT INTO stories (user_id, file_path, expiration_time, uploaded_at) VALUES (?, ?, ?, NOW())");

        $stmt->bind_param('iss', $user_id, $target_file, $expiration_time);
        $stmt->execute();
        
    } else {
        $error = "Gagal mengunggah file.";
    }
}


// Ambil stories dari pengguna sendiri dan akun yang diikuti
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT stories.id, stories.user_id, stories.file_path, users.username, users.profile_picture 
    FROM stories 
    JOIN users ON stories.user_id = users.id 
    LEFT JOIN followers ON followers.following_id = users.id AND followers.follower_id = ?
    WHERE stories.expiration_time > NOW() 
    AND (stories.user_id = ? OR followers.follower_id IS NOT NULL) 
    ORDER BY stories.uploaded_at ASC
");
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$stories_by_user = [];
while ($story = $result->fetch_assoc()) {
    $stories_by_user[$story['user_id']]['username'] = $story['username'];
    $stories_by_user[$story['user_id']]['profile_picture'] = $story['profile_picture'];
    $stories_by_user[$story['user_id']]['stories'][] = $story['file_path'];
}

// Simpan data stories di SESSION agar tidak hilang ketika pindah halaman
$_SESSION['stories_by_user'] = $stories_by_user;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagramm - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css"> 
    
</head>
<body>
    <div class="container">
        
        <?php include '../includes/sidebar.php'; ?> 
        <?php include '../includes/right_sidebar.php'; ?> 
        <div class="main-content">
            <div class="upload-form">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="file" name="story_file" accept="image/*,video/*" required>
                    <label for="story_duration">Durasi Story:</label>
                    <select name="story_duration" id="story_duration" required>
                        <option value="1">1 Jam</option>
                        <option value="12">12 Jam</option>
                        <option value="24">24 Jam</option>
                    </select>
                    <button type="submit">Unggah Story</button>
                </form>
            </div>

            <div class="stories">
            <?php foreach ($stories_by_user as $user_id => $user_stories): ?>
                <div class="story-container">


    <div class="story-avatar-wrapper" onclick="openStoryModal(<?php echo htmlspecialchars(json_encode($user_stories['stories'])); ?>)">
        <img src="<?php echo !empty($user_stories['profile_picture']) ? '../uploads/profile_pictures/' . $user_stories['profile_picture'] : '../images/default_profile.png'; ?>" class="story-avatar">
    </div>
    <span class="story-username"><?php echo htmlspecialchars($user_stories['username']); ?></span>
</div>


<?php endforeach; ?>
</div>


<div class="upload-section">
    <form id="upload-form" enctype="multipart/form-data">
        <label for="upload-files">Unggah Foto/Video:</label>
        <input type="file" id="upload-files" name="files[]" multiple accept="image/*,video/*" required>
        <textarea name="caption" placeholder="Tambahkan caption..." required></textarea>
        <button type="submit">Upload</button>
    </form>
</div>

<div class="feed-container">
    <!-- Tabs -->
   <div id="for-you" class="feed-tab active">
    <div class="feed-container">
    <div id="for-you" class="feed-tab active">
    <div class="feed">
    <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="post">
            <!-- Profile Picture & Username -->
            <div class="post-header">
    <!-- Link ke profil pengguna untuk gambar profil -->
    <a href="/ig/pages/<?= htmlspecialchars($post['username']) ?>">
        <img src="<?php echo !empty($post['profile_picture']) ? '../uploads/profile_pictures/' . $post['profile_picture'] : '../uploads/profile_pictures/default-profile.png'; ?>" class="profile-picture">
    </a>

    <!-- Link ke profil pengguna untuk username -->
    <a href="/ig/pages/<?= htmlspecialchars($post['username']) ?>" class="profile-link">
        <?= htmlspecialchars($post['username']) ?> <!-- Hapus @ -->
    </a>

    <span class="post-time">
        <?php echo isset($post['created_at']) ? time_elapsed_string($post['created_at']) : 'Waktu tidak tersedia'; ?>
    </span>
</div>



           <!-- Decode JSON media_paths -->
<?php 
$media_paths = json_decode($post['media_paths'], true);

// Pastikan media_paths adalah array yang valid
if (!is_array($media_paths)) {
    $media_paths = [];
}
?>

<!-- Jika lebih dari satu media, tampilkan sebagai slider -->
<?php if (!empty($media_paths) && count($media_paths) > 1): ?>
    <div class="media-slider">
        <?php foreach ($media_paths as $media): ?>
            <?php if (is_string($media) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $media)): ?>
                <img src="<?php echo htmlspecialchars($media); ?>" class="post-media">
            <?php elseif (is_string($media) && preg_match('/\.(mp4|webm|ogg)$/i', $media)): ?>
                <video src="<?php echo htmlspecialchars($media); ?>" class="post-media" controls></video>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

<!-- Jika hanya satu media -->
<?php elseif (!empty($media_paths) && isset($media_paths[0]) && is_string($media_paths[0])): ?>
    <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $media_paths[0])): ?>
        <img src="<?php echo htmlspecialchars($media_paths[0]); ?>" class="post-media">
    <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $media_paths[0])): ?>
        <video src="<?php echo htmlspecialchars($media_paths[0]); ?>" class="post-media" controls></video>
    <?php endif; ?>
<?php endif; ?>

<div class="caption">
                    <?php echo htmlspecialchars($post['caption']); ?>
                </div>
                    <!-- Actions (Like, Comment, Share, Save) -->
                    <div class="actions">
                        <div class="like-container">
                                <button class="like-btn <?php echo ($post['is_liked'] ? 'liked' : ''); ?>" data-post-id="<?php echo $post['id']; ?>">
                                    <i class="fa<?php echo ($post['is_liked'] ? 's' : 'r'); ?> fa-heart"></i>
                                </button>
                                <span class="like-count"><?php echo $post['like_count']; ?></span>
                            </div>
                        <button class="share-btn">
                            <i class="fa-regular fa-paper-plane"></i>
                        </button>
                        <?php
                      

                        $user_id = $_SESSION['user_id'];
                        $query = "SELECT post_id FROM bookmarks WHERE user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $bookmarked_posts = [];
                        while ($row = $result->fetch_assoc()) {
                            $bookmarked_posts[] = $row['post_id'];
                        }
                        ?>

                        <button class="save-btn <?php echo in_array($post['id'], $bookmarked_posts) ? 'saved' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                            <i class="fa<?php echo in_array($post['id'], $bookmarked_posts) ? 's' : 'r'; ?> fa-bookmark"></i>
                        </button>

                    </div>

                    <!-- Likes & Caption -->
                    <div class="comments-section">
    <div class="comments-list">
        <?php
        $post_id = $post['id'];

        // Hitung total komentar
        $count_query = $conn->prepare("SELECT COUNT(*) AS total FROM comments WHERE post_id = ?");
        $count_query->bind_param("i", $post_id);
        $count_query->execute();
        $count_result = $count_query->get_result();
        $total_comments = $count_result->fetch_assoc()['total'];

        // Ambil 2 komentar terbaru
        $comments_query = $conn->prepare("
            SELECT comments.*, users.username 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE post_id = ? 
            ORDER BY comments.created_at ASC 
            LIMIT 2");
        $comments_query->bind_param("i", $post_id);
        $comments_query->execute();
        $result = $comments_query->get_result();

        while ($comment = $result->fetch_assoc()):
        ?>
           <p data-comment-id="<?php echo $comment['id']; ?>">
    <strong><?php echo htmlspecialchars($comment['username']); ?></strong> 
    <?php echo htmlspecialchars($comment['comment_text']); ?>
    <?php if ($comment['user_id'] == $user_id): ?>
        <span class="delete-comment" data-comment-id="<?php echo $comment['id']; ?>" style="color: red; cursor: pointer;">Hapus</span>
    <?php endif; ?>
</p>

            <?php endwhile; ?>

        <!-- Jika lebih dari 2 komentar, tampilkan tombol "Lihat semua komentar" -->
        <?php if ($total_comments > 2): ?>
    <span class="view-all-comments" data-post-id="<?php echo $post_id; ?>" style="cursor: pointer; color: #007bff;">
        Lihat semua (<?php echo $total_comments; ?>) komentar
    </span>
<?php endif; ?>

    </div>

    <!-- Comment Form -->
    <form class="comment-form" method="POST" action="add_comment.php">
        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
        <input type="text" name="comment" placeholder="Tulis komentar..." required>
        <button type="submit">Kirim</button>
    </form>
    
</div>


                </div>
            <?php endwhile; ?>
        </div>
        <p id="for-you-empty" style="display: none;">Belum ada postingan untuk Anda.</p>
    </div>
</div>
<?php if (!empty($post['media_paths']) && count($post['media_paths']) > 1): ?>
    <div class="media-slider">
        <?php foreach ($post['media_paths'] as $media): ?>
            <img src="<?php echo $media; ?>" class="slide">
        <?php endforeach; ?>
    </div>
<?php else: ?>
    
<?php endif; ?>



    
<div id="story-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.8); justify-content: center; align-items: center;" onclick="closeModal(event)">
<div id="story-content" style="width: 360px; height: 640px; position: relative; background-color: black;" onclick="event.stopPropagation()">
    <div id="progress-bar-container" style="position: absolute; top: 5px; left: 10px; right: 10px; height: 4px; display: flex; gap: 5px;"></div>
    <button onclick="closeModal()" style="position: absolute; top: 10px; right: 10px; background: none; border: none; color: white; font-size: 24px;">&times;</button>
    <img id="story-image" style="width: 100%; height: 100%; object-fit: cover; display: none;">
    <video id="story-video" style="width: 100%; height: 100%; object-fit: cover; display: none;" autoplay ></video>
</div>

</div>

<div class="comment-modal" id="comment-modal" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div id="comments-container"></div>
    </div>
</div>

</body>
<script>
    function showTab(tabId) {
    // Hapus class active dari semua tombol tab
    document.querySelectorAll('.tab-btn').forEach((btn) => btn.classList.remove('active'));
    // Hapus class active dari semua tab
    document.querySelectorAll('.feed-tab').forEach((tab) => tab.classList.remove('active'));

    // Tambahkan class active ke tombol tab yang diklik
    document.querySelector(`button[onclick="showTab('${tabId}')"]`).classList.add('active');
    // Tambahkan class active ke tab yang sesuai
    document.getElementById(tabId).classList.add('active');
}

    document.getElementById('upload-form').addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    fetch('upload.php', {
        method: 'POST',
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.status === 'success') {
                alert(data.message);
                e.target.reset();
                loadPosts(); // Panggil fungsi untuk memperbarui feed
            } else {
                alert(data.message);
            }
        })
        .catch((error) => console.error('Error:', error));
});

let stories = [];
let currentIndex = 0;
let storyTimer;
let progressBars = [];

function openStoryModal(storyArray) {
    stories = storyArray;
    currentIndex = 0;
    document.getElementById("story-modal").style.display = "flex";

    // Buat progress bar untuk setiap story
    const progressContainer = document.getElementById("progress-bar-container");
    progressContainer.innerHTML = ''; // Reset progress bar
    progressBars = stories.map(() => {
        const barWrapper = document.createElement("div");
        barWrapper.classList.add("progress-bar");

        const barFill = document.createElement("div");
        barFill.classList.add("progress-bar-fill");

        barWrapper.appendChild(barFill);
        progressContainer.appendChild(barWrapper);
        return barFill;
    });

    showStory();
}

function showStory() {
    if (currentIndex >= stories.length) {
        closeModal();
        return;
    }

    let storyPath = stories[currentIndex];
    let storyImage = document.getElementById("story-image");
    let storyVideo = document.getElementById("story-video");

    // Reset progress bar
    progressBars.forEach((bar, index) => {
        bar.style.width = index < currentIndex ? "100%" : "0%";
    });

    // Hentikan video sebelumnya
    storyVideo.pause();
    storyVideo.currentTime = 0;

    let duration = 10000; // Default 10 detik

    if (storyPath.match(/\.(mp4|mov|avi)$/i)) {
        storyImage.style.display = "none";
        storyVideo.style.display = "block";
        storyVideo.src = storyPath;
        storyVideo.play();

        // Gunakan durasi video jika tersedia
        storyVideo.onloadedmetadata = function () {
            duration = (storyVideo.duration * 1000) || 10000;
            startProgress(duration);
        };
    } else {
        storyVideo.style.display = "none";
        storyImage.style.display = "block";
        storyImage.src = storyPath;
        startProgress(duration);
    }

    function startProgress(duration) {
        let startTime = Date.now();

        function updateProgressBar() {
            let elapsedTime = Date.now() - startTime;
            let progress = Math.min(elapsedTime / duration, 1);
            progressBars[currentIndex].style.width = `${progress * 100}%`;

            if (progress < 1) {
                requestAnimationFrame(updateProgressBar);
            } else {
                currentIndex++;
                showStory();
            }
        }

        updateProgressBar();
        clearTimeout(storyTimer);
        storyTimer = setTimeout(() => {
            currentIndex++;
            showStory();
        }, duration);
    }
}


function closeModal() {
    document.getElementById("story-modal").style.display = "none";
    clearTimeout(storyTimer);

    // Hentikan video yang sedang diputar
    let storyVideo = document.getElementById("story-video");
    storyVideo.pause();
    storyVideo.currentTime = 0; // Kembalikan ke awal agar tidak ada suara yang tertinggal
    storyVideo.src = ""; // Kosongkan src agar suara tidak tersisa
}



document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function () {
            const postId = this.getAttribute('data-post-id');
            const likeCountElem = this.nextElementSibling;
            const isLiked = this.classList.contains('liked');
            const action = isLiked ? 'unlike' : 'like';

            // Update tampilan UI terlebih dahulu
            this.classList.toggle('liked');
            this.innerHTML = `<i class="${isLiked ? 'fa-regular' : 'fa-solid'} fa-heart"></i>`;
            likeCountElem.textContent = isLiked ? parseInt(likeCountElem.textContent) - 1 : parseInt(likeCountElem.textContent) + 1;

            // Kirim perubahan ke server
            fetch('like_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId, action: action }),
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Jika gagal, kembalikan ke keadaan awal
                    this.classList.toggle('liked');
                    this.innerHTML = `<i class="${isLiked ? 'fa-solid' : 'fa-regular'} fa-heart"></i>`;
                    likeCountElem.textContent = isLiked ? parseInt(likeCountElem.textContent) + 1 : parseInt(likeCountElem.textContent) - 1;
                    alert('Terjadi kesalahan. Coba lagi.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.classList.toggle('liked');
                this.innerHTML = `<i class="${isLiked ? 'fa-solid' : 'fa-regular'} fa-heart"></i>`;
                likeCountElem.textContent = isLiked ? parseInt(likeCountElem.textContent) + 1 : parseInt(likeCountElem.textContent) - 1;
            });
        });
    });
});





// Load posts on page load
loadPosts();

</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
    // Tampilkan form balasan ketika tombol "Balas" diklik
    $(document).on("click", ".reply-btn", function () {
        $(this).next(".reply-form").toggle();
    });

    // Kirim balasan komentar dengan AJAX
    $(document).on("submit", ".reply-form", function (e) {
        e.preventDefault();

        var form = $(this);
        var postData = form.serialize();

        $.ajax({
            url: "add_comment.php",
            type: "POST",
            data: postData,
            success: function (response) {
                var data = JSON.parse(response);
                if (data.success) {
                    // Refresh komentar setelah balasan ditambahkan
                    var postId = form.find("input[name='post_id']").val();
                    loadComments(postId);
                } else {
                    alert("Gagal mengirim balasan!");
                }
            },
            error: function () {
                alert("Terjadi kesalahan. Coba lagi!");
            }
        });
    });

    // Fungsi untuk memuat semua komentar
    function loadComments(postId) {
        $.ajax({
            url: "load_comments.php",
            type: "POST",
            data: { post_id: postId },
            success: function (data) {
                $("#comments-container").html(data);
                $("#comment-modal").fadeIn();
            }
        });
    }

    // Klik tombol lihat komentar untuk membuka modal
    $(document).on("click", ".view-all-comments", function () {
        var postId = $(this).data("post-id");
        loadComments(postId);
    });

    // Tutup modal
    $(document).on("click", ".close-modal", function () {
        $("#comment-modal").fadeOut();
    });
});

$(document).ready(function () {
    // Tampilkan form balasan ketika tombol "Balas" diklik
    $(document).on("click", ".reply-btn", function () {
        $(this).next(".reply-form").toggle();
    });

    // Kirim balasan komentar dengan AJAX
    $(document).on("submit", ".reply-form", function (e) {
        e.preventDefault();

        var form = $(this);
        var postData = form.serialize();

        $.ajax({
            url: "add_comment.php",
            type: "POST",
            data: postData,
            success: function (response) {
                var data = JSON.parse(response);
                if (data.success) {
                    // Refresh komentar setelah balasan ditambahkan
                    var postId = form.find("input[name='post_id']").val();
                    loadComments(postId);
                } else {
                    alert("Gagal mengirim balasan!");
                }
            },
            error: function () {
                alert("Terjadi kesalahan. Coba lagi!");
            }
        });
    });

    // Fungsi untuk memuat semua komentar
    function loadComments(postId) {
        $.ajax({
            url: "load_comments.php",
            type: "POST",
            data: { post_id: postId },
            success: function (data) {
                $("#comments-container").html(data);
                $("#comment-modal").fadeIn();
            }
        });
    }

    // Klik tombol lihat komentar untuk membuka modal
    $(document).on("click", ".view-all-comments", function () {
        var postId = $(this).data("post-id");
        loadComments(postId);
    });

    // Tutup modal
    $(document).on("click", ".close-modal", function () {
        $("#comment-modal").fadeOut();
    });
});
document.addEventListener("DOMContentLoaded", function () {
    // Tangkap semua tombol "Balas"
    document.querySelectorAll(".reply-btn").forEach(button => {
        button.addEventListener("click", function () {
            let commentId = this.getAttribute("data-comment-id");
            let replyForm = this.closest(".comment").querySelector(".reply-form");

            // Tampilkan atau sembunyikan form balasan
            if (replyForm.style.display === "none" || replyForm.style.display === "") {
                replyForm.style.display = "block";
            } else {
                replyForm.style.display = "none";
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".delete-comment").forEach(button => {
        button.addEventListener("click", function () {
            let commentId = this.getAttribute("data-comment-id");
            let commentElement = this.closest("p"); // Ambil elemen <p> tempat komentar

            if (confirm("Apakah Anda yakin ingin menghapus komentar ini?")) {
                fetch("delete_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "comment_id=" + commentId
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "success") {
                        commentElement.remove(); // Hapus dari tampilan
                    } else {
                        alert("Gagal menghapus komentar!");
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".save-btn").forEach(button => {
        button.addEventListener("click", function () {
            let postId = this.getAttribute("data-post-id");
            let isSaved = this.classList.contains("saved");
            let action = isSaved ? "unbookmark" : "bookmark";
            let icon = this.querySelector("i");

            console.log("Klik bookmark:", postId, action); // Debug log

            this.classList.toggle("saved");
            icon.classList.toggle("fa-solid");
            icon.classList.toggle("fa-regular");

            fetch("bookmark_post.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ post_id: postId, action: action })
            })
            .then(response => response.json())
            .then(data => {
                console.log("Response dari server:", data); // Debug log
                if (!data.success) {
                    this.classList.toggle("saved");
                    icon.classList.toggle("fa-solid");
                    icon.classList.toggle("fa-regular");
                    alert("Gagal menyimpan bookmark. Coba lagi.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                this.classList.toggle("saved");
                icon.classList.toggle("fa-solid");
                icon.classList.toggle("fa-regular");
            });
        });
    });
});


</script>


</html>
