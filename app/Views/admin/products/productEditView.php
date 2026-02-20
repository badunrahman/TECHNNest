<?php

use App\Helpers\ViewHelper;

$pageTitle = $data['page_title'] ?? 'Edit Product';
$product = $data['product'] ?? []; // The existing product data from the Database
$categories = $data['categories'] ?? []; // List of categories for the dropdown
$old = $data['old'] ?? [];
$errors = $data['errors'] ?? [];// Validation errors

ViewHelper::loadAdminHeader($pageTitle);


//  decide what to show in the inputs
// 1. $old: The user just typed this
// 2. $product: The value currently saved in the database.
// 3. '': Default empty string
$nameValue = $old['name'] ?? ($product['name'] ?? '');
$priceValue = $old['price'] ?? ($product['price'] ?? '');
$stockValue = $old['stock_quantity'] ?? ($product['stock_quantity'] ?? '');
$descriptionValue = $old['description'] ?? ($product['description'] ?? '');


// handles Category Selection
// check if the user just selected something ($old), otherwise use the DB value ($product)
$selectedCategory = isset($old['category_id'])
    ? (string) $old['category_id']
    : (string) ($product['category_id'] ?? '');

    //  options with the correct item selected
$options = ViewHelper::renderSelectOptions($categories, $selectedCategory, 'id', 'name');

// Product ID for the Form Action URL
$productId = (string) ($product['id'] ?? '');
?>



<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= hs($pageTitle) ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a class="btn btn-sm btn-outline-secondary" href="<?= hs(APP_ADMIN_URL . '/products') ?>">Back to Products</a>
        </div>
    </div>

    <div class="mb-3">
        <?= App\Helpers\FlashMessage::render() ?>
    </div>

    <form action="<?= hs(APP_ADMIN_URL . '/products/' . $productId) ?>" method="POST" enctype="multipart/form-data" novalidate>
        <div class="mb-3">

            <label for="product-name" class="form-label">Name</label>
            <input
                type="text"
                id="product-name"
                name="name"
                value="<?= hs($nameValue) ?>"
                class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                required
            >
            <?php if (!empty($errors['name'])) : ?>
                <div class="invalid-feedback d-block"><?= hs($errors['name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="row g-3">

            <div class="col-md-4">
                <label for="product-price" class="form-label">Price</label>
                <input
                    type="text"
                    id="product-price"
                    name="price"
                    value="<?= hs((string) $priceValue) ?>"
                    class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>"
                    required
                >
                <?php if (!empty($errors['price'])) : ?>
                    <div class="invalid-feedback d-block"><?= hs($errors['price']) ?></div>
                <?php endif; ?>
            </div>


            <div class="col-md-4">
                <label for="product-stock" class="form-label">Stock Quantity</label>
                <input
                    type="number"
                    id="product-stock"
                    name="stock_quantity"
                    value="<?= hs((string) $stockValue) ?>"
                    min="0"
                    class="form-control <?= isset($errors['stock_quantity']) ? 'is-invalid' : '' ?>"
                >
                <?php if (!empty($errors['stock_quantity'])) : ?>
                    <div class="invalid-feedback d-block"><?= hs($errors['stock_quantity']) ?></div>
                <?php endif; ?>
            </div>


            <div class="col-md-4">
                <label for="product-category" class="form-label">Category</label>
                <select
                    id="product-category"
                    name="category_id"
                    class="form-select"
                >
                    <?= $options ?>
                </select>
            </div>

        </div>

        <div class="mb-3 mt-3">

            <label for="product-image" class="form-label">Product Image</label>
            <?php if (!empty($product['image_path'])): ?>
                <div class="mb-2">
                    <img src="<?= hs(APP_BASE_URL . '/' . $product['image_path']) ?>" alt="Current Image" style="max-height: 100px;">
                </div>
            <?php endif; ?>

            <input
                type="file"
                id="product-image"
                name="image"
                class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>"
                accept="image/png, image/jpeg"
            >

            <div class="form-text">Leave empty to keep current image.</div>
            <?php if (!empty($errors['image'])) : ?>
                <div class="invalid-feedback d-block"><?= hs($errors['image']) ?></div>
            <?php endif; ?>

        </div>

        <div class="mb-3 mt-3">

            <label for="product-description" class="form-label">Description</label>
            <textarea
                id="product-description"
                name="description"
                rows="4"
                class="form-control"
            ><?= hs((string) $descriptionValue) ?></textarea>

        </div>



        <div class="d-flex gap-2">

            <button type="submit" class="btn btn-primary">Update Product</button>
            <a class="btn btn-outline-secondary" href="<?= hs(APP_ADMIN_URL . '/products') ?>">Cancel</a>
        </div>


    </form>
</main>
<?php
ViewHelper::loadJsScripts();
ViewHelper::loadAdminFooter();
?>
