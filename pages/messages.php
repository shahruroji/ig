<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Silakan login terlebih dahulu.");
}

$current_user_id = $_SESSION['user_id'];
$receiver_id = $_GET['user_id'] ?? 0; // Ambil ID penerima dari URL

// Ambil data pengguna yang diajak chat
$queryUser = $conn->prepare("SELECT id, username, profile_picture FROM users WHERE id = ?");
$queryUser->bind_param("i", $receiver_id);
$queryUser->execute();
$resultUser = $queryUser->get_result();
$receiver = $resultUser->fetch_assoc();

if (!$receiver) {
    die("Pengguna tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan <?= htmlspecialchars($receiver['username']) ?></title>
    <link rel="stylesheet" href="../chat.css">
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <img src="<?= !empty($receiver['profile_picture']) ? '../uploads/profile_pictures/' . $receiver['profile_picture'] : '../images/default_profile.png' ?>" class="chat-avatar">
        <h2><?= htmlspecialchars($receiver['username']) ?></h2>
    </div>

    <div id="chat-box"></div>

    <div class="chat-input">
        <input type="text" id="message-input" placeholder="Tulis pesan...">
        <button id="send-button">Kirim</button>
    </div>
</div>

<script>
    let receiverId = <?= $receiver_id ?>;

    document.getElementById('send-button').addEventListener('click', function() {
        let message = document.getElementById('message-input').value;

        fetch('../actions/send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `receiver_id=${receiverId}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('message-input').value = '';
                loadMessages();
            }
        });
    });

    function loadMessages() {
        fetch(`../actions/fetch_messages.php?receiver_id=${receiverId}`)
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

    setInterval(loadMessages, 2000); // Auto-refresh setiap 2 detik
    loadMessages();
</script>

</body>
</html>
