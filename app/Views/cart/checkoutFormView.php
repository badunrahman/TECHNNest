<?php

use App\Helpers\ViewHelper;

$pageTitle = $data['page_title'] ?? 'Checkout';
$cart = $data['cart'] ?? [];
$total = $data['total'] ?? 0.00;
$user = $data['user'] ?? [];

ViewHelper::loadHeader($pageTitle);
?>

<div class="container mt-5">

    <div class="row">

        <div class="col-md-8">

            <h4 class="mb-3">Shipping Address</h4>

            <div class="card mb-4">

                <div class="card-body">

                    <p><strong>Name:</strong> <?= hs(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></p>

                    <p><strong>Email:</strong> <?= hs($user['email'] ?? '') ?></p>

                    <p class="text-muted">Currently using account details for shipping.</p>
                </div>

            </div>

            <form action="<?= hs(APP_BASE_URL . '/checkout') ?>" method="POST">

                <h4 class="mb-3">Payment Method</h4>

                <div class="card mb-4">

                    <div class="card-body">
                        <div class="form-check">
                            <input id="credit" name="paymentMethod" type="radio" class="form-check-input" value="credit_card" checked required>
                            <label class="form-check-label" for="credit">Credit card</label>
                        </div>

                        <div class="form-check">
                            <input id="debit" name="paymentMethod" type="radio" class="form-check-input" value="debit_card" required>
                            <label class="form-check-label" for="debit">Debit card</label>
                        </div>

                    </div>
                </div>

                <button class="btn btn-primary btn-lg w-100" type="submit">Place Order</button>

            </form>
        </div>

        <div class="col-md-4">

            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-primary">Your cart</span>
                <span class="badge bg-primary rounded-pill"><?= count($cart) ?></span>
            </h4>

            <ul class="list-group mb-3">
                <?php foreach ($cart as $item): ?>
                    <li class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                            <h6 class="my-0"><?= hs($item['name']) ?></h6>
                            <small class="text-muted">Qty: <?= hs((string)$item['quantity']) ?></small>
                        </div>

                        <span class="text-muted">$<?= number_format((float)$item['price'] * $item['quantity'], 2) ?></span>
                    </li>

                <?php endforeach; ?>

                <li class="list-group-item d-flex justify-content-between">
                    <span>Total (USD)</span>

                    <strong>$<?= number_format((float)$total, 2) ?></strong>
                </li>
                
            </ul>
        </div>
    </div>
</div>

<?php
ViewHelper::loadJsScripts();
ViewHelper::loadFooter();
?>
