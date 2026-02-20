<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\App;

// Define the application's base path once for consistent includes.
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader when available.
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // Composer dependencies are missing; the application will not fully run
    // but we avoid triggering fatal errors from requiring a non-existent file.
    trigger_error('Composer dependencies are missing. Run "composer install" in the project root.', E_USER_WARNING);
}

// Load the app's global constants.
require_once BASE_PATH . '/config/constants.php';
// Include the global functions that will be used across the app's various components.
require_once BASE_PATH . '/config/functions.php';

// Configure the DI container and load dependencies.
$definitions = require BASE_PATH . '/config/container.php';

// Build DI container instance
//@see https://php-di.org/
$container = (new ContainerBuilder())
    ->addDefinitions($definitions)
    ->build();

// Set up global translator for use in trans() helper function
global $translator;
$translator = $container->get(\App\Helpers\TranslationHelper::class);

// Create App instance
return $container->get(App::class);
