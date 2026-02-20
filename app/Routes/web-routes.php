<?php

declare(strict_types=1);

/**
 * This file contains the routes for the web application.
 */

use App\Controllers\AuthController;
use App\Controllers\CategoriesController;
use App\Controllers\DashboardController;
use App\Controllers\DemoController;
use App\Controllers\FlashDemoController;
use App\Controllers\HomeController;
use App\Controllers\ProductsController;
use App\Controllers\UploadController;
use App\Helpers\SessionManager;
use App\Middleware\AdminAuthMiddleware;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpNotFoundException;

return static function (App $app): void {

    //ROuting group for the amdin panel

    $app->group('/admin', function ($group) use ($app) {
        // Admin Dashboard
        $group->get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->setName('admin.dashboard');

        // Product CRUD routes
        $group->get(
            '/products',
            [ProductsController::class, 'index']
        )->setName('products.index');

        $group->get(
            '/products/create',
            [ProductsController::class, 'create']
        )->setName('products.create');

        $group->post(
            '/products',
            [ProductsController::class, 'store']
        );

        $group->get(
            '/products/{id}/edit',
            [ProductsController::class, 'edit']
        )->setName('products.edit');

        $group->post(
            '/products/{id}',
            [ProductsController::class, 'update']
        );

        $group->get(
            '/products/{id}/delete',
            [ProductsController::class, 'delete']
        )->setName('products.delete');

        // Category CRUD routes
        $group->get(
            '/categories',
            [CategoriesController::class, 'index']
        )->setName('categories.index');

        $group->get(
            '/categories/create',
            [CategoriesController::class, 'create']
        )->setName('categories.create');

        $group->post(
            '/categories',
            [CategoriesController::class, 'store']
        );

        $group->get(
            '/categories/{id}/edit',
            [CategoriesController::class, 'edit']
        )->setName('categories.edit');

        $group->post(
            '/categories/{id}',
            [CategoriesController::class, 'update']
        );

        $group->get(
            '/categories/{id}/delete',
            [CategoriesController::class, 'delete']
        )->setName('categories.delete');

        $group->get('/upload', [UploadController::class, 'index'])->setName('upload.index');

        $group->post('/upload', [UploadController::class, 'upload'])->setName('upload.upload');

        // Order Management Routes
        $group->get('/orders', [\App\Controllers\OrdersController::class, 'index'])->setName('admin.orders.index');
        $group->get('/orders/{id}', [\App\Controllers\OrdersController::class, 'show'])->setName('admin.orders.show');
        $group->post('/orders/{id}/status', [\App\Controllers\OrdersController::class, 'updateStatus'])->setName('admin.orders.status');

        // Reports Route
        $group->get('/reports', [\App\Controllers\ReportsController::class, 'index'])->setName('admin.reports');
        $group->get('/reports/pdf/products', [\App\Controllers\ReportsController::class, 'exportProductsPdf'])->setName('admin.reports.products.pdf');

    })->add(\App\Middleware\TwoFactorMiddleware::class)
      ->add(AdminAuthMiddleware::class)
      ->add(AuthMiddleware::class);

    // 2FA Routes
    $app->group('/2fa', function ($group) {
        $group->get('/setup', [\App\Controllers\TwoFactorController::class, 'showSetup'])
            ->setName('2fa.setup');

        $group->post('/setup', [\App\Controllers\TwoFactorController::class, 'verifyAndEnable'])
            ->setName('2fa.enable');

        $group->get('/disable', [\App\Controllers\TwoFactorController::class, 'showDisable'])
            ->setName('2fa.disable');

        $group->post('/disable', [\App\Controllers\TwoFactorController::class, 'disable'])
            ->setName('2fa.disable.post');

        $group->get('/verify', [\App\Controllers\TwoFactorController::class, 'showVerify'])
            ->setName('2fa.verify');

        $group->post('/verify', [\App\Controllers\TwoFactorController::class, 'verify'])
            ->setName('2fa.verify.post');
    })->add(AuthMiddleware::class);


    $app->get('/', [HomeController::class, 'index'])
        ->setName('home.index');

    $app->get('/home', [HomeController::class, 'index'])
        ->setName('home.index');

    // authentication Routes
    $app->get('/register', [AuthController::class, 'register'])->setName('auth.register');
    $app->post('/register', [AuthController::class, 'store']);
    $app->get('/login', [AuthController::class, 'login'])->setName('auth.login');
    $app->post('/login', [AuthController::class, 'authenticate']);
    $app->get('/logout', [AuthController::class, 'logout'])->setName('auth.logout');
    $app->get('/dashboard', [AuthController::class, 'dashboard'])->setName('user.dashboard')->add(AuthMiddleware::class);

    // user Product List & Search
    $app->get('/products', [ProductsController::class, 'userIndex'])
        ->setName('products.list');
    $app->get('/api/products/search', [ProductsController::class, 'search'])->setName('products.search');

    // cart Routes
    $app->get('/cart', [\App\Controllers\CartController::class, 'index'])->setName('cart.index');
    $app->post('/cart/add', [\App\Controllers\CartController::class, 'add'])->setName('cart.add');
    $app->post('/cart/update', [\App\Controllers\CartController::class, 'update'])->setName('cart.update');
    $app->get('/cart/remove/{id}', [\App\Controllers\CartController::class, 'remove'])->setName('cart.remove');
    $app->get('/cart/clear', [\App\Controllers\CartController::class, 'clear'])->setName('cart.clear');

    // checkout routes (Protected)
    $app->get('/checkout', [\App\Controllers\CheckoutController::class, 'index'])->setName('checkout.index')->add(AuthMiddleware::class);
    $app->post('/checkout', [\App\Controllers\CheckoutController::class, 'process'])->add(AuthMiddleware::class);
    $app->get('/checkout/confirmation/{id}', [\App\Controllers\CheckoutController::class, 'confirmation'])->setName('checkout.confirmation')->add(AuthMiddleware::class);

    // wishlist Routes
    $app->get('/wishlist', [\App\Controllers\WishlistController::class, 'index'])->setName('wishlist.index')->add(AuthMiddleware::class);
    $app->post('/wishlist/toggle', [\App\Controllers\WishlistController::class, 'toggle'])->setName('wishlist.toggle')->add(AuthMiddleware::class);

    // my Orders Routes
    $app->get('/my-orders', [\App\Controllers\MyOrdersController::class, 'index'])->setName('my-orders.index')->add(AuthMiddleware::class);
    $app->get('/my-orders/{id}', [\App\Controllers\MyOrdersController::class, 'show'])->setName('my-orders.show')->add(AuthMiddleware::class);




    $app->get('/error', function (Request $request, Response $response, $args) {
        throw new HttpNotFoundException($request, "Something went wrong");
    });

    $app->get('/test-session', function ($request, $response) {

        $counter = SessionManager::get('counter', 0) + 1;
        SessionManager::set('counter', $counter);

        $response->getBody()->write("Counter: " . $counter);

        return $response;
    });

    $app->get('/demo/counter', [DemoController::class, 'counter'])->setName('demo.counter');
    $app->post('/demo/reset', [DemoController::class, 'resetCounter'])->setName('demo.reset');


    // flash message demo routes
    $app->get('/flash-demo', [FlashDemoController::class, 'index'])->setName('flash.demo');
    $app->post('/flash-demo/success', [FlashDemoController::class, 'success'])->setName('flash.success');
    $app->post('/flash-demo/error', [FlashDemoController::class, 'error'])->setName('flash.error');
    $app->post('/flash-demo/info', [FlashDemoController::class, 'info'])->setName('flash.info');
    $app->post('/flash-demo/warning', [FlashDemoController::class, 'warning'])->setName('flash.warning');
    $app->post('/flash-demo/multiple', [FlashDemoController::class, 'multiple'])->setName('flash.multiple');
};
