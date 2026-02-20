<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\Core\PDOService;
use PDOException;

class WishlistsModel extends BaseModel
{
    private const TABLE = 'wishlists';
    private const PRODUCTS_TABLE = 'products';
    private const PRODUCT_IMAGES_TABLE = 'product_images';

    public function __construct(PDOService $db_service)
    {
        parent::__construct($db_service);
    }

    // tries to save the like to the db
    public function add(int $userId, int $productId): bool
    {
        $sql = 'INSERT INTO ' . self::TABLE . ' (user_id, product_id) VALUES (:user_id, :product_id)';
        try {
            return (bool) $this->execute($sql, ['user_id' => $userId, 'product_id' => $productId]);
        } catch (PDOException $e) {
            // integrity constraint violation: 1062 Duplicate entry
            // if they spam the button and its already there, just say true so it doesnt crash
            if ($e->getCode() == 23000) {
                return true;
            }
            throw $e;
        }
    }

    // removes the item from wishlist
    public function remove(int $userId, int $productId): bool
    {
        $sql = 'DELETE FROM ' . self::TABLE . ' WHERE user_id = :user_id AND product_id = :product_id';
        return (bool) $this->execute($sql, ['user_id' => $userId, 'product_id' => $productId]);
    }

    public function isWishlisted(int $userId, int $productId): bool
    {
        $sql = 'SELECT count(*) FROM ' . self::TABLE . ' WHERE user_id = :user_id AND product_id = :product_id';
        return $this->count($sql, ['user_id' => $userId, 'product_id' => $productId]) > 0;
    }


    // gets the full product details for the wishlist page
    // doing joins here so i can show image, price, name etc
    public function getByUserId(int $userId): array
    {
        $sql = 'SELECT p.*, pi.file_path as image_path, c.name as category_name
                FROM ' . self::TABLE . ' w
                JOIN ' . self::PRODUCTS_TABLE . ' p ON w.product_id = p.id
                LEFT JOIN ' . self::PRODUCT_IMAGES_TABLE . ' pi ON pi.product_id = p.id AND pi.is_primary = 1
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE w.user_id = :user_id
                ORDER BY w.created_at DESC';

        return $this->selectAll($sql, ['user_id' => $userId]);
    }

    // useful to grab just the IDs so i can quickly check status in loops
    public function getWishlistedProductIds(int $userId): array
    {
        $sql = 'SELECT product_id FROM ' . self::TABLE . ' WHERE user_id = :user_id';
        $rows = $this->selectAll($sql, ['user_id' => $userId]);
        return array_column($rows, 'product_id');
    }
}
