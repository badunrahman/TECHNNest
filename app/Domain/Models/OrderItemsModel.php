<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\Core\PDOService;

class OrderItemsModel extends BaseModel
{
    private const TABLE = 'order_items';

    // standard setup to get the db connection working
    public function __construct(PDOService $db_service)
    {
        parent::__construct($db_service);
    }


    // grabbing all the items for a specific order
    public function getItemsByOrderId(int $orderId): array
    {

        // doing a join with products table cause i need the name and image to show in the order details
        // otherwise i just have random ID numbers
        $sql = 'SELECT oi.*, p.name as product_name, p.image_path
                FROM ' . self::TABLE . ' oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id';
        return $this->selectAll($sql, ['order_id' => $orderId]);
    }


    // saves a single line item to the database
    public function create(array $data): int|string
    {
        $sql = 'INSERT INTO ' . self::TABLE . ' (order_id, product_id, quantity, unit_price)
                VALUES (:order_id, :product_id, :quantity, :unit_price)';

        $this->execute($sql, [
            'order_id' => $data['order_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price']
        ]);

        return $this->lastInsertId();
    }
}
