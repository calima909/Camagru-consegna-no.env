<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <main class="main2">
        <h1>Login</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red; padding-left: 15px;" >
                <?= htmlspecialchars($_SESSION['error']); ?>
            </p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" action="/auth/loginPost" style="padding: 15px;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>
            <button type="submit">Login</button>        
        </form>

        <a href="/auth/forgot" style="padding: 15px; color: black">Password dimenticata?</a>
        <br><br>
        <a href="/" style="padding: 15px; color: black">Torna alla Home</a>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>