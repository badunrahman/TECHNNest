<?php
use App\Helpers\ViewHelper;

$pageTitle = $data['page_title'] ?? 'Order Confirmed';
$orderId = $data['order_id'] ?? 0;

ViewHelper::loadHeader($pageTitle);
?>


<div class="container mt-5 text-center">
    <div class="card p-5">

        <div class="mb-4">

            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        </div>

        <h1>Thank You!</h1>

        <p class="lead">Your order #<?= hs((string)$orderId) ?> has been placed successfully.</p>

        <p>You will receive an email confirmation shortly.</p>
        <div class="mt-4">

            <a href="<?= hs(APP_BASE_URL . '/products') ?>" class="btn btn-primary me-2">Continue Shopping</a>

            <a href="<?= hs(APP_BASE_URL . '/my-orders') ?>" class="btn btn-outline-secondary">View My Orders</a>
            
        </div>
    </div>
</div>

<?php
ViewHelper::loadJsScripts();
ViewHelper::loadFooter();
?>
