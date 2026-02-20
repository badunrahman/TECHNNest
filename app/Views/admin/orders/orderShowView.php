<?php
use App\Helpers\ViewHelper;

// Extract variables passed from the controller, using defaults if they don't exist.
$pageTitle = $data['page_title'] ?? 'Order Details';
$order = $data['order'] ?? [];
$items = $order['items'] ?? [];

ViewHelper::loadAdminHeader($pageTitle);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

        <h1 class="h2"><?= hs($pageTitle) ?></h1>

        <div class="btn-toolbar mb-2 mb-md-0">

            <a href="<?= hs(APP_ADMIN_URL . '/orders') ?>" class="btn btn-sm btn-outline-secondary">Back to Orders</a>

        </div>
    </div>

    <div class="mb-3">
        <?= App\Helpers\FlashMessage::render() ?>
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
                                    <th>Subtotal</th>
                                </tr>
                            </thead>


                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image_path'])): ?>
                                                    <img src="<?= hs(APP_BASE_URL . '/' . $item['image_path']) ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
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
                    Customer Details
                </div>


                <div class="card-body">
                    <p><strong>Name:</strong> <?= hs($order['first_name'] . ' ' . $order['last_name']) ?></p>
                    <p><strong>Email:</strong> <?= hs($order['email']) ?></p>
                    <p><strong>Username:</strong> <?= hs($order['username']) ?></p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Order Status
                </div>


                <div class="card-body">
                    <form action="<?= hs(APP_ADMIN_URL . '/orders/' . $order['id'] . '/status') ?>" method="POST">
                        <div class="mb-3">

                            <label for="status" class="form-label">Current Status</label>
                            <select name="status" id="status" class="form-select">


                                <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>


                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
ViewHelper::loadJsScripts();
ViewHelper::loadAdminFooter();
?>
