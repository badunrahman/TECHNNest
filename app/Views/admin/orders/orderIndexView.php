<?php
use App\Helpers\ViewHelper;

$pageTitle = $data['page_title'] ?? 'Manage Orders';
$orders = $data['orders'] ?? [];
// loading the sidebar and top nav
ViewHelper::loadAdminHeader($pageTitle);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= hs($pageTitle) ?></h1>
    </div>

    <div class="mb-3">
        <?= App\Helpers\FlashMessage::render() ?>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>


                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>


            </thead>
            <tbody>

                <?php if (empty($orders)): ?>
                    <tr>

                        <td colspan="6" class="text-center">No orders found.</td>
                    </tr>

                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= hs((string)$order['id']) ?></td>
                            <td>
                                <?= hs($order['first_name'] . ' ' . $order['last_name']) ?>
                                <br>
                                <small class="text-muted"><?= hs($order['email']) ?></small>
                            </td>
                            <td>$<?= number_format((float)$order['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $order['status'] === 'Completed' ? 'success' : ($order['status'] === 'Pending' ? 'warning' : 'secondary') ?>">
                                    <?= hs($order['status']) ?>
                                </span>
                            </td>
                            <td><?= hs($order['created_at']) ?></td>
                            <td>
                                <a href="<?= hs(APP_ADMIN_URL . '/orders/' . $order['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php
ViewHelper::loadJsScripts();
ViewHelper::loadAdminFooter();
?>
