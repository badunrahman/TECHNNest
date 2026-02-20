<?php
use App\Helpers\ViewHelper;


// setup vars, default to empty stuff if controller fails
$pageTitle = $data['page_title'] ?? 'Shopping Cart';
$cart = $data['cart'] ?? [];
$total = $data['total'] ?? 0.00;

ViewHelper::loadHeader($pageTitle);
?>


<div class="container mt-5">


    <h1><?= hs(trans('cart.title')) ?></h1>


    <div class="mb-3">

        <?= App\Helpers\FlashMessage::render() ?>
    </div>


    <?php if (empty($cart)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="<?= hs(APP_BASE_URL . '/products') ?>">Browse Products</a>
        </div>

    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th><?= hs(trans('cart.product')) ?></th>
                        <th><?= hs(trans('cart.price')) ?></th>
                        <th><?= hs(trans('cart.quantity')) ?></th>
                        <th><?= hs(trans('cart.total')) ?></th>
                        <th><?= hs(trans('cart.action')) ?></th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($cart as $productId => $item): ?>

                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?= hs(APP_BASE_URL . '/' . $item['image_path']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; margin-right: 15px;">
                                    <?php endif; ?>
                                    <?= hs($item['name']) ?>
                                </div>
                            </td>

                            <td>$<?= number_format((float)$item['price'], 2) ?></td>

                            <td>
                                <form action="<?= hs(APP_BASE_URL . '/cart/update') ?>" method="POST" class="d-flex" style="width: 150px;">
                                    <input type="hidden" name="product_id" value="<?= hs((string)$productId) ?>">
                                    <input type="number" name="quantity" value="<?= hs((string)$item['quantity']) ?>" min="1" class="form-control me-2">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?= hs(trans('cart.update')) ?></button>
                                </form>
                            </td>

                            <td>$<?= number_format((float)$item['price'] * $item['quantity'], 2) ?></td>
                            <td>
                                <a href="<?= hs(APP_BASE_URL . '/cart/remove/' . $productId) ?>" class="btn btn-sm btn-danger"><?= hs(trans('cart.remove')) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong><?= hs(trans('cart.total')) ?>:</strong></td>
                        <td><strong>$<?= number_format((float)$total, 2) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>

            </table>

        </div>

        <div class="d-flex justify-content-between">

            <a href="<?= hs(APP_BASE_URL . '/products') ?>" class="btn btn-outline-primary"><?= hs(trans('cart.continue_shopping')) ?></a>
            <div>
                <a href="<?= hs(APP_BASE_URL . '/cart/clear') ?>" class="btn btn-outline-danger me-2">Clear Cart</a>
                <a href="<?= hs(APP_BASE_URL . '/checkout') ?>" class="btn btn-success"><?= hs(trans('cart.checkout')) ?></a>
            </div>
        </div>
        
    <?php endif; ?>
</div>

<?php
ViewHelper::loadJsScripts();
ViewHelper::loadFooter();
?>
