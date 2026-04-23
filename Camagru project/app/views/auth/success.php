<!DOCTYPE html>

<?php require __DIR__ . '/../partials/header.php'; ?>

<h2>Successo</h2>

<?php if (!empty($message)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

</html>