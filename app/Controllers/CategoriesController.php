<?php

namespace App\Controllers;

use App\Domain\Models\CategoriesModel;
use App\Domain\Services\CategoryService;
use App\Helpers\FlashMessage;
use App\Helpers\SessionManager;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoriesController extends BaseController
{
    // keys for the session flash data so i dont make typos later
    private const FLASH_OLD_KEY = 'categories.old';
    private const FLASH_ERRORS_KEY = 'categories.errors';

    //model and service needed for htis controller
    public function __construct(
        Container $container,
        private CategoriesModel $categoriesModel,
        private CategoryService $categoryService
    ) {
        parent::__construct($container);
    }


    //shows the main list of categories in the admin panel
    public function index(Request $request, Response $response, array $args): Response
    {
        $categories = $this->categoriesModel->getAllCategories();

        $data = [
            'page_title' => 'Categories',
            'categories' => $categories,
        ];

        return $this->render($response, 'admin/categories/categoryIndexView.php', $data);
    }

    // displays the form to create a new category
    // also pulls old input/errors if they messed up the last submission
    public function create(Request $request, Response $response, array $args): Response
    {
        $data = [
            'page_title' => 'Create Category',
            'old' => $this->pullOldInput(),
            'errors' => $this->pullErrors(),
        ];

        return $this->render($response, 'admin/categories/categoryCreateView.php', $data);
    }


    // handles the actual form submission to save the new category
    public function store(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getParsedBody() ?? [];
        //validate the input usinf the service
        $result = $this->categoryService->validateCategory($payload);

        if (!$result['success']) {
            SessionManager::set(self::FLASH_OLD_KEY, $result['data']);
            SessionManager::set(self::FLASH_ERRORS_KEY, $result['errors']);
            FlashMessage::error('Please fix the validation errors and try again.');

            return $this->redirect($request, $response, 'categories.create');
        }

        $this->categoriesModel->create($result['data']);
        FlashMessage::success('Category created successfully.');

        return $this->redirect($request, $response, 'categories.index');
    }




// shows the edit form. needs to find the category first.
    public function edit(Request $request, Response $response, array $args): Response
    {
        $categoryId = isset($args['id']) ? (int) $args['id'] : 0;
        if ($categoryId <= 0) {
            FlashMessage::error('Invalid category selected.');
            return $this->redirect($request, $response, 'categories.index');
        }

        // chekcing if it actually exists in the database
        $category = $this->categoriesModel->findById($categoryId);
        if ($category === false) {
            FlashMessage::error('Category not found.');
            return $this->redirect($request, $response, 'categories.index');
        }

        $data = [
            'page_title' => 'Edit Category',
            'category' => $category,
            'old' => $this->pullOldInput(),
            'errors' => $this->pullErrors(),
        ];

        return $this->render($response, 'admin/categories/categoryEditView.php', $data);
    }


    // handles the update logic. basically same as store but with an ID.
    public function update(Request $request, Response $response, array $args): Response
    {
        $categoryId = isset($args['id']) ? (int) $args['id'] : 0;
        if ($categoryId <= 0) {
            FlashMessage::error('Invalid category selected.');
            return $this->redirect($request, $response, 'categories.index');
        }

        $payload = $request->getParsedBody() ?? [];

        // new data valid or not
        $result = $this->categoryService->validateCategory($payload);

        if (!$result['success']) {
            SessionManager::set(self::FLASH_OLD_KEY, $result['data']);
            SessionManager::set(self::FLASH_ERRORS_KEY, $result['errors']);
            FlashMessage::error('Please fix the validation errors and try again.');

            return $this->redirect($request, $response, 'categories.edit', ['id' => $categoryId]);
        }

        $category = $this->categoriesModel->findById($categoryId);
        if ($category === false) {
            FlashMessage::error('Category not found.');
            return $this->redirect($request, $response, 'categories.index');
        }

        $this->categoriesModel->update($categoryId, $result['data']);
        FlashMessage::success('Category updated successfully.');

        return $this->redirect($request, $response, 'categories.index');
    }


    // deletes the category from the database
    public function delete(Request $request, Response $response, array $args): Response
    {
        $categoryId = isset($args['id']) ? (int) $args['id'] : 0;
        if ($categoryId <= 0) {
            FlashMessage::error('Invalid category selected.');
            return $this->redirect($request, $response, 'categories.index');
        }

        $category = $this->categoriesModel->findById($categoryId);
        if ($category === false) {
            FlashMessage::error('Category not found.');
            return $this->redirect($request, $response, 'categories.index');
        }

        $this->categoriesModel->delete($categoryId);
        FlashMessage::success('Category deleted successfully.');

        return $this->redirect($request, $response, 'categories.index');
    }


    // helper to get the old form input from session and clear it
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
}
