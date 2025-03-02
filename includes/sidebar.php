<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require '../config.php';

// Cek apakah user sudah login
$user_id = $_SESSION['user_id'] ?? null;
$profile_picture = "../uploads/profile_pictures/default-profile.jpg"; // Default foto profil

$unread_count = 0; // Default jumlah notifikasi

if ($user_id) {
    // Ambil jumlah notifikasi belum dibaca
    $query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $unread_count = $row['unread_count'] ?? 0;

    // Ambil foto profil pengguna
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!empty($user['profile_picture'])) {
        $profile_picture = "../uploads/profile_pictures/" . htmlspecialchars($user['profile_picture']);
    }
}
?>

<style>
/* Sidebar */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 220px;
    height: 100vh;
    background: white;
    border-right: 1px solid #dbdbdb;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar ul li {
    margin: 10px 0;
}

.sidebar ul li a {
    display: flex;
    align-items: center;
    padding: 10px;
    font-size: 16px;
    color: black;
    text-decoration: none;
}

/* Ukuran ikon di sidebar */
.sidebar ul li a i {
    font-size: 22px;
    margin-right: 12px;
}

/* Gaya foto profil di sidebar */
.sidebar-profile-pic {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

/* Notifikasi */
.notif-icon-container {
    position: relative;
    display: flex;
    align-items: center;
    padding: 10px;
    cursor: pointer;
    color: black;
    text-decoration: none;
}

.notif-icon-container {
    position: relative;  /* Tambahkan ini */
    display: flex;
    align-items: center;
    padding: 10px;
    cursor: pointer;
    color: black;
    text-decoration: none;
}


.notif-badge {
    position: absolute;
    top: 0px;    /* Atur supaya pas di atas ikon */
    right: 0px;  /* Geser badge ke kanan */
    background-color: red;
    color: white;
    font-size: 12px;
    padding: 3px 6px;
    border-radius: 50%;
    display: none;
}


/* Mode Mobile */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: 50px;
        position: fixed;
        bottom: 0;
        top: unset;
        border-top: 1px solid #dbdbdb;
        border-right: none;
        flex-direction: row;
        justify-content: space-around;
        padding: 0;
    }

    .sidebar ul {
        display: flex;
        width: 100%;
        justify-content: space-around;
    }

    .sidebar ul li {
        margin: 0;
    }

    .sidebar ul li a {
        display: flex;
        flex-direction: column;
        align-items: center;
        font-size: 0; /* Sembunyikan teks */
    }

    .sidebar ul li a i {
        font-size: 24px;
    }

    .sidebar-profile-pic {
        width: 28px;
        height: 28px;
        margin: 0;
    }
}
</style>

<!-- Sidebar -->
<div class="sidebar">
    <ul>
        <li><a href="../pages/home.php"><i class="fas fa-home"></i><span>Beranda</span></a></li>
        <li><a href="../search.php"><i class="fas fa-search"></i><span>Cari</span></a></li>
        <li><a href="../explore.php"><i class="fas fa-compass"></i><span>Jelajahi</span></a></li>
        <li><a href="../pages/reels.php"><i class="fas fa-video"></i><span>Reels</span></a></li>
        <li><a href="../pages/chat.php"><i class="fas fa-envelope"></i><span>Pesan</span></a></li>

        <!-- Ikon Notifikasi -->
        <li>
    <a href="../pages/notif.php" class="notif-icon-container" onclick="openNotifications()">
        <i class="fas fa-heart"></i><span>Notifikasi</span> <!-- Ikon Notifikasi -->
        <span class="notif-badge"><?= $unread_count ?></span> <!-- Badge Notifikasi -->
    </a>
</li>


        <li>
            <a href="../pages/profil.php">
                <img src="<?= $profile_picture ?>" alt="Profile Picture" class="sidebar-profile-pic">
                <span>Profil</span>
            </a>
        </li>
    </ul>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function checkNotifications() {
    $.ajax({
        url: "../pages/fetch_unread_notifications.php",
        method: "GET",
        success: function(data) {
            let count = parseInt(data);
            if (count > 0) {
                $(".notif-badge").text(count).show(); // Update angka
            } else {
                $(".notif-badge").hide(); // Sembunyikan jika tidak ada notifikasi
            }
        }
    });
}

// Cek notifikasi setiap 5 detik
setInterval(checkNotifications, 1000);

// Hilangkan badge saat ikon diklik
function openNotifications() {
    $.ajax({
        url: "../pages/mark_notifications_as_read.php",
        method: "POST",
        success: function() {
            $(".notif-badge").hide(); // Sembunyikan badge setelah diklik
        }
    });
}
</script>
