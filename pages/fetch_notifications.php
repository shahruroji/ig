<?php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'];
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

while ($notif = $result->fetch_assoc()) :
?>
    <li class="notif-item <?= $notif['is_read'] ? '' : 'unread' ?>">
    <img src="<?php echo !empty($notif['profile_picture']) ? '../uploads/profile_pictures/' . $notif['profile_picture'] : '../uploads/profile_pictures/default-profile.png'; ?>" class="profile-pic">

        <div class="notif-content">
        <p>
    <strong><?= $notif['username'] ?></strong>
    <?php if ($notif['type'] == 'like') : ?>
        menyukai postingan kamu
    <?php elseif ($notif['type'] == 'comment') : ?>
        mengomentari postingan kamu
    <?php elseif ($notif['type'] == 'follow') : ?>
        mulai mengikuti kamu
    <?php else: ?>
        melakukan tindakan tidak dikenal
    <?php endif; ?>
</p>

            <span class="notif-time"><?= time_elapsed_string($notif['created_at']) ?></span>
        </div>
    </li>
<?php endwhile; ?>

<?php
$stmt->close();
$conn->close();
?>
