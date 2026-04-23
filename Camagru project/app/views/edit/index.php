<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor</title>
    <style>
    </style>
</head>
<body>

<?php include __DIR__ . '/../partials/header.php'; ?>
<main>
<h1>Editor</h1>
<div class="panelEditor">
    <div id="preview">
        <video id="video" height="480" width="640" autoplay playsinline></video>
        <img id="uploadedImage" height="480" width="640" style="display:none;">
        <img id="overlay" class="overlay" src="" alt="">
    </div>
    
    <div>
        <h3>Scegli uno sticker</h3>

        <div id="overlayBar">
            <?php foreach ($overlays as $file): ?>
                <img 
                    class="overlayThumb"
                    src="/overlays/<?= htmlspecialchars($file) ?>"
                    data-src="/overlays/<?= htmlspecialchars($file) ?>"
                >
            <?php endforeach; ?>
        </div>
    </div>
</div>
<button id="captureBtn" disabled>Cattura</button>
<h3>Oppure carica una foto <input type="file" id="uploadInput" accept="image/*"></h3>

<h2>Le tue foto</h2>

<?php foreach ($userImages as $img): ?>
    <div class="imageContainer">

        <div class="thumbnail">
            <img src="/uploads/<?= htmlspecialchars($img['filename']) ?>" alt="">
            <button class="deleteBtn" data-filename="<?= htmlspecialchars($img['filename']) ?>">×</button>
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
                    <?= htmlspecialchars($comment['username']) ?>: <?= htmlspecialchars($comment['content']) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="commentForm" data-image-id="<?= $img['id'] ?>">
            <input type="text" name="comment" placeholder="Scrivi un commento">
            <button type="submit">Invia</button>
        </form>        

    </div>
<?php endforeach; ?>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
    const video = document.getElementById('video');
    const overlayImg = document.getElementById('overlay');
    const captureBtn = document.getElementById('captureBtn');

    const uploadInput = document.getElementById('uploadInput');
    const uploadedImage = document.getElementById('uploadedImage');

    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;

    let selectedOverlay = null;


document.querySelectorAll('.overlayThumb').forEach(img => {

    img.addEventListener('click', () => {

        document.querySelectorAll('.overlayThumb').forEach(i => {
            i.style.border = "2px solid transparent";
        });

        img.style.border = "2px solid red";

        overlayImg.src = img.dataset.src;
        selectedOverlay = img.dataset.src;
        updateCaptureButton();

    });
});

function updateCaptureButton() {
    const hasImage = uploadedImage.style.display === "block";
    const hasVideo = video.srcObject !== null;

    captureBtn.disabled = !(selectedOverlay && (hasImage || hasVideo));
}

captureBtn.addEventListener('click', () => {
    const canvas = document.createElement('canvas');
    canvas.width = 640;
    canvas.height = 640;
    const ctx = canvas.getContext('2d');

    if (uploadedImage.style.display === "block") {
        const size = Math.min(uploadedImage.naturalWidth, uploadedImage.naturalHeight);
        const sx = (uploadedImage.naturalWidth - size) / 2;
        const sy = (uploadedImage.naturalHeight - size) / 2;
        ctx.drawImage(uploadedImage, sx, sy, size, size, 0, 0, 640, 640);
    } else if (video.srcObject !== null) {
        const size = Math.min(video.videoWidth, video.videoHeight);
        const sx = (video.videoWidth - size) / 2;
        const sy = (video.videoHeight - size) / 2;
        ctx.drawImage(video, sx, sy, size, size, 0, 0, 640, 640);
    }

    const overlayRect = overlayImg.getBoundingClientRect();
    const previewRect = document.getElementById('preview').getBoundingClientRect();

    const scaleX = 640 / previewRect.width;
    const scaleY = 640 / previewRect.height;

    const cropSize = Math.min(video.videoWidth, video.videoHeight);

const displayedCropWidth = previewRect.height; 
const cropLeftOffset = (previewRect.width - displayedCropWidth) / 2;

const x = (overlayRect.left - previewRect.left - cropLeftOffset) * (640 / displayedCropWidth);
const y = (overlayRect.top - previewRect.top) * (640 / displayedCropWidth);

const width = overlayImg.offsetWidth * (640 / displayedCropWidth);
const height = overlayImg.offsetHeight * (640 / displayedCropWidth);

    // const x = (overlayRect.left - previewRect.left) * scaleX;
    // const y = (overlayRect.top - previewRect.top) * scaleY;
    // const width = overlayImg.offsetWidth * scaleX;
    // const height = overlayImg.offsetHeight * scaleY;

    ctx.drawImage(overlayImg, x, y, width, height);

    const dataURL = canvas.toDataURL('image/png');

    fetch('/edit/save', {
        method: 'POST',
        body: JSON.stringify({
            image: dataURL,
            csrf_token: document.querySelector('meta[name="csrf-token"]').content
        }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error);
        }
    });
});


