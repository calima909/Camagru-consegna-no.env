<footer class="footer">
    <div class="footer-content">
        <p>✌️ Made with ❤️ by FChiocci — © 2026</p>

        <p>
            <a href="/">Home</a> |
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/profile">Profilo</a> |
                <a href="/edit">Editor</a>
            <?php else: ?>
                <a href="/auth/login">Login</a> |
                <a href="/auth/register">Registrati</a>
            <?php endif; ?>
        </p>
    </div>
</footer>