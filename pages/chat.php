<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Silakan login terlebih dahulu.");
}

$current_user_id = $_SESSION['user_id'];
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    // Jika tidak ada user_id, tampilkan daftar percakapan
    $queryChats = $conn->prepare(
        "SELECT users.id, users.username, users.profile_picture, MAX(messages.created_at) AS last_message_time 
        FROM messages
        JOIN users ON (messages.sender_id = users.id OR messages.receiver_id = users.id)
        WHERE (messages.sender_id = ? OR messages.receiver_id = ?) AND users.id != ?
        GROUP BY users.id, users.username, users.profile_picture
        ORDER BY last_message_time DESC"
    );
    $queryChats->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
    $queryChats->execute();
    $chats = $queryChats->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pesan</title>
        <link rel="stylesheet" href="../chat.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <?php include '../includes/sidebar.php'; ?> 
    <?php include '../includes/right_sidebar.php'; ?> 
    </head>
    <body>
    
    <div class="chat-list">
        <h2>Pesan</h2>
        <?php foreach ($chats as $chat): ?>
            <a href="chat.php?user_id=<?= $chat['id'] ?>" class="chat-item">
                <img src="../uploads/profile_pictures/<?= $chat['profile_picture'] ?? 'default_profile.png' ?>" class="profile-picture">
                <span><?= htmlspecialchars($chat['username']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Jika user_id ada, tampilkan obrolan seperti sebelumnya
$queryUser = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$queryUser->bind_param("i", $user_id);
$queryUser->execute();
$user = $queryUser->get_result()->fetch_assoc();

if (!$user) {
    die("Pengguna tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../chat.css">
    
</head>
<body>

<div class="chat-container">
    
    <div class="chat-header">
        <a href="chat.php">&#8592; Kembali</a>
        <img src="../uploads/profile_pictures/<?= $user['profile_picture'] ?? 'default_profile.png' ?>" class="profile-picture">
        <h2><?= htmlspecialchars($user['username']) ?></h2>
    </div>

    <div class="chat-box" id="chat-box"></div>

    <div class="chat-input">
        <input type="text" id="message-input" placeholder="Tulis pesan...">
        <button id="send-btn">Kirim</button>
    </div>
    
</div>

<script>
function loadMessages() {
    fetch('load_messages.php?user_id=<?= $user_id ?>')
        .then(response => response.text())
        .then(data => {
            document.getElementById('chat-box').innerHTML = data;
        });
}

document.getElementById('send-btn').addEventListener('click', function() {
    let message = document.getElementById('message-input').value;
    if (message.trim() === '') return;

    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `receiver_id=<?= $user_id ?>&message=${encodeURIComponent(message)}`
    })
    .then(response => response.text())
    .then(() => {
        document.getElementById('message-input').value = '';
        loadMessages();
    });
});

document.getElementById('message-input').addEventListener("keypress", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
        document.getElementById('send-btn').click();
    }
});

setInterval(loadMessages, 3000);
loadMessages();

</script>

</body>
</html>
