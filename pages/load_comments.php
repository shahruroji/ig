<?php
include 'config.php';

if (isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
    $post_id = $_POST['post_id'];

    // Ambil semua komentar utama dan balasan dalam satu query
    $comments_query = $conn->prepare("
        SELECT comments.*, users.username 
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        WHERE post_id = ? 
        ORDER BY comments.created_at ASC");
    $comments_query->bind_param("i", $post_id);
    $comments_query->execute();
    $comments_result = $comments_query->get_result();

    // Pisahkan komentar utama dan balasan
    $comments = [];
    while ($row = $comments_result->fetch_assoc()) {
        if ($row['parent_id'] === null) {
            $comments[$row['id']] = $row;
            $comments[$row['id']]['replies'] = []; // Tambahkan array untuk balasan
        } else {
            $comments[$row['parent_id']]['replies'][] = $row;
        }
    }

    // Tampilkan komentar utama dan balasan
    foreach ($comments as $comment):
?>
        <div class="comment">
            <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong> <?php echo htmlspecialchars($comment['comment_text']); ?></p>
            <span class="reply-btn" data-comment-id="<?php echo $comment['id']; ?>" style="cursor: pointer; color: #007bff;">
                Balas
            </span>

            <!-- Form balasan (Hidden) -->
            <form class="reply-form" style="display: none;">
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                <input type="text" name="comment" placeholder="Balas komentar..." required>
                <button type="submit">Kirim</button>
            </form>

            <!-- Load balasan komentar -->
            <div class="replies">
                <?php foreach ($comment['replies'] as $reply): ?>
                    <div class="reply">
                        <p><strong><?php echo htmlspecialchars($reply['username']); ?></strong> <?php echo htmlspecialchars($reply['comment_text']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
<?php
    endforeach;
}
?>
<script>document.addEventListener("DOMContentLoaded", function () {
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
</script>