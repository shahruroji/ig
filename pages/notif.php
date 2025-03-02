<?php
session_start();
require '../config.php'; // Pastikan path benar

$user_id = $_SESSION['user_id']; // ID user yang sedang login

// Ambil notifikasi terbaru
$query = "SELECT n.*, u.username, u.profile_picture, 
                 COALESCE(p.media_paths, '') AS media_paths
          FROM notifications n
          JOIN users u ON n.sender_id = u.id
          LEFT JOIN posts p ON n.post_id = p.id
          WHERE n.user_id = ? 
          ORDER BY n.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi</title>
    <link rel="stylesheet" href="../assets/css/notif.css"> <!-- Sesuaikan dengan file CSS -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="notif-container">
    <?php include '../includes/sidebar.php'; ?>

<?php include '../includes/right_sidebar.php'; ?>

        <h2>Notifikasi</h2>
        <ul id="notif-list">
            <?php foreach ($notifications as $notif) : ?>
                <li class="notif-item <?= $notif['is_read'] ? '' : 'unread' ?>">
                <img src="<?php echo !empty($notif['profile_picture']) ? '../uploads/profile_pictures/' . $notif['profile_picture'] : '../uploads/profile_pictures/default-profile.png'; ?>" class="profile-pic">
                    <div class="notif-content">
                        <p>
                            <strong><?= $notif['username'] ?></strong>
                            <?php if ($notif['type'] == 'like') : ?>
                                menyukai postingan kamu
                                <?php if (!empty($notif['media_paths'])) : ?>
                                    <img src="<?= $notif['media_paths'] ?>" class="notif-post-img">
                                <?php endif; ?>
                            <?php elseif ($notif['type'] == 'comment') : ?>
                                mengomentari postingan kamu
                                <?php if (!empty($notif['media_paths'])) : ?>
                                    <img src="<?= $notif['media_paths'] ?>" class="notif-post-img">
                                <?php endif; ?>
                            <?php elseif ($notif['type'] == 'follow') : ?>
                                mulai mengikuti kamu
                            <?php endif; ?>
                        </p>
                        <span class="notif-time"><?= time_elapsed_string($notif['created_at']) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        // Real-time notifikasi dengan AJAX
        setInterval(function () {
            $.ajax({
                url: "fetch_notifications.php",
                method: "GET",
                success: function (data) {
                    $("#notif-list").html(data);
                }
            });
        }, 5000);
    </script>
</body>
</html>

<?php
// Fungsi untuk menampilkan waktu relatif
function time_elapsed_string($datetime, $full = false) {
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
    foreach ($string as $k => &$v) {
        if ($v) {
            $v = "$v $k";
        } else {
            unset($string[$k]);
        }
    }
    return $string ? implode(', ', $string) . ' lalu' : 'baru saja';
}
?>
