<?php
use App\Helpers\ViewHelper;

$pageTitle = $data['page_title'] ?? 'My Orders';
$orders = $data['orders'] ?? [];

ViewHelper::loadHeader($pageTitle);
?>

<div class="container mt-5">
    <h1><?= hs($pageTitle) ?></h1>

    <div class="mb-3">
        <?= App\Helpers\FlashMessage::render() ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="5" class="text-center">You have placed no orders yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= hs((string)$order['id']) ?></td>
                            <td><?= hs($order['created_at']) ?></td>
                            <td>$<?= number_format((float)$order['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $order['status'] === 'Completed' ? 'success' : ($order['status'] === 'Pending' ? 'warning' : 'secondary') ?>">
                                    <?= hs($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= hs(APP_BASE_URL . '/my-orders/' . $order['id']) ?>" class="btn btn-sm btn-outline-primary">Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
ViewHelper::loadJsScripts();
ViewHelper::loadFooter();
?>
