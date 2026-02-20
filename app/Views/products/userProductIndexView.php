<?php
use App\Helpers\ViewHelper;

$pageTitle = $data['page_title'] ?? 'Products';
$products = $data['products'] ?? [];
$categories = $data['categories'] ?? [];

ViewHelper::loadHeader($pageTitle);
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><?= hs(trans('products.title')) ?></h1>
        </div>
        <div class="col-md-6 d-flex gap-2 justify-content-end">
            <select id="category-filter" class="form-select w-auto">
                <option value=""><?= hs(trans('products.all_categories')) ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= hs((string)$category['id']) ?>"><?= hs($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="input-group w-auto">
                <input type="text" id="search-input" class="form-control" placeholder="<?= hs(trans('products.search_placeholder')) ?>" aria-label="Search products">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
            </div>
        </div>
    </div>

    <div class="row" id="product-list">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center">
                <p><?= hs(trans('products.no_products')) ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4 product-item">
                    <div class="card h-100">
                        <?php if (!empty($product['image_path'])): ?>
                            <img src="<?= hs(APP_BASE_URL . '/' . $product['image_path']) ?>" class="card-img-top" alt="<?= hs($product['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <span class="text-muted"><?= hs(trans('products.no_image')) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body position-relative">
                             <form action="<?= hs(APP_BASE_URL . '/wishlist/toggle') ?>" method="POST" class="position-absolute top-0 end-0 p-2">
                                <input type="hidden" name="product_id" value="<?= hs((string)$product['id']) ?>">
                                <button type="submit" class="btn btn-link p-0 text-decoration-none" title="Wishlist">
                                    <?php 
                                        $isWishlisted = in_array($product['id'], $data['wishlisted_ids'] ?? []);
                                    ?>
                                    <i class="bi <?= $isWishlisted ? 'bi-heart-fill text-danger' : 'bi-heart text-muted' ?>" style="font-size: 1.5rem;"></i>
                                </button>
                            </form>
                            <h5 class="card-title"><?= hs($product['name']) ?></h5>
                            <p class="card-text text-muted"><?= hs($product['category_name'] ?? 'Uncategorized') ?></p>
                            <p class="card-text"><?= hs(substr($product['description'], 0, 100)) ?>...</p>
                            <h6 class="card-subtitle mb-2 text-primary">$<?= number_format((float)$product['price'], 2) ?></h6>
                        </div>
                            <form action="<?= hs(APP_BASE_URL . '/cart/add') ?>" method="POST" class="d-grid gap-2 px-3 pb-3">
                                <input type="hidden" name="product_id" value="<?= hs((string)$product['id']) ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary"><?= hs(trans('products.add_to_cart')) ?></button>
                            </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
  const APP_BASE_URL = <?= json_encode(rtrim(APP_BASE_URL, '/')) ?>;
</script>
<script src="<?= hs(APP_BASE_URL . '/assets/js/search.js') ?>"></script>

<?php
ViewHelper::loadFooter();
?>
