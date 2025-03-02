<?php
include 'config.php'; // Pastikan file ini mengandung koneksi MySQLi

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? 0;

// Ambil data pengguna yang login
$queryUser = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$queryUser->bind_param("i", $user_id);
$queryUser->execute();
$resultUser = $queryUser->get_result();
$user = $resultUser->fetch_assoc();

// Ambil topik trending berdasarkan jumlah komentar/like
$queryTrending = $conn->query("
    SELECT p.caption, COUNT(c.id) AS total_comments
    FROM posts p
    LEFT JOIN comments c ON p.id = c.post_id
    GROUP BY p.id
    ORDER BY total_comments DESC
    LIMIT 5
");
$trendingTopics = $queryTrending->fetch_all(MYSQLI_ASSOC);

// Ambil akun yang mungkin dikenal (tidak diikuti)
$querySuggested = $conn->prepare("
    SELECT u.id, u.username, u.profile_picture 
    FROM users u
    WHERE u.id NOT IN (
        SELECT following_id FROM followers WHERE follower_id = ?
    ) AND u.id != ?
    ORDER BY RAND() 
    LIMIT 5
");
$querySuggested->bind_param("ii", $user_id, $user_id);
$querySuggested->execute();
$resultSuggested = $querySuggested->get_result();
$suggestedUsers = $resultSuggested->fetch_all(MYSQLI_ASSOC);
?>

<div class="right-sidebar">
    <!-- Profil Pengguna -->
    <div class="profile-section">
    <img src="<?php echo !empty($user['profile_picture']) ? '../uploads/profile_pictures/' . $user['profile_picture'] : '../images/default_profile.png'; ?>" alt="Profile Picture">

        <p>@<?= htmlspecialchars($user['username']) ?></p>
        <a href="profil.php">Lihat Profil</a>
    </div>

    <!-- Trending Topics -->
    <div class="trending-section">
        <h3>Trending</h3>
        <ul>
            <?php foreach ($trendingTopics as $topic): ?>
                <li>#<?= htmlspecialchars(substr($topic['caption'], 0, 20)) ?>...</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Rekomendasi Akun -->
    <div class="suggested-section">
        <h3>Orang yang Mungkin Anda Kenal</h3>
        <ul>
            <?php foreach ($suggestedUsers as $user): ?>
                <li>
                <img src="<?php echo !empty($user['profile_picture']) ? '../uploads/profile_pictures/' . $user['profile_picture'] : '../images/default_profile.png'; ?>" alt="Profile Picture">
                <a href="/ig/pages/<?= htmlspecialchars($user['username']) ?>" class="profile-link">@<?= htmlspecialchars($user['username']) ?></a>
                    <button class="follow-btn" data-user="<?= $user['id'] ?>">Follow</button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
document.querySelectorAll('.follow-btn').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        let userId = this.getAttribute('data-user');
        
        fetch('follow_user.php', {
            method: 'POST',
            body: JSON.stringify({ user_id: userId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.innerText = 'Diikuti';
                this.disabled = true;
            } else {
                alert('Gagal mengikuti pengguna.');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

</script>

<style>
.right-sidebar {
    position: fixed;
    right: 0;
    top: 0;
    width: 300px;
    height: 100vh;
    background: white;
    border-left: 1px solid #dbdbdb;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
    padding-left: 15px;
    padding-right: 15px;
    overflow-y: auto;
}

/* Gaya untuk setiap bagian di sidebar */
.profile-section,
.trending-section,
.suggested-section {
    margin-bottom: 20px;
}

/* Gaya untuk profil pengguna */
.profile-section img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;
}

/* Gaya daftar trending */
.trending-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.trending-section li {
    font-size: 14px;
    color: #333;
    padding: 5px 0;
}

/* Gaya daftar akun yang direkomendasikan */
.suggested-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.suggested-section li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.suggested-section img {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;
}

/* Tombol Follow */
.follow-btn {
    background: #0095f6;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 5px;
}

.follow-btn:disabled {
    background: #aaa;
}

/* Hilangkan sidebar kanan di mode mobile */
@media screen and (max-width: 768px) {
    .right-sidebar {
        display: none;
    }
}

</style>
