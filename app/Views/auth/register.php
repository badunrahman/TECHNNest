<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Authentication System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center"><?= hs(trans('auth.register_title')) ?></h3>
                    </div>
                    <div class="card-body">
                        <?= App\Helpers\FlashMessage::render() ?>

                        <form method="POST" action="register">
                            <div class="mb-3">
                                <label for="first_name" class="form-label"><?= hs(trans('auth.first_name')) ?></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label"><?= hs(trans('auth.last_name')) ?></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label"><?= hs(trans('auth.email')) ?></label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label"><?= hs(trans('auth.email')) ?></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label"><?= hs(trans('auth.password')) ?></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">
                                    Password must be at least 8 characters long and contain at least one number.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label"><?= hs(trans('auth.confirm_password')) ?></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <input type="hidden" name="role" value="customer">

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><?= hs(trans('auth.register_btn')) ?></button>
                            </div>
                        </form>

                        <div class="mt-3 text-center">
                            <p><?= hs(trans('auth.already_have_account')) ?> <a href="login"><?= hs(trans('auth.login_btn')) ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
