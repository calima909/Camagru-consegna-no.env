<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <main class="main2">
    <h1>Registrazione</h1>

    <form method="POST" action="/auth/store" style="padding: 15px;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Registrati</button>
    </form>

    <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red; padding-left: 15px;">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </p>
    <?php endif; ?>

    <a href="/" style="padding: 15px; color: black">Torna alla Home</a>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>