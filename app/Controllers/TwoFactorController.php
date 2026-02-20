<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Models\TwoFactorAuthModel;
use App\Domain\Models\UserModel;
use App\Domain\Models\TrustedDeviceModel;
use App\Helpers\FlashMessage;
use App\Helpers\SessionManager;
use RobThree\Auth\TwoFactorAuth as TFA;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

/**
 * Controller for Two-Factor Authentication operations.
 */
class TwoFactorController extends BaseController
{
    /**
     * Display the 2FA setup page with QR code.
     */
    public function showSetup(Request $request, Response $response): Response
    {
        $userId = SessionManager::get('user_id');
        $userEmail = SessionManager::get('user_email');

        $twoFactorModel = $this->container->get(TwoFactorAuthModel::class);

        // Check if user already has 2FA enabled
        if ($twoFactorModel->isEnabled($userId)) {
            FlashMessage::error('2FA is already enabled.');
            $targetRoute = SessionManager::get('user_role') === 'admin' ? 'admin.dashboard' : 'user.dashboard';
            return $this->redirect($request, $response, $targetRoute);
        }

         // TODO: Create a QR code provider instance
        // HINT: Use BaconQrCodeProvider with parameters: (4, '#ffffff', '#000000', 'svg')
        // The parameters are: size, background color, foreground color, image format
        // Correct provider + TFA constructor usage
        $qrProvider = new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg');

        // TODO: Create a TFA (TwoFactorAuth) instance
        // HINT: Pass the QR provider and your app name (e.g., 'YourAppName') to the constructor

        $tfa = new TFA($qrProvider, 'TeachNest');


         // TODO: Generate a new TOTP secret
        // HINT: Use the TFA instance's createSecret() method
        $secret = $tfa->createSecret(); // Replace with your implementation


          // Store secret in session temporarily (not in database yet)
        SessionManager::set('2fa_setup_secret', $secret);


         // TODO: Generate QR code as a data URI for display in an <img> tag
        // HINT: Use $tfa->getQRCodeImageAsDataUri($userEmail, $secret)
        // This returns a string like "data:image/svg+xml;base64,..." ready for img src
        $qrCodeDataUri = $tfa->getQRCodeImageAsDataUri($userEmail, $secret);// Replace with your implementation


        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $cancelRoute = SessionManager::get('user_role') === 'admin' ? 'admin.dashboard' : 'user.dashboard';

        return $this->render($response, 'auth/2fa-setup.php', [
            'title' => 'Enable 2FA',
            'qrCodeDataUri' => $qrCodeDataUri,
            'secret' => $secret,
            'setupAction' => $routeParser->urlFor('2fa.enable'),
            'cancelUrl' => $routeParser->urlFor($cancelRoute),
        ]);
    }


     /**
     * Verify the code and enable 2FA.
     */
    public function verifyAndEnable(Request $request, Response $response): Response
    {
        $userId = SessionManager::get('user_id');
        $userEmail = SessionManager::get('user_email');

        $data = $request->getParsedBody() ?? [];
        $code = $data['code'] ?? '';

         // Get secret from session
        $secret = SessionManager::get('2fa_setup_secret');

        if (!$secret) {
            FlashMessage::error('Setup session expired. Please try again.');
            return $this->redirect($request, $response, '2fa.setup');
        }

         // TODO: Create a QR code provider and TFA instance (same as showSetup)
        // HINT: Use BaconQrCodeProvider and TFA classes
        $qrProvider = new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg');

  // TODO: Verify the user's code against the secret
        // HINT: Use $tfa->verifyCode($secret, $code) - returns true if valid

        $tfa = new TFA($qrProvider, 'TeachNest');

        $valid = $tfa->verifyCode($secret, $code);// Replace with your implementation

        if (!$valid) {

             // TODO: Regenerate QR code for retry (user entered wrong code)
            // HINT: Use $tfa->getQRCodeImageAsDataUri($userEmail, $secret)
            $qrCodeDataUri = $tfa->getQRCodeImageAsDataUri($userEmail, $secret);

            return $this->render($response, 'auth/2fa-setup.php', [
                'title' => 'Enable 2FA',
                'error' => 'Invalid verification code. Please try again.',
                'qrCodeDataUri' => $qrCodeDataUri,
                'secret' => $secret
            ]);
        }

         // TODO: Save secret to database and enable 2FA
        // Step 1: Get the TwoFactorAuth model from the container
        // Step 2: Create a new 2FA record: $twoFactorModel->create($userId, $secret)
        // Step 3: Enable 2FA for the user: $twoFactorModel->enable($userId)

        // Clear the setup secret from session

        $twoFactorModel = $this->container->get(TwoFactorAuthModel::class);
        $twoFactorModel->create($userId, $secret);
        $twoFactorModel->enable($userId);

        SessionManager::remove('2fa_setup_secret');

        FlashMessage::success('2FA has been enabled successfully!');
        // Redirect based on role
        $role = SessionManager::get('user_role');
        if ($role === 'admin') {
            return $this->redirect($request, $response, 'admin.dashboard');
        }
        return $this->redirect($request, $response, 'user.dashboard');
    }


