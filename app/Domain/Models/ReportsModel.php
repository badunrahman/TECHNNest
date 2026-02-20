<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\Core\PDOService;

class ReportsModel extends BaseModel
{
    // just storing table names here so i dont mess them up later
    private const ORDERS_TABLE = 'orders';
    private const USERS_TABLE = 'users';

    public function __construct(PDOService $db_service)
    {
        parent::__construct($db_service);
    }

    // basic count of every order ever made
    public function getTotalOrders(): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . self::ORDERS_TABLE;
        return $this->count($sql);
    }

    // adds up the total_amount column to see how much cash we made
    public function getTotalRevenue(): float
    {
        $sql = 'SELECT SUM(total_amount) FROM ' . self::ORDERS_TABLE;

        $result = $this->selectOne('SELECT SUM(total_amount) as total FROM ' . self::ORDERS_TABLE);
        return (float) ($result['total'] ?? 0);
    }

    // counting actual users. logic is anyone who isnt an admin is a customer.
    public function getTotalCustomers(): int
    {
        
        $sql = 'SELECT COUNT(*) FROM ' . self::USERS_TABLE . ' WHERE role != :role';
        return $this->count($sql, ['role' => 'admin']);
    }
}