document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const filename = btn.dataset.filename;

        fetch('/edit/delete', {
            method: 'POST',
            body: JSON.stringify({ filename,
                csrf_token: document.querySelector('meta[name="csrf-token"]').content
            }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error);
            }
        });
    });
});

document.querySelectorAll('.commentForm').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const imageId = form.dataset.imageId;
        const commentInput = form.querySelector('input[name="comment"]');
        const commentText = commentInput.value.trim();

        if (!commentText) return;

        try {
            const res = await fetch('/comment/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    image_id: imageId,
                    comment: commentText,
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content
                })
            });

            const data = await res.json();

            if (data.success) {
                const commentList = form.closest('.imageContainer').querySelector('.comments');
                const commentDiv = document.createElement('div');
                commentDiv.innerHTML = `<b>${data.username}:</b> ${commentText}`;
                commentList.appendChild(commentDiv);
                commentInput.value = '';
            } else {
                if (data.error === 'image_not_found') {
                    alert("Questa immagine è stata eliminata.");
                    const imageContainer = form.closest('.imageContainer');
                    if (imageContainer) imageContainer.remove();
                    return;
                }
                else {
                    alert(data.error || 'Errore nel commento');
                }
            }
        } catch (err) {
            console.error('Errore fetch commento:', err);
            alert('Impossibile aggiungere il commento');
        }
    });
});


uploadInput.addEventListener('change', () => {
    const file = uploadInput.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        alert('Non provarci, devi caricare un file immagine!');
        uploadInput.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedImage.src = e.target.result;
        uploadedImage.style.display = "block";
        video.style.display = "none";
        updateCaptureButton();
    };
    reader.readAsDataURL(file);
});

overlayImg.addEventListener('mousedown', (e) => {
    isDragging = true;
    const rect = overlayImg.getBoundingClientRect();

    offsetX = e.clientX - rect.left;
    offsetY = e.clientY - rect.top;
});

document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;

    const preview = document.getElementById('preview');
    const rect = preview.getBoundingClientRect();

    let x = e.clientX - rect.left - offsetX;
    let y = e.clientY - rect.top - offsetY;

    x = Math.max(0, Math.min(rect.width - overlayImg.offsetWidth, x));
    y = Math.max(0, Math.min(rect.height - overlayImg.offsetHeight, y));

    overlayImg.style.left = x + "px";
    overlayImg.style.top = y + "px";
});

document.addEventListener('mouseup', () => {
    isDragging = false;
});

overlayImg.addEventListener('wheel', (e) => {
    e.preventDefault();
    let width = overlayImg.offsetWidth;

    if (e.deltaY < 0) {
        width += 10;
    } else {
        width -= 10;
    }

    width = Math.max(18, Math.min(300, width));

    overlayImg.style.width = width + "px";
});


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
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content
                 })
            });

            const data = await res.json();

            if (data.success) {
                const countSpan = document.getElementById(
                    'like-count-' + imageId
                );
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

async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: "user"
            },
            audio: false
        });

        video.srcObject = stream;
        updateCaptureButton();
    } catch (err) {
        console.error("Errore accesso camera:", err);
    }
}

startCamera();

</script>
</body>
</html>