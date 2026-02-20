<?php

namespace App\Controllers;

use App\Domain\Models\TwoFactorAuthModel;
    use App\Domain\Models\UserModel;
    use App\Helpers\FlashMessage;
    use App\Helpers\SessionManager;
    use DI\Container;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Routing\RouteContext;

    class AuthController extends BaseController
    {
        public function __construct(
            Container $container,
            private UserModel $userModel,
            private TwoFactorAuthModel $twoFactorModel
        ) {
            parent::__construct($container);
        }


    /**
     * Display the registration form (GET request).
     */
    public function register(Request $request, Response $response, array $args): Response
    {
         // TODO: Create a $data array with 'title' => 'Register'
        $data = [
            'title' => 'Register'
        ];
         // TODO: Render 'auth/register.php' view and pass $data
        return $this->render($response, 'auth/register.php', $data);
    }


    /**
     * Process registration form submission (POST request).
     */
    public function store(Request $request, Response $response, array $args): Response
    {
          // TODO: Get form data using getParsedBody()
        //       Store in $formData variable
        $formData = $request->getParsedBody();

         // TODO: Extract individual fields from $formData:
        //       $firstName, $lastName, $username, $email, $password, $confirmPassword, $role
        $firstName = trim($formData['first_name'] ?? '');
        $lastName = trim($formData['last_name'] ?? '');
        $username = trim($formData['username'] ?? '');
        $email = trim($formData['email'] ?? '');
        $password = trim($formData['password'] ?? '');
        $confirmPassword = trim($formData['confirm_password'] ?? '');
        $role = 'customer';


        // Start validation
        $errors = [];

// TODO: Validate required fields (first_name, last_name, username, email, password, confirm_password)
        //       If any field is empty, add error: "All fields are required."
        //       Hint: if (empty($firstName) || empty($lastName) || ...) { $errors[] = "..."; }

        if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $errors[] = "All fields are required.";
        }

         // TODO: Validate email format using filter_var()
        //       If invalid, add error: "Invalid email format."
        //


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

            // TODO: Check if email already exists using $this->userModel->emailExists($email)
        //       If exists, add error: "Email already registered."
        if ($this->userModel->emailExists($email)) {
            $errors[] = "Email already registered.";
        }

         // TODO: Check if username already exists using $this->userModel->usernameExists($username)
        //       If exists, add error: "Username already taken."
        if ($this->userModel->usernameExists($username)) {
            $errors[] = "Username already taken.";
        }

        // TODO: Validate password length (minimum 8 characters)
        //       If too short, add error: "Password must be at least 8 characters long."
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

         // TODO: Validate password contains at least one number
        //       If no number, add error: "Password must contain at least one number."
        //       Hint: if (!preg_match('/[0-9]/', $password)) { ... }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        };

         // TODO: Check if password matches confirm_password
        //       If not match, add error: "Passwords do not match."
        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match.";
        }


        // If validation errors exist, redirect back with error message
        // TODO: Check if $errors array is not empty
        //       If errors exist:
        //         - Use FlashMessage::error() with the first error message
        //         - Redirect back to 'auth.register' route
        if (!empty($errors)) {
            FlashMessage::error($errors[0]);
            return $this->redirect($request, $response, 'auth.register');
        }


        // If validation passes, create the user
        try {
            $userData = [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'username'   => $username,
                'email'      => $email,
                'password'   => $password,
                'role'       => $role,
            ];

            $userId = $this->userModel->createUser($userData);

            FlashMessage::success("Registration successful! Please log in.");
            return $this->redirect($request, $response, 'auth.login');
        } catch (\Exception $e) {
            FlashMessage::error("Registration failed. Please try again.");
            return $this->redirect($request, $response, 'auth.register');
        }
    }



      /**
     * Displaying the login form GET request.
     */
    public function login(Request $request, Response $response, array $args): Response
    {
        $data = ['title' => 'Login'];
        return $this->render($response, 'auth/login.php', $data);
    }


    

 /**
     * Processing login form submission POST request.
     */
    public function authenticate(Request $request, Response $response, array $args): Response
    {
        //getting the fomr data
        $formData   = $request->getParsedBody() ?? [];
        $identifier = trim($formData['identifier'] ?? '');
        $password   = trim($formData['password'] ?? '');

        $errors = [];

        // Validatng required fields
        if (empty($identifier) || empty($password)) {
            $errors[] = "Email/username and password are required.";
        }

        // if any  error, redirect back
        if (!empty($errors)) {
            FlashMessage::error($errors[0]);
            return $this->redirect($request, $response, 'auth.login');
        }

        //  verify user credentials
        $user = $this->userModel->verifyCredentials($identifier, $password);

        // if authentication failed
        if ($user === null) {
            FlashMessage::error("Invalid credentials. Please try again.");
            return $this->redirect($request, $response, 'auth.login');
        }

        // if authentication is successful
        SessionManager::set('user_id', $user['id']);
        SessionManager::set('user_email', $user['email']);
        SessionManager::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
        SessionManager::set('user_role', $user['role']);
        SessionManager::set('is_authenticated', true);

        // checking if the user has 2fa enabled
        $has2FA = $this->twoFactorModel->isEnabled($user['id']);
        SessionManager::set('requires_2fa', $has2FA);

        $post2faRoute = $user['role'] === 'admin' ? 'admin.dashboard' : 'user.dashboard';
        SessionManager::set('post_2fa_route', $post2faRoute);

        if ($has2FA) {
            SessionManager::set('two_factor_verified', false);
            FlashMessage::success("Welcome back, {$user['first_name']}! Please verify your 2FA code.");
            return $this->redirect($request, $response, '2fa.verify');
        }

        SessionManager::set('two_factor_verified', true);

        FlashMessage::success("Welcome back, {$user['first_name']}!");

        return $this->redirect($request, $response, $post2faRoute);
    }

    /**
     * logout current user GET request.
     */
    public function logout(Request $request, Response $response, array $args): Response
    {
        SessionManager::destroy();
        FlashMessage::success("You have been logged out successfully.");
        return $this->redirect($request, $response, 'auth.login');
    }

    /**
     * displaying user dashboard
     */
   public function dashboard(Request $request, Response $response, array $args): Response
    {
        $userId = SessionManager::get('user_id');
        $has2FA = $this->twoFactorModel->isEnabled($userId);

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $data = [
            'title' => 'Dashboard',
            'has2FA' => $has2FA,
            'enable2faUrl' => $routeParser->urlFor('2fa.setup'),
            'disable2faUrl' => $routeParser->urlFor('2fa.disable.post'),
            'productsUrl' => $routeParser->urlFor('products.list'),
            'ordersUrl'   => $routeParser->urlFor('my-orders.index'),
            // 'profileUrl' => $routeParser->urlFor('user.profile'), // Keeping this for now i dont have the profile section set up yet

        ];
        return $this->render($response, 'user/dashboard.php', $data);
    }
}


