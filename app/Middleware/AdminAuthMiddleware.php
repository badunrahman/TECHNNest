<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\FlashMessage;
use App\Helpers\SessionManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

use App\Domain\Models\TwoFactorAuthModel;

class AdminAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TwoFactorAuthModel $twoFactorModel
    ) {
    }
    /**
     * Process the request - check if user is authenticated AND is an admin.
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $isAuthenticated = SessionManager::get('is_authenticated');
        $userRole        = SessionManager::get('user_role');

        $routeParser  = RouteContext::fromRequest($request)->getRouteParser();
        $psr17Factory = new Psr17Factory();

        // Not authenticated
        if (!$isAuthenticated) {
            FlashMessage::error("Please log in to access the admin panel.");

            $loginUrl = $routeParser->urlFor('auth.login');
            $response = $psr17Factory->createResponse(302);

            return $response->withHeader('Location', $loginUrl)
                            ->withStatus(302);
        }

        // Authenticated but not admin
        if ($userRole !== 'admin') {
            FlashMessage::error("Access denied. Admin privileges required.");

            $userDashboardUrl = $routeParser->urlFor('user.dashboard');
            $response         = $psr17Factory->createResponse(302);

            return $response->withHeader('Location', $userDashboardUrl)
                            ->withStatus(302);
        }

        // Authenticated AND admin

        // Enforce 2FA for admins
        $userId = SessionManager::get('user_id');
        if (!$this->twoFactorModel->isEnabled($userId)) {
            FlashMessage::warning('Admin accounts require Two-Factor Authentication. Please enable 2FA to continue.');

            $setupUrl = $routeParser->urlFor('2fa.setup');
            $response = $psr17Factory->createResponse(302);
            return $response->withHeader('Location', $setupUrl);
        }

        return $handler->handle($request);
    }
}
