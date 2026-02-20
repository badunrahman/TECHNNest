<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Services\CartService;
use App\Domain\Services\OrderService;
use App\Domain\Models\OrdersModel;
use App\Domain\Models\OrderItemsModel;
use App\Domain\Models\ProductsModel;
use App\Helpers\FlashMessage;
use App\Helpers\SessionManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

// define('APP_PUBLIC_PATH', __DIR__);

class CheckoutController extends BaseController
{
    private CartService $cartService;
    private OrdersModel $ordersModel;
    private OrderItemsModel $orderItemsModel;
    private ProductsModel $productsModel;
    private \App\Domain\Services\OrderValidationService $orderValidationService;


    // loading up all the models and services i need for checkout to work
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->cartService = $container->get(CartService::class);
        $this->ordersModel = $container->get(OrdersModel::class);
        $this->orderItemsModel = $container->get(OrderItemsModel::class);
        $this->productsModel = $container->get(ProductsModel::class);
        $this->orderValidationService = $container->get(\App\Domain\Services\OrderValidationService::class);
    }

    // shows the main checkout page where user enters info

    public function index(Request $request, Response $response): Response
    {
        $cart = $this->cartService->getCart();
        // double checkin if cart is empty
        if (empty($cart)) {
            FlashMessage::warning('Your cart is empty.');
            return $this->redirect($request, $response, 'cart.index');
        }

        $total = $this->cartService->getTotal();


        // trying to get user from request attribute first
        $user = $request->getAttribute('user');

      // if the middleware failed or user variable is missing, i manually grab it from session
        // so the form fields arnt blank
        if (!$user) {
            $user = [
                'first_name' => SessionManager::get('user_name'),
                'last_name'  => '',
                'email'      => SessionManager::get('user_email'),
                'id'         => SessionManager::get('user_id')
            ];
        }


        $data = [
            'page_title' => 'Checkout',
            'cart' => $cart,
            'total' => $total,
            'user' => $user
        ];

        return $this->render($response, 'cart/checkoutFormView.php', $data);
    }

    // this is the big function that handles the submit when they click place order
    public function process(Request $request, Response $response): Response
    {
        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            FlashMessage::error('Cart is empty.');
            return $this->redirect($request, $response, 'cart.index');
        }

        $payload = $request->getParsedBody();
        $totalAmount = $this->cartService->getTotal();

        // verifying the user is actually logged in before buying
        $user = $request->getAttribute('user');
        $userId = 0;

        if ($user && isset($user['id'])) {
            $userId = (int)$user['id'];
        } else {
            $userId = (int) SessionManager::get('user_id');
        }
// if id is still 0 they arnt logged in so kick them to login page
        if ($userId <= 0) {
            FlashMessage::error('You must be logged in to checkout.');

            return $this->redirect($request, $response, 'auth.login');
        }


        // validating inputs like address etc
        $errors = $this->orderValidationService->validateCheckout($payload, $totalAmount);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                FlashMessage::error($error);
            }
            return $this->redirect($request, $response, 'checkout.index');
        }

        // Create Order logic starts here
        try {
            // using db transaction so if something crashes halfway (like stock error)
            // we can undo everything so database doesnt get messy
            $this->ordersModel->beginTransaction();

            $orderId = $this->ordersModel->create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'Pending'
            ]);

            if (!$orderId) {
                throw new \Exception('Failed to create order. Please try again.');
            }

          // looping thru cart items to save them and update stock
            foreach ($cart as $item) {
                // decrementing the Stock
                $success = $this->productsModel->decrementStock((int)$item['id'], (int)$item['quantity']);
                if (!$success) {
                    throw new \Exception("Not enough stock for product: " . $item['name']);
                }
// saving the individual item to order_items table
                $this->orderItemsModel->create([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price']
                ]);
            }

            $this->ordersModel->commit();

            // claring the  Cart
            $this->cartService->clear();

            // redirectiong to confirmation
            return $this->redirect($request, $response, 'checkout.confirmation', ['id' => $orderId]);

        } catch (\Exception $e) {
            $this->ordersModel->rollBack();
            FlashMessage::error($e->getMessage());
            return $this->redirect($request, $response, 'checkout.index'); 
        }
    }

    // just displays the success page with the order id
    public function confirmation(Request $request, Response $response, array $args): Response
    {
        $orderId = (int) $args['id'];

        $data = [
            'page_title' => 'Order Confirmed',
            'order_id' => $orderId
        ];

        return $this->render($response, 'cart/checkoutConfirmationView.php', $data);
    }
}
