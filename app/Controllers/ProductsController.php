<?php

namespace App\Controllers;

use App\Domain\Models\CategoriesModel;
use App\Domain\Models\ProductsModel;
use App\Domain\Services\ProductService;
use App\Helpers\FlashMessage;
use App\Helpers\SessionManager;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Domain\Models\WishlistsModel;

class ProductsController extends BaseController
{
    private const FLASH_OLD_KEY = 'products.old';
    private const FLASH_ERRORS_KEY = 'products.errors';

    public function __construct(
        Container $container,
        private ProductsModel $productsModel,
        private CategoriesModel $categoriesModel,
        private ProductService $productService,
        private WishlistsModel $wishlistsModel
    ) {
        parent::__construct($container);
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $products = $this->productsModel->getAllProducts();

        $data = [
            'page_title' => 'Products',
            'products' => $products,
        ];

        return $this->render($response, 'admin/products/productIndexView.php', $data);
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $categories = $this->categoriesModel->getAllCategories();

        $data = [
            'page_title' => 'Create Product',
            'categories' => $categories,
            'old' => $this->pullOldInput(),
            'errors' => $this->pullErrors(),
        ];

        return $this->render($response, 'admin/products/productCreateView.php', $data);
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getParsedBody() ?? [];
        $result = $this->productService->validateProduct($payload);

        if (!$result['success']) {
            SessionManager::set(self::FLASH_OLD_KEY, $result['data']);
            SessionManager::set(self::FLASH_ERRORS_KEY, $result['errors']);
            FlashMessage::error('Please fix the validation errors and try again.');

            return $this->redirect($request, $response, 'products.create');
        }

        // Handle File Upload
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['image'] ?? null;
        $imageFilename = null;

        if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            $uploadResult = \App\Helpers\FileUploadHelper::upload($uploadedFile, [
                'directory' => APP_PUBLIC_PATH . '/uploads/products',
                'allowedTypes' => ['image/jpeg', 'image/png'],
                'maxSize' => 5 * 1024 * 1024, // 5MB
                'filenamePrefix' => 'prod_'
            ]);

            if ($uploadResult->isSuccess()) {
                $data = $uploadResult->getData();
                $imageFilename = 'uploads/products/' . $data['filename'];
            } else {
                SessionManager::set(self::FLASH_OLD_KEY, $result['data']);
                FlashMessage::error($uploadResult->getMessage());
                return $this->redirect($request, $response, 'products.create');
            }
        }

        // Add image to data
        $productData = $result['data'];
        if ($imageFilename) {
            $productData['image_path'] = $imageFilename;
        }

        $this->productsModel->create($productData);
        FlashMessage::success('Product created successfully.');

        return $this->redirect($request, $response, 'products.index');
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $productId = isset($args['id']) ? (int) $args['id'] : 0;
        if ($productId <= 0) {
            FlashMessage::error('Invalid product selected.');
            return $this->redirect($request, $response, 'products.index');
        }

        $product = $this->productsModel->findById($productId);
        if ($product === false) {
            FlashMessage::error('Product not found.');
            return $this->redirect($request, $response, 'products.index');
        }

        $categories = $this->categoriesModel->getAllCategories();

        $data = [
            'page_title' => 'Edit Product',
            'product' => $product,
            'categories' => $categories,
            'old' => $this->pullOldInput(),
            'errors' => $this->pullErrors(),
        ];

        return $this->render($response, 'admin/products/productEditView.php', $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $productId = isset($args['id']) ? (int) $args['id'] : 0;
        if ($productId <= 0) {
            FlashMessage::error('Invalid product selected.');
            return $this->redirect($request, $response, 'products.index');
        }

        $payload = $request->getParsedBody() ?? [];
        $result = $this->productService->validateProduct($payload);

        if (!$result['success']) {
            SessionManager::set(self::FLASH_OLD_KEY, $result['data']);
            SessionManager::set(self::FLASH_ERRORS_KEY, $result['errors']);
            FlashMessage::error('Please fix the validation errors and try again.');

            return $this->redirect($request, $response, 'products.edit', ['id' => $productId]);
        }

        $product = $this->productsModel->findById($productId);
        if ($product === false) {
            FlashMessage::error('Product not found.');
            return $this->redirect($request, $response, 'products.index');
        }

        // dandles file Upload
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['image'] ?? null;
        $imageFilename = null;

        if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            $uploadResult = \App\Helpers\FileUploadHelper::upload($uploadedFile, [
                'directory' => APP_PUBLIC_PATH . '/uploads/products',
                'allowedTypes' => ['image/jpeg', 'image/png'],
                'maxSize' => 5 * 1024 * 1024, // 5MB
                'filenamePrefix' => 'prod_'
            ]);

            if ($uploadResult->isSuccess()) {
                $data = $uploadResult->getData();
                $imageFilename = 'uploads/products/' . $data['filename'];
            } else {
                SessionManager::set(self::FLASH_OLD_KEY, $result['data']);
                FlashMessage::error($uploadResult->getMessage());
                return $this->redirect($request, $response, 'products.edit', ['id' => $productId]);
            }
        }

