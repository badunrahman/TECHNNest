<?php

declare(strict_types=1);

use App\Helpers\TranslationHelper;
use App\Middleware\LocaleMiddleware;
use App\Helpers\Core\AppSettings;
use App\Helpers\Core\JsonRenderer;
use App\Helpers\Core\PDOService;
use App\Middleware\ExceptionMiddleware;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Slim\Factory\AppFactory;
use Slim\App;
use Slim\Views\PhpRenderer;


$definitions = [
    AppSettings::class => function () {
        return new AppSettings(
            require_once __DIR__ . '/settings.php'
        );
    },
    App::class => function (ContainerInterface $container) {

        $app = AppFactory::createFromContainer($container);
        // echo APP_ROOT_DIR_NAME;exit;
        $app->setBasePath('/' . APP_ROOT_DIR_NAME);

        // Register web routes.
        (require_once BASE_PATH . '/app/Routes/web-routes.php')($app);
        //TODO: We will add it back later (register API routes).
        //(require_once realpath(__DIR__ . '/../app/Routes/api-routes.php'))($app);

        // Register middleware
        (require_once __DIR__ . '/middleware.php')($app);

        return $app;
    },
    PhpRenderer::class => function (ContainerInterface $container): PhpRenderer {
        $renderer = new PhpRenderer(APP_VIEWS_PATH);
        return $renderer;
    },
    PDOService::class => function (ContainerInterface $container): PDOService {
    $db = $container->get(AppSettings::class)->get('db') ?? [];

    // Normalize keys (support teacher-style: user/dbname, and your PDOService: username/database)
    $config = [
        'host'     => $db['host'] ?? '127.0.0.1',
        'port'     => $db['port'] ?? '3306',
        'database' => $db['database'] ?? $db['dbname'] ?? 'assignment_two',
        'username' => $db['username'] ?? $db['user'] ?? 'root',
        'password' => $db['password'] ?? $db['pass'] ?? '',
        'options'  => $db['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ];

    return new PDOService($config);
},

    // HTTP factories
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },
    ServerRequestFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },
    StreamFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },
    UriFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    // LoggerInterface::class => function (ContainerInterface $container) {
    //     $settings = $container->get('settings')['logger'];
    //     $logger = new Logger('app');

    //     $filename = sprintf('%s/app.log', $settings['path']);
    //     $level = $settings['level'];
    //     $rotatingFileHandler = new RotatingFileHandler($filename, 0, $level, true, 0777);
    //     $rotatingFileHandler->setFormatter(new LineFormatter(null, null, false, true));
    //     $logger->pushHandler($rotatingFileHandler);

    //     return $logger;
    // },
    ExceptionMiddleware::class => function (ContainerInterface $container) {
        $settings = $container->get(AppSettings::class)->get('error');
        return new ExceptionMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(JsonRenderer::class),
            null,
            (bool) $settings['display_error_details'],
        );
    },

    TranslationHelper::class => function (ContainerInterface $container): TranslationHelper {
    return new TranslationHelper(
        APP_LANG_PATH,      // Path to language files
        'en',               // Default locale (fallback language)
        ['en', 'fr']        // Available locales (languages your app supports)
    );
},

    LocaleMiddleware::class => function (ContainerInterface $container): LocaleMiddleware {
        return new LocaleMiddleware(
            $container->get(TranslationHelper::class)  // Inject TranslationHelper dependency
        );
    },

    // 2FA Models & Services
    \App\Domain\Models\TwoFactorAuthModel::class => function (ContainerInterface $container) {
        return new \App\Domain\Models\TwoFactorAuthModel($container->get(PDOService::class));
    },
    \App\Domain\Models\TrustedDeviceModel::class => function (ContainerInterface $container) {
        return new \App\Domain\Models\TrustedDeviceModel($container->get(PDOService::class));
    },
    \App\Controllers\TwoFactorController::class => function (ContainerInterface $container) {
        // Inject dependencies explicitly for controller
        return new \App\Controllers\TwoFactorController(
            $container,
            $container->get(\App\Domain\Models\TwoFactorAuthModel::class),
            $container->get(\App\Domain\Models\UserModel::class),
            $container->get(\App\Domain\Models\TrustedDeviceModel::class)
        );
    },
    \App\Middleware\TwoFactorMiddleware::class => function (ContainerInterface $container) {
        return new \App\Middleware\TwoFactorMiddleware(
            $container
        );
    },

    // Reports
    \App\Domain\Models\ReportsModel::class => function (ContainerInterface $container) {
        return new \App\Domain\Models\ReportsModel($container->get(PDOService::class));
    },
    \App\Controllers\ReportsController::class => function (ContainerInterface $container) {
        return new \App\Controllers\ReportsController($container);
    },

    // Wishlist
    \App\Domain\Models\WishlistsModel::class => function (ContainerInterface $container) {
        return new \App\Domain\Models\WishlistsModel($container->get(PDOService::class));
    },
    \App\Controllers\WishlistController::class => function (ContainerInterface $container) {
        return new \App\Controllers\WishlistController($container);
    },
];
return $definitions;
