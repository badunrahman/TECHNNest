<?php require __DIR__ . '/../common/header.php'; ?>

<div class="container" style="max-width: 400px; margin: 100px auto;">
    <h1>Two-Factor Verification</h1>
    <p>Enter the 6-digit code from your authenticator app.</p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($verifyAction ?? '') ?>">
        <div class="form-group">
            <label for="code">Verification Code:</label>
            <input type="text"
                id="code"
                name="code"
                pattern="[0-9]{6}"
                maxlength="6"
                required
                autofocus
                placeholder="000000"
                style="font-size: 2em; letter-spacing: 10px; text-align: center;">
        </div>
 
        <div class="form-group" style="text-align: left;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="trust_device" value="1" style="width: auto; margin-right: 10px;">
                Trust this device for 30 days
            </label>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Verify</button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
        <form method="POST" action="<?= htmlspecialchars($logoutUrl ?? '') ?>">
            <button type="submit" class="btn-link">Cancel and Logout</button>
        </form>
    </div>
</div>

<style>
    .form-group {
        margin: 20px 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-group input[type="text"] {
        width: 100%;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .btn {
        padding: 15px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-link {
        background: none;
        border: none;
        color: #007bff;
        cursor: pointer;
        text-decoration: underline;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 4px;
    }
</style>

<?php require __DIR__ . '/../common/footer.php'; ?>
