<?php
use App\Helpers\ViewHelper;

ViewHelper::loadHeader($data['page_title'] ?? 'My Wishlist');
$wishlistItems = $data['wishlist_items'] ?? [];
?>

<div class="container py-5">
    <h1 class="mb-4">My Wishlist</h1>

    <?php if (empty($wishlistItems)): ?>
        <div class="alert alert-info">
            Your wishlist is empty. <a href="<?= APP_BASE_URL ?>/products">Browse products</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($item['image_path'])): ?>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="<?= hs($item['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                                <span>No Image</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= hs($item['name']) ?></h5>
                            <p class="text-muted small mb-2"><?= hs($item['category_name'] ?? 'Uncategorized') ?></p>
                            <p class="card-text fw-bold">$<?= number_format((float)$item['price'], 2) ?></p>
                            
                            <div class="d-grid gap-2">
                                <!-- Add to Cart -->
                                <form action="<?= APP_BASE_URL ?>/cart/add" method="POST">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </form>

                                <!-- Remove -->
                                <form action="<?= APP_BASE_URL ?>/wishlist/toggle" method="POST">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php ViewHelper::loadFooter(); ?>
