<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Services\OrderService;
use App\Helpers\FlashMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

class OrdersController extends BaseController
{
    private OrderService $orderService;

    // grabbing the order service from the container here
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->orderService = $container->get(OrderService::class);
    }


    // this is the main admin page to see every order in the system
    public function index(Request $request, Response $response): Response
    {
        $orders = $this->orderService->getAllOrders();

        $data = [
            'page_title' => 'Manage Orders',
            'orders' => $orders
        ];

        return $this->render($response, 'admin/orders/orderIndexView.php', $data);
    }


    // shows the full details of one specific order when i click view
    public function show(Request $request, Response $response, array $args): Response
    {
        $orderId = (int) $args['id'];
        $order = $this->orderService->getOrderDetails($orderId);

        if (empty($order)) {
            FlashMessage::error('Order not found.');
            return $this->redirect($request, $response, 'admin.orders.index');
        }

        $data = [
            'page_title' => 'Order Details #' . $orderId,
            'order' => $order
        ];

        return $this->render($response, 'admin/orders/orderShowView.php', $data);
    }

    // handles changing the order status (like pending -> delivered)
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $orderId = (int) $args['id'];
        $payload = $request->getParsedBody();
        $status = $payload['status'] ?? '';

        if (empty($status)) {
            FlashMessage::error('Invalid status.');
            return $this->redirect($request, $response, 'admin.orders.show', ['id' => $orderId]);
        }
// try to update it and show message based on if it worked or not
        if ($this->orderService->updateStatus($orderId, $status)) {
            FlashMessage::success('Order status updated successfully.');
        } else {
            FlashMessage::error('Failed to update order status.');
        }

        return $this->redirect($request, $response, 'admin.orders.show', ['id' => $orderId]);
    }
}
