<?php require __DIR__ . '/../common/header.php'; ?>

<div class="container" style="max-width: 500px; margin: 50px auto;">
    <h1>Enable Two-Factor Authentication</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="setup-steps">
        <h3>Setup Instructions:</h3>
        <ol>
            <li>Install Google Authenticator or Authy on your smartphone</li>
            <li>Scan the QR code below with the app</li>
            <li>Enter the 6-digit code from the app to verify</li>
        </ol>
    </div>

    <div class="qr-code" style="text-align: center; margin: 20px 0; background: white; padding: 20px;">
        
        <img src="<?= $qrCodeDataUri ?? '' ?>" alt="QR Code for 2FA Setup">
    </div>

    <div class="manual-entry" style="background: #f5f5f5; padding: 15px; margin: 20px 0;">
        <p><strong>Can't scan?</strong> Enter this code manually:</p>
        <code style="font-size: 1.2em; letter-spacing: 2px;"><?= htmlspecialchars($secret ?? '') ?></code>
    </div>

    <form method="POST" action="<?= htmlspecialchars($setupAction ?? '') ?>">
        <div class="form-group">
            <label for="code">Verification Code:</label>
            <input type="text"
                   id="code"
                   name="code"
                   pattern="[0-9]{6}"
                   maxlength="6"
                   required
                   autofocus
                   placeholder="Enter 6-digit code"
                   style="font-size: 1.5em; letter-spacing: 5px; text-align: center;">
        </div>

        <button type="submit" class="btn btn-primary">Verify and Enable 2FA</button>
        <a href="<?= htmlspecialchars($cancelUrl ?? '') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<style>
    .form-group { margin: 20px 0; }
    .form-group label { display: block; margin-bottom: 5px; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 10px; }
    .btn-primary { background: #007bff; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
</style>

<?php require __DIR__ . '/../common/footer.php'; ?>
