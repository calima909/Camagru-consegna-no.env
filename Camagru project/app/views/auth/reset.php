<!DOCTYPE html>

<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Nuova password</h2>

<?php if (!empty($message)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($message ?? ''); ?></p>
<?php endif; ?>

<form method="POST" action="/auth/updatePassword">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
    <label>Nuova password</label><br>
    <input type="password" name="password" required>
    <br><br>
    <button type="submit">Aggiorna password</button>
</form>

</html>