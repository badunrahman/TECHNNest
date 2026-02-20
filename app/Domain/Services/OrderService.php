<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Models\OrderItemsModel;
use App\Domain\Models\OrdersModel;

class OrderService extends BaseService
{
    private OrdersModel $ordersModel;
    private OrderItemsModel $orderItemsModel;

    // injecting the models i need to handle orders and their items
    public function __construct(OrdersModel $ordersModel, OrderItemsModel $orderItemsModel)
    {
        $this->ordersModel = $ordersModel;
        $this->orderItemsModel = $orderItemsModel;
    }


    // just a wrapper to get every order for the admin list
    public function getAllOrders(): array
    {
        return $this->ordersModel->getAllOrders();
    }


    // grabs the full info for a specific order including products
    public function getOrderDetails(int $orderId): array
    {
        $order = $this->ordersModel->findById($orderId);
        // if it doesnt exist, return empty array so controller handles it
        if (!$order) {
            return [];
        }

        // fetch all the individual products bought in this order
        $items = $this->orderItemsModel->getItemsByOrderId($orderId);
        $order['items'] = $items;

        return $order;
    }


    // changing the status like pending -> delivered
    public function updateStatus(int $orderId, string $status): bool
    {
        // Simple validation for status enum could be added here
        return $this->ordersModel->updateStatus($orderId, $status);
    }
}
