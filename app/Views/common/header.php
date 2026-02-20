<?php
    global $translator;
    $languageLabels = ['en' => 'English', 'fr' => 'Français'];
    $currentLocale = isset($translator) ? $translator->getLocale() : 'en';
    $currentLanguageLabel = $languageLabels[$currentLocale] ?? $languageLabels['en'];
?>
<!DOCTYPE html>
<html lang="<?= hs($currentLocale) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?= hs(APP_BASE_URL) ?>"><?= hs(trans('app.name')) ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= hs(APP_BASE_URL . '/home') ?>"><?= hs(trans('nav.home')) ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= hs(APP_BASE_URL . '/products') ?>"><?= hs(trans('nav.products')) ?></a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= hs(APP_BASE_URL . '/cart') ?>">
                            <i class="bi bi-cart"></i> <?= hs(trans('nav.cart')) ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= hs(trans('nav.language')) ?>: <?= hs($currentLanguageLabel) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="?lang=en">English</a></li>
                            <li><a class="dropdown-item" href="?lang=fr">Français</a></li>
                        </ul>
                    </li>
                    <?php if (\App\Helpers\SessionManager::has('user_id')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= hs(APP_BASE_URL . '/wishlist') ?>">
                                <i class="bi bi-heart"></i> <?= hs(trans('nav.wishlist') ?: 'Wishlist') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= hs(APP_BASE_URL . '/my-orders') ?>"><?= hs(trans('nav.my_orders')) ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= hs(APP_BASE_URL . '/admin/dashboard') ?>"><?= hs(trans('nav.dashboard')) ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= hs(APP_BASE_URL . '/logout') ?>"><?= hs(trans('nav.logout')) ?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= hs(APP_BASE_URL . '/login') ?>"><?= hs(trans('nav.login')) ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>