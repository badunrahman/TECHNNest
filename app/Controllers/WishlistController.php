<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Models\WishlistsModel;
use App\Helpers\FlashMessage;
use App\Helpers\SessionManager;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class WishlistController extends BaseController
{
    private WishlistsModel $wishlistsModel;

    // same this getting the modoel ready so i use it
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->wishlistsModel = $container->get(WishlistsModel::class);
    }


    // shows the page with all the items the user saved
    public function index(Request $request, Response $response): Response
    {
        $userId = (int) SessionManager::get('user_id');

        // fetching the list from db for this specific user
        $items = $this->wishlistsModel->getByUserId($userId);

        $data = [
            'page_title' => 'My Wishlist',
            'wishlist_items' => $items
        ];

        return $this->render($response, 'wishlist/indexView.php', $data);
    }

    // logic for the heart button click or not click
    public function toggle(Request $request, Response $response): Response
    {
        $payload = $request->getParsedBody();
        $productId = (int) ($payload['product_id'] ?? 0);
        $userId = (int) SessionManager::get('user_id');

        // see if the product if is gvalid
        if ($productId <= 0) {
            FlashMessage::error('Invalid product.');
            return $this->redirect($request, $response, 'products.list');
        }

        // checking if its already in the list
        if ($this->wishlistsModel->isWishlisted($userId, $productId)) {
            $this->wishlistsModel->remove($userId, $productId);
            FlashMessage::success('Removed from wishlist.');
        } else {
            $this->wishlistsModel->add($userId, $productId);
            FlashMessage::success('Added to wishlist.');
        }

        // redirecting back to where the user came from  so the page doesnt jump around
        // if that fails, just go to main wishlist page
        $referer = $request->getHeaderLine('Referer');
        if ($referer) {
            return $response->withHeader('Location', $referer)->withStatus(302);
        }

        return $this->redirect($request, $response, 'wishlist.index');
    }
}
