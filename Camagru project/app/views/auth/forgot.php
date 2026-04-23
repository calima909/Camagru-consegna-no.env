<!DOCTYPE html>

<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Recupero password</h2>

<?php if (!empty($message)): ?>
    <p style="color:red; margin-left:15px;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="POST" action="/auth/sendReset" style="margin-left:15px;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <label>Email</label><br>
    <input type="email" name="email" required>
    <br><br>

    <button type="submit">Invia email di recupero</button>
</form>

</html>