    /**
     * Show the 2FA verification page (during login).
     */
    public function showVerify(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->render($response, 'auth/2fa-verify.php', [
            'title' => 'Verify 2FA',
            'verifyAction' => $routeParser->urlFor('2fa.verify.post'),
            'logoutUrl' => $routeParser->urlFor('auth.logout'),
        ]);
    }


     /**
     * Verify 2FA code during login.
     */

    public function verify(Request $request, Response $response): Response
    {
        $userId = SessionManager::get('user_id');
        $data = $request->getParsedBody() ?? [];
        $code = $data['code'] ?? '';

         // TODO: Get the user's TOTP secret from the database
        // Step 1: Get the TwoFactorAuth model from the container
        // Step 2: Use getSecret($userId) to retrieve the secret
        $trustDevice = isset($data['trust_device']);

        $twoFactorModel = $this->container->get(TwoFactorAuthModel::class);
        $secret = $twoFactorModel->getSecret($userId);

        if (!$secret) {
            FlashMessage::error('2FA not enabled for this account.');
            return $this->redirect($request, $response, 'auth.login');
        }
 // TODO: Create a QR code provider and TFA instance

        $qrProvider = new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg');
        $tfa = new TFA($qrProvider, 'TeachNest');


 // TODO: Verify the user's code against their stored secret
        // HINT: Use $tfa->verifyCode($secret, $code)
        $valid = $tfa->verifyCode($secret, $code);

        if (!$valid) {
            $attempts = (SessionManager::get('2fa_attempts') ?? 0) + 1;
            SessionManager::set('2fa_attempts', $attempts);

            if ($attempts >= 5) {
                SessionManager::destroy();
                return $this->redirect($request, $response, 'auth.login');
            }

            return $this->render($response, 'auth/2fa-verify.php', [
                'title' => 'Verify 2FA',
                'error' => 'Invalid code. Please try again.'
            ]);
        }

        SessionManager::set('two_factor_verified', true);
        SessionManager::remove('2fa_attempts');

        $targetRoute = SessionManager::get('post_2fa_route');
        SessionManager::remove('post_2fa_route');

        /* trusted device logic not working for now date: 12/15
        if ($trustDevice) {
            $token = bin2hex(random_bytes(32));
            $deviceInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            $trustedDeviceModel = $this->container->get(TrustedDeviceModel::class);
            $trustedDeviceModel->create($userId, $token, $deviceInfo, $ipAddress);

            setcookie('trusted_device', $token, [
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        */

        if ($trustDevice) {
            // TODO: Generate a unique device token (64-character hex string)
    // HINT: Use bin2hex(random_bytes(32)) to generate a secure random token
            $token = bin2hex(random_bytes(32));
            $deviceInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';


             // TODO: Save the trusted device to the database
    // HINT: Call $this->trustedDeviceModel->create($userId, $deviceToken, $deviceInfo
            $trustedDeviceModel = $this->container->get(TrustedDeviceModel::class);
            $trustedDeviceModel->create($userId, $token, $deviceInfo, $ipAddress);


            // TODO: Set a secure cookie to remember this device
    // HINT: Use setcookie() with an options array containing:
    // - 'expires': strtotime('+30 days')
    // - 'path': '/' . APP_ROOT_DIR_NAME
    // - 'secure': false (set to true in production with HTTPS)
    // - 'httponly': true (prevents JavaScript access)
    // - 'samesite': 'Lax' (CSRF protection)
            setcookie('trusted_device', $token, [
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        // Redirect based on role
        $role = SessionManager::get('user_role');
        $fallbackRoute = $role === 'admin' ? 'admin.dashboard' : 'user.dashboard';
        return $this->redirect($request, $response, $targetRoute ?: $fallbackRoute);
    }

     /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request, Response $response): Response
    {
        $userId = SessionManager::get('user_id');
        $userEmail = SessionManager::get('user_email');

        $data = $request->getParsedBody() ?? [];
        $password = $data['password'] ?? '';

        // Verify password before disabling 2FA
        $userModel = $this->container->get(UserModel::class);
        $validUser = $userModel->verifyPassword($userEmail, $password);

        if (!$validUser) {
            return $this->render($response, 'auth/2fa-disable.php', [
                'title' => 'Disable 2FA',
                'error' => 'Invalid password.'
            ]);
        }

 // TODO: Disable 2FA in the database
        // HINT: Call $this->twoFactorModel->disable($userId)
        $twoFactorModel = $this->container->get(TwoFactorAuthModel::class);
        $twoFactorModel->disable($userId);

        FlashMessage::success('2FA has been disabled.');
        FlashMessage::success('2FA has been disabled.');
        // Redirect based on role
        $role = SessionManager::get('user_role');
        if ($role === 'admin') {
            return $this->redirect($request, $response, 'admin.dashboard');
        }
        return $this->redirect($request, $response, 'user.dashboard');
    }

    /**
     * Show disable confirmation page.
     */
    public function showDisable(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $cancelRoute = SessionManager::get('user_role') === 'admin' ? 'admin.dashboard' : 'user.dashboard';

        return $this->render($response, 'auth/2fa-disable.php', [
            'title' => 'Disable 2FA',
            'disableAction' => $routeParser->urlFor('2fa.disable.post'),
            'cancelUrl' => $routeParser->urlFor($cancelRoute),
        ]);
    }
}
