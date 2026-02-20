<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Authentication System</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center"><?= hs(trans('auth.login_title')) ?></h3>
                </div>
                <div class="card-body">

                    <?= App\Helpers\FlashMessage::render() ?>

                    <form method="POST" action="login">
                        <div class="mb-3">
                            <label for="identifier" class="form-label"><?= hs(trans('auth.email')) ?></label>
                            <input
                                type="text"
                                class="form-control"
                                id="identifier"
                                name="identifier"
                                placeholder="<?= hs(trans('auth.email')) ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label"><?= hs(trans('auth.password')) ?></label>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="<?= hs(trans('auth.password')) ?>"
                                required
                            >
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><?= hs(trans('auth.login_btn')) ?></button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        <p><?= hs(trans('auth.dont_have_account')) ?>
                            <a href="register"><?= hs(trans('auth.register_btn')) ?></a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
