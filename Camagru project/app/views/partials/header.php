<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}
?>

<header>
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/home.css">
    <link rel="stylesheet" href="/css/edit.css">
    <link rel="stylesheet" href="/css/overlays.css">
    <link rel="stylesheet" href="/css/thumbnails.css">
    <link rel="stylesheet" href="/css/comments.css">
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/footer.css">
    <link rel="stylesheet" href="/css/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Henny+Penny&display=swap" rel="stylesheet">

    <nav>
        <?php if (isLoggedIn()): ?>
            <div>
                <p class="navClass">
                    Benvenuto 
                    <a href="/profile"><?= htmlspecialchars($_SESSION['username']) ?></a> |
                    <a href="/edit">Editor</a> |
                    <a href="/">Home</a> |
                    <a href="/auth/logout">Logout</a>

                </p>
                <?php else: ?>
                <p class="navClass">
                    <a href="/auth/login">Login</a> |
                    <a href="/auth/register">Registrati</a> |
                    <a href="/">Home</a>
                </p>
            </div>
        <?php endif; ?>
    </nav>
    <div class="logo">FunkyLink</div>

</header>