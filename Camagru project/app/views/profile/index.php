<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Profilo</title>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<main>
    <h1>Profilo</h1>  

    <h3>Modifica dati</h3>

    <form method="POST" action="/profile/update" style="padding: 15px;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label>Username</label><br>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        <br><br>

        <label>Email</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <br><br>

        <label>Password</label><br>
            <input type="password" name="password" placeholder="Lascia vuoto per non cambiarla">
        <br><br>

        <label>
            <input type="checkbox" name="notify_comments"
                <?= $notifyComments ? 'checked' : '' ?>>
            Ricevi notifiche sui commenti alle tue immagini
        </label>

        <br><br>
        <button type="submit">Aggiorna dati</button>
        <br><br>
    </form>

    <?php if (!empty($errors)): ?>
        <div style="color:red;">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2>Le tue immagini</h2>
    <div class="imageProfile">
        <?php foreach ($images as $img): ?>
            <img src="/uploads/<?= htmlspecialchars($img['filename']) ?>">
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>