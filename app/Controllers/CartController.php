<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Services\CartService;
use App\Helpers\FlashMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

class CartController extends BaseController
{
    private CartService $cartService;

    // grabbing the cart service from the container here so i can use it in the functions
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->cartService = $container->get(CartService::class);
    }

    // this just loads the main cart page
    //it gets the l9ist of items and total price and send it tot he views
    public function index(Request $request, Response $response): Response
    {
        $cart = $this->cartService->getCart();
        $total = $this->cartService->getTotal();

        $data = [
            'page_title' => 'Shopping Cart',
            'cart' => $cart,
            'total' => $total
        ];

        return $this->render($response, 'cart/cartItemsView.php', $data);
    }


    //add item, it checks for product if and quantity
    public function add(Request $request, Response $response): Response
    {
        $payload = $request->getParsedBody();
        $productId = (int) ($payload['product_id'] ?? 0);
        $quantity = (int) ($payload['quantity'] ?? 1);

        $result = $this->cartService->add($productId, $quantity);

        //checking if it actually worked or nah
        if ($result['success']) {
            FlashMessage::success($result['message']);
        } else {
            FlashMessage::error($result['message']);
        }

        // trying to send back ot the page thery were before and if that dont work goes to the product list
        $referer = $request->getHeaderLine('Referer');
        if ($referer) {
             return $response->withHeader('Location', $referer)->withStatus(302);
        }
// reload the page to show the new total price
        return $this->redirect($request, $response, 'products.list');
    }

    // updates the qty when the user changes the number in the input box
    public function update(Request $request, Response $response): Response
    {
        $payload = $request->getParsedBody();
        $productId = (int) ($payload['product_id'] ?? 0);
        $quantity = (int) ($payload['quantity'] ?? 0);

        $result = $this->cartService->update($productId, $quantity);

        if ($result['success']) {
            FlashMessage::success($result['message']);
        } else {
            FlashMessage::error($result['message']);
        }

        return $this->redirect($request, $response, 'cart.index');
    }


    // just deletes the item based on the ID passed in the url args
    public function remove(Request $request, Response $response, array $args): Response
    {
        $productId = (int) $args['id'];
        $this->cartService->remove($productId);

        FlashMessage::success('Item removed from cart.');

        return $this->redirect($request, $response, 'cart.index');
    }

    // completely wipes the cart session
    public function clear(Request $request, Response $response): Response
    {
        $this->cartService->clear();
        FlashMessage::success('Cart cleared.');
        return $this->redirect($request, $response, 'cart.index');
    }
}
