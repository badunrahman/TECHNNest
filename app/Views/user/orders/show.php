<?php
use App\Helpers\ViewHelper;

$pageTitle = $data['page_title'] ?? 'Order Details';
$order = $data['order'] ?? [];
$items = $order['items'] ?? [];

ViewHelper::loadHeader($pageTitle);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= hs($pageTitle) ?></h1>
        <a href="<?= hs(APP_BASE_URL . '/my-orders') ?>" class="btn btn-outline-secondary">Back to Orders</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    Items
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image_path'])): ?>
                                                    <img src="<?= hs(APP_BASE_URL . '/' . $item['image_path']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; margin-right: 15px;">
                                                <?php endif; ?>
                                                <?= hs($item['product_name']) ?>
                                            </div>
                                        </td>
                                        <td>$<?= number_format((float)$item['unit_price'], 2) ?></td>
                                        <td><?= hs((string)$item['quantity']) ?></td>
                                        <td>$<?= number_format((float)$item['unit_price'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>$<?= number_format((float)$order['total_amount'], 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    Order Summary
                </div>
                <div class="card-body">
                    <p><strong>Order ID:</strong> <?= hs((string)$order['id']) ?></p>
                    <p><strong>Date:</strong> <?= hs($order['created_at']) ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-<?= $order['status'] === 'Completed' ? 'success' : ($order['status'] === 'Pending' ? 'warning' : 'secondary') ?>">
                            <?= hs($order['status']) ?>
                        </span>
                    </p>
                    <p><strong>Total Amount:</strong> $<?= number_format((float)$order['total_amount'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
ViewHelper::loadJsScripts();
ViewHelper::loadFooter();
?>
