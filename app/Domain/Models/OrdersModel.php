<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\Core\PDOService;

class OrdersModel extends BaseModel
{
    private const TABLE = 'orders';

    // standard db connection stuff
    public function __construct(PDOService $db_service)
    {
        parent::__construct($db_service);
    }


    // grabbing every single order in the system, useful for admin dashboard
    // joining users table so we see names instead of just user_ids
    public function getAllOrders(): array
    {
        $sql = 'SELECT o.*, u.first_name, u.last_name, u.email
                FROM ' . self::TABLE . ' o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC';
        return $this->selectAll($sql);
    }


    // finding one specific order by its id.
    // also grabbing the user info so  can show who bought it on the details page.
    public function findById(int $id): array|false
    {
        $sql = 'SELECT o.*, u.first_name, u.last_name, u.email, u.username
                FROM ' . self::TABLE . ' o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = :id
                LIMIT 1';
        return $this->selectOne($sql, ['id' => $id]);
    }

    // inserting the new order into the table
    // defaults status to pending if dont specify it
    public function create(array $data): int|string
    {
        $sql = 'INSERT INTO ' . self::TABLE . ' (user_id, total_amount, status)
                VALUES (:user_id, :total_amount, :status)';

        $this->execute($sql, [
            'user_id' => $data['user_id'],
            'total_amount' => $data['total_amount'],
            'status' => $data['status'] ?? 'Pending'
        ]);

        return $this->lastInsertId();
    }


    // just updates the status column, mostly used by admin to mark stuff as delivered
  public function updateStatus(int $id, string $status): bool
{
    $sql = 'UPDATE ' . self::TABLE . ' SET status = :status WHERE id = :id';

    $result = $this->execute($sql, [
        'status' => $status,
        'id' => $id
    ]);


    return (bool) $result;
}


// showing order history for a specific user profile
    public function getOrdersByUserId(int $userId): array
    {
        $sql = 'SELECT * FROM ' . self::TABLE . ' WHERE user_id = :user_id ORDER BY created_at DESC';
        return $this->selectAll($sql, ['user_id' => $userId]);
    }


    // Expose transaction methods publicly for the controller
    // these wrappers are here so the controller can manage the transaction
    // like if stock update fails, we gotta rollback the whole order to prevent bugs
    public function beginTransaction(): bool
    {
        return parent::beginTransaction();
    }

    public function commit(): bool
    {
        return parent::commit();
    }

    public function rollBack(): bool
    {
        return parent::rollBack();
    }
}
