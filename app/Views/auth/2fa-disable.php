<?php require __DIR__ . '/../common/header.php'; ?>

<div class="container" style="max-width: 400px; margin: 100px auto;">
    <h1>Disable Two-Factor Authentication</h1>
    <p>Enter your password to confirm disabling 2FA.</p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($disableAction ?? '') ?>">
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-danger">Disable 2FA</button>
        <a href="<?= htmlspecialchars($cancelUrl ?? '') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<style>
    .form-group { margin: 20px 0; }
    .form-group label { display: block; margin-bottom: 5px; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 10px; }
    .btn-danger { background: #dc3545; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; }
</style>

<?php require __DIR__ . '/../common/footer.php'; ?>
