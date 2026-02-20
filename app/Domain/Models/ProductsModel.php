<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\Core\PDOService;

class ProductsModel extends BaseModel
{
    private const TABLE = 'products';

    public function __construct(PDOService $db_service)
    {
        parent::__construct($db_service);
    }

    /**
     * Retrieve all products ordered by the most recent creation date.
     */

    public function getAllProducts(): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, pi.file_path AS image_path '
            . 'FROM ' . self::TABLE . ' p '
            . 'LEFT JOIN categories c ON c.id = p.category_id '
            . 'LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 '
            . 'ORDER BY p.created_at DESC';

        return $this->selectAll($sql);
    }

    /**
     * Find a product by its primary key.
     */
    public function findById(int $id): array|false
    {
        $sql = 'SELECT p.*, c.name AS category_name, pi.file_path AS image_path '
            . 'FROM ' . self::TABLE . ' p '
            . 'LEFT JOIN categories c ON c.id = p.category_id '
            . 'LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 '
            . 'WHERE p.id = :id '
            . 'LIMIT 1';

        return $this->selectOne($sql, ['id' => $id]);
    }

    /**
     * Persist a new product record.
     */
    public function create(array $data): int|string
    {
        $sql = 'INSERT INTO ' . self::TABLE
            . ' (category_id, name, description, price, stock_quantity) '
            . 'VALUES (:category_id, :name, :description, :price, :stock_quantity)';

        $this->execute($sql, [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'] ?? 0,
        ]);

        $productId = $this->lastInsertId();

        if (!empty($data['image_path'])) {
            $sqlImage = 'INSERT INTO product_images (product_id, file_path, is_primary) VALUES (:product_id, :file_path, 1)';
            $this->execute($sqlImage, [
                'product_id' => $productId,
                'file_path' => $data['image_path']
            ]);
        }

        return $productId;
    }

    /**
     * updating an existing product record.
     */
    public function update(int $id, array $data): int
    {
        $sql = 'UPDATE ' . self::TABLE
            . ' SET category_id = :category_id, name = :name, description = :description, '
            . 'price = :price, stock_quantity = :stock_quantity '
            . 'WHERE id = :id';

        $result = $this->execute($sql, [
            'id' => $id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'] ?? 0,
        ]);

        if (!empty($data['image_path'])) {
            // checking if primary image exists


            $checkSql = 'SELECT id FROM product_images WHERE product_id = :product_id AND is_primary = 1 LIMIT 1';
            $existing = $this->selectOne($checkSql, ['product_id' => $id]);

            if ($existing) {
                $updateImageSql = 'UPDATE product_images SET file_path = :file_path WHERE id = :id';
                $this->execute($updateImageSql, [
                    'file_path' => $data['image_path'],
                    'id' => $existing['id']
                ]);
            } else {
                $insertImageSql = 'INSERT INTO product_images (product_id, file_path, is_primary) VALUES (:product_id, :file_path, 1)';
                $this->execute($insertImageSql, [
                    'product_id' => $id,
                    'file_path' => $data['image_path']
                ]);
            }
        }

        return (int) $result;
    }

    /**
     * deleteing a product by its primary key.
     */
    public function delete(int $id): int
    {
        $sql = 'DELETE FROM ' . self::TABLE . ' WHERE id = :id';

        return $this->execute($sql, ['id' => $id]);
    }




    // for the live Search AJAX

    public function searchProducts(string $searchTerm = '', ?int $categoryId = null): array
    {
        // creating base SQL query with LEFT JOINs
        $sql = "
        SELECT
            p.id,
            p.name,
            p.description,
            p.price,
            p.stock_quantity,
            c.name AS category_name,
            c.id AS category_id,
            pi.file_path AS image_path
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
        WHERE 1=1
        ";

        $params = [];

        if ($searchTerm !== '') {
            // Useing two different parameter names :search_name and :search_desc
            // was giving me error before ffr the same name
            $sql .= " AND (p.name LIKE :search_name OR p.description LIKE :search_desc)";

            // adding both parameters to the array
            $wildcardSearch = '%' . $searchTerm . '%';
            $params['search_name'] = $wildcardSearch;
            $params['search_desc'] = $wildcardSearch;
        }

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        $sql .= " GROUP BY p.id ORDER BY p.name ASC";

        return $this->selectAll($sql, $params);
    }

    /**
     * decrementing stock quantity for a product.
     * prevents stock from going below zero.
     *
     *
     */
    public function decrementStock(int $id, int $quantity): bool
    {
        // Using two different parameter names :qty_sub and :qty_check
        // because we cannot reuse the same name twice in one query with this setup, was messing up my shit
        $sql = 'UPDATE ' . self::TABLE
            . ' SET stock_quantity = stock_quantity - :qty_sub'
            . ' WHERE id = :id AND stock_quantity >= :qty_check';

        $params = [
            'id' => $id,
            'qty_sub' => $quantity,   // Parameter 1
            'qty_check' => $quantity  // Parameter 2 
        ];

        $affectedRows = $this->execute($sql, $params);
        return $affectedRows > 0;
    }
}
