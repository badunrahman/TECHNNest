<?php

declare(strict_types=1);

define('APP_PUBLIC_PATH', __DIR__);
define('APP_VIEW_PATH', realpath(__DIR__ . '/../app/Views'));
//* This is the entry point of the application: the front controller of the Slim application.

// Launch the application's bootstrap process.
(require_once __DIR__ . '/../config/bootstrap.php')->run();
