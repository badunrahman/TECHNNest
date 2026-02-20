<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Models\ProductsModel;
use App\Helpers\SessionManager;

class CartService extends BaseService
{

    // key for the session so i dont typo it everywhere
    private const CART_SESSION_KEY = 'cart_items';
    private ProductsModel $productsModel;

    // need the products model to check stock and prices
    public function __construct(ProductsModel $productsModel)
    {
        $this->productsModel = $productsModel;
    }

    // helper to grab the cart array from session. returns empty array if nothing there.
    public function getCart(): array
    {
        return SessionManager::get(self::CART_SESSION_KEY, []);
    }


    // helper to save the array back to session after i change stuff
    public function saveCart(array $cart): void
    {
        SessionManager::set(self::CART_SESSION_KEY, $cart);
    }


    // logic to add an item. checks stock and validation first.
    public function add(int $productId, int $quantity = 1): array
    {
        $product = $this->productsModel->findById($productId);

        // make sure the product actually exists in db
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        // cant add negative numbers or zero
        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid quantity.'];
        }

        // checking stock so we dont oversell what we have

        if ($product['stock_quantity'] < $quantity) {
             return ['success' => false, 'message' => 'Not enough stock available.'];
        }

        $cart = $this->getCart();

        // if its already in the cart, just increase the number
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image_path' => $product['image_path'] ?? null,
                'quantity' => $quantity
            ];
        }

        $this->saveCart($cart);

        return ['success' => true, 'message' => 'Product added to cart.'];
    }


    // updating the quantity from the cart page input
    public function update(int $productId, int $quantity): array
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return ['success' => false, 'message' => 'Product not in cart.'];
        }

        if ($quantity <= 0) {
            unset($cart[$productId]);
            $this->saveCart($cart);
            return ['success' => true, 'message' => 'Item removed from cart.'];
        }



        $cart[$productId]['quantity'] = $quantity;
        $this->saveCart($cart);

        return ['success' => true, 'message' => 'Cart updated.'];
    }


    // delete specific item
    public function remove(int $productId): void
    {
        $cart = $this->getCart();
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->saveCart($cart);
        }
    }


    // wipe everything like after checkout
    public function clear(): void
    {
        SessionManager::remove(self::CART_SESSION_KEY);
    }


    // calculates the total cost of everything in the cart
    public function getTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }


    // counting total number of items for the little badge in the header
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
}
