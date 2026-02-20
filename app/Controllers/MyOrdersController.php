<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Services\OrderService;
use App\Domain\Models\OrdersModel;
use App\Helpers\FlashMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;
use App\Helpers\SessionManager;

class MyOrdersController extends BaseController
{
    private OrderService $orderService;
    private OrdersModel $ordersModel;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->orderService = $container->get(OrderService::class);
        $this->ordersModel = $container->get(OrdersModel::class);
    }


    // showing a list of all orders the logged in user has made
public function index(Request $request, Response $response): Response
{
    $userId = SessionManager::get('user_id');

    // if they arnt logged in, kick them out
    if (!$userId) {
        FlashMessage::error('Please login first.');
        return $this->redirect($request, $response, 'auth.login');
    }

    // fetching only the orders for this specific user
    $orders = $this->ordersModel->getOrdersByUserId((int)$userId);

    return $this->render($response, 'user/orders/index.php', [
        'page_title' => 'My Orders',
        'orders' => $orders
    ]);
}


// showing the details of a single order when they click 'view'
public function show(Request $request, Response $response, array $args): Response
{
    $userId = SessionManager::get('user_id');

    // security check again just in case
    if (!$userId) {
        FlashMessage::error('Please login first.');
        return $this->redirect($request, $response, 'auth.login');
    }

    $orderId = (int)($args['id'] ?? 0);
    // checking if the order id from the url is valid
    if ($orderId <= 0) {
        FlashMessage::error('Invalid order.');
        return $this->redirect($request, $response, 'my-orders.index');
    }
// grabing the full details (items, total, etc) from service
    $order = $this->orderService->getOrderDetails($orderId);

    // making sure the order exists and belongs to the user
       
    if (empty($order) || (int)$order['user_id'] !== (int)$userId) {
        FlashMessage::error('Order not found.');
        return $this->redirect($request, $response, 'my-orders.index');
    }

    return $this->render($response, 'user/orders/show.php', [
        'page_title' => 'Order #' . $orderId,
        'order' => $order
    ]);
}}
