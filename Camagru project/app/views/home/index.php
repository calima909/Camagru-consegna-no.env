<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FunkyLink</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>    

    <main>

    <div class="gallery">
        <?php foreach ($images as $img): ?>
        <div class="imageCard" data-image-id="<?= $img['id'] ?>">
            <img src="/uploads/<?= htmlspecialchars($img['filename']) ?>" width="300">
            <div class=author>
                <p>Autore: <?= htmlspecialchars($img['username']) ?></p>
            </div>

            <div class="likeSection">
                <button class="likeBtn" data-image-id="<?= $img['id'] ?>">
                    <?= $img['liked_by_user'] ? '💔' : '❤️' ?>
                </button>
                
                <span id="like-count-<?= $img['id'] ?>">
                    <?= $img['likes_count'] ?>
                </span>
            </div>

            <div class="comments">
                <?php foreach ($img['comments'] as $comment): ?>
                    <div>
                        <b><?= htmlspecialchars($comment['username']) ?>:</b>
                        <?= htmlspecialchars($comment['content']) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
            <form class="commentForm" data-image-id="<?= $img['id'] ?>">
                <input 
                    type="text" 
                    name="comment" 
                    placeholder="Scrivi un commento..."
                    required
                >
                <button type="submit">Commenta</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">

    <?php if ($currentPage > 1): ?>
        <a href="/?page=<?= $currentPage - 1 ?>">⬅ Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="/?page=<?= $i ?>"
        <?= $i == $currentPage ? 'style="font-weight:bold"' : '' ?>>
        <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($currentPage < $totalPages): ?>
        <a href="/?page=<?= $currentPage + 1 ?>">Next ➡</a>
    <?php endif; ?>

    </div>

    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    document.querySelectorAll('.likeBtn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const imageId = btn.dataset.imageId;
            try {
                const res = await fetch('/like/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        image_id: imageId, 
                        csrf_token: document.querySelector('meta[name="csrf-token"]').content })
                });

                const data = await res.json();

                if (data.success) {
                    const countSpan = document.getElementById('like-count-' + imageId);
                    countSpan.textContent = data.count;
                    btn.textContent = data.liked ? '💔' : '❤️';
                }
                else {
                    if (data.error === 'not_logged') {
                        window.location.href = '/auth/login';
                        return;
                    }
                    if (data.error === 'image_not_found') {
                        alert("immagine eliminata");
                        const imageCard = btn.closest('.imageCard');
                        if (imageCard) {
                            imageCard.remove();
                        }
                        return;
                    }
                    alert('Errore nel like');
                }
            } catch (err) {
                console.error('Errore richiesta like:', err);
            }
        });
    });
    
    document.querySelectorAll('.commentForm').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const imageId = form.dataset.imageId;
            const input = form.querySelector('input[name="comment"]');
            const commentText = input.value.trim();

            if (!commentText) return;

            const formData = new FormData();
            formData.append('image_id', imageId);
            formData.append('comment', commentText);
            formData.append('csrf_token', csrfToken);

            try {
                console.log("cazzp");
                const res = await fetch('/comment/add', {
                    method: 'POST',
                    body: formData,               
                });
                console.log("cazzo2");
                const data = await res.json();
                if (data.success) {
                    const imageCard = form.closest('.imageCard');
                    const commentsDiv = imageCard.querySelector('.comments');
                    const newComment = document.createElement('div');
                    newComment.innerHTML =
                        "<b>" + data.username + ":</b> " + commentText;
                    commentsDiv.appendChild(newComment);
                    input.value = '';
                } else {
                    if (data.error === 'image_not_found') {
                        alert("Questa immagine è stata eliminata.");

                        const imageCard = form.closest('.imageCard');
                        if (imageCard) {
                            imageCard.remove();
                        }
                        return;
                    }
                    else {
                        alert(data.error || 'Errore nel commento');
                    }
                }
            } catch (err) {
                console.error(err);
            }
        });
    });
    </script>
</body>
</html>