        // adding image to data
        $productData = $result['data'];
        if ($imageFilename) {
            $productData['image_path'] = $imageFilename;
        }

        $this->productsModel->update($productId, $productData);
        FlashMessage::success('Product updated successfully.');

        return $this->redirect($request, $response, 'products.index');
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $productId = isset($args['id']) ? (int) $args['id'] : 0;
        if ($productId <= 0) {
            FlashMessage::error('Invalid product selected.');
            return $this->redirect($request, $response, 'products.index');
        }

        $product = $this->productsModel->findById($productId);
        if ($product === false) {
            FlashMessage::error('Product not found.');
            return $this->redirect($request, $response, 'products.index');
        }

        $this->productsModel->delete($productId);
        FlashMessage::success('Product deleted successfully.');

        return $this->redirect($request, $response, 'products.index');
    }

    private function pullOldInput(): array
    {
        $old = SessionManager::get(self::FLASH_OLD_KEY, []);
        SessionManager::remove(self::FLASH_OLD_KEY);

        return is_array($old) ? $old : [];
    }

    private function pullErrors(): array
    {
        $errors = SessionManager::get(self::FLASH_ERRORS_KEY, []);
        SessionManager::remove(self::FLASH_ERRORS_KEY);

        return is_array($errors) ? $errors : [];
    }

    public function search(Request $request, Response $response, array $args): Response
    {
        // TODO: Extract query parameters using $request->getQueryParams()
    // - Get 'q' parameter, trim it, default to empty string if not set
    // - Get 'category' parameter, convert to int if set, otherwise null
        $queryParams = $request->getQueryParams();
        $searchTerm = isset($queryParams['q']) ? trim($queryParams['q']) : '';
        $categoryId = isset($queryParams['category']) ? (int) $queryParams['category'] : null;

        // TODO: Validate search term length
    // - If longer than 100 characters, truncate it using substr()
        // Validate search term length
        if (strlen($searchTerm) > 100) {
            $searchTerm = substr($searchTerm, 0, 100);
        }
// TODO: Call $this->model->searchProducts() with search term and category ID
        // Call model to search products
        $products = $this->productsModel->searchProducts($searchTerm, $categoryId);

        // Add Wishlist status to products
        $userId = SessionManager::get('user_id');
        $wishlistedIds = $userId ? $this->wishlistsModel->getWishlistedProductIds((int)$userId) : [];

        foreach ($products as &$product) {
            $product['is_wishlisted'] = in_array($product['id'], $wishlistedIds);
        }
 // TODO: Create response data array with these keys:
        // Create response data array
        $data = [
            'success' => true,
            'count' => count($products),
            'query' => $searchTerm,
            'category_id' => $categoryId,
            'products' => $products,
        ];

        // TODO: Convert response data to JSON and write to response body
    // @see: https://www.slimframework.com/docs/v4/objects/response.html#returning-json
    // - Use json_encode()
    // - Use $response->getBody()->write()

        // Convert response data to JSON and write to response body
        $payload = json_encode($data);
        $response->getBody()->write($payload);

        // - Set Content-Type: application/json
    // - Set status code 200
        // Return response with proper headers
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function userIndex(Request $request, Response $response, array $args): Response
    {
         // TODO: Get all products using $this->model->getAllProducts()
        $products = $this->productsModel->getAllProducts();

        // TODO: Get all categories using $this->model->getAllCategories()
        $categories = $this->categoriesModel->getAllCategories();


        // TODO: Render the view 'products/userProductIndexView.php'
    // - Pass products, categories, and page_title in the data array
        $userId = SessionManager::get('user_id');
        $wishlistedIds = $userId ? $this->wishlistsModel->getWishlistedProductIds((int)$userId) : [];

        $data = [
            'page_title' => 'Products',
            'products' => $products,
            'categories' => $categories,
            'wishlisted_ids' => $wishlistedIds
        ];

        return $this->render($response, 'products/userProductIndexView.php', $data);
    }

}
