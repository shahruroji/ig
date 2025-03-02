<?php
session_start();
require '../config.php';

$search_query = $_GET['q'] ?? '';

// Jika ada pencarian
if (!empty($search_query)) {
    $stmt = $conn->prepare("SELECT id, username, profile_picture FROM users WHERE username LIKE ?");
    $like_query = "%{$search_query}%";
    $stmt->bind_param("s", $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian</title>
    <link rel="stylesheet" href="/assets/css/search.css">
</head>
<body>

<!-- Form Pencarian -->
<form action="search.php" method="GET">
    <input type="text" name="q" placeholder="Cari username..." value="<?= htmlspecialchars($search_query) ?>" required>
    <button type="submit">Cari</button>
</form>

<!-- Hasil Pencarian -->
<?php if ($result && $result->num_rows > 0): ?>
    <ul class="search-results">
        <?php while ($user = $result->fetch_assoc()): ?>
            <li>
                <a href="/ig/pages/<?= htmlspecialchars($user['username']) ?>">
                    <img src="<?= !empty($user['profile_picture']) ? '../uploads/profile_pictures/' . $user['profile_picture'] : '../uploads/profile_pictures/default-profile.png' ?>" class="profile-pic">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php elseif (!empty($search_query)): ?>
    <p>Tidak ada hasil untuk "<strong><?= htmlspecialchars($search_query) ?></strong>"</p>
<?php endif; ?>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
