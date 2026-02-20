<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Domain\Models\TrustedDeviceModel;
use App\Domain\Models\TwoFactorAuthModel;
use App\Helpers\SessionManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseFactoryInterface;
//use Slim\Psr7\Response as SlimResponse;
use DI\Container;
use Slim\Routing\RouteContext;


/**
 * Middleware to check if user needs to verify 2FA.
 *
 * This middleware runs AFTER AuthMiddleware. It checks:
 * 1. Is the user authenticated?
 * 2. Does the user have 2FA enabled?
 * 3. Has the user already verified 2FA in this session?
 *
 * If 2FA is required but not verified, redirect to /2fa/verify
 */
class TwoFactorMiddleware implements MiddlewareInterface
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $routeName = $route?->getName();

        // 1. Check if user is logged in
        if (!SessionManager::has('user_id')) {
            return $handler->handle($request);
        }

        $userId = SessionManager::get('user_id');

        // TODO: Check if user has 2FA enabled
        // HINT: Use $this->twoFactorModel->isEnabled($userId)
        $twoFactorModel = $this->container->get(TwoFactorAuthModel::class);
        if (!$twoFactorModel->isEnabled($userId)) {
            return $handler->handle($request);
        }

        // 3. Check if already verified in this session
         // TODO: Check if 2FA has already been verified in this session
        // HINT: Check SessionManager::get('two_factor_verified')
        if (SessionManager::get('two_factor_verified') === true) {
            return $handler->handle($request);
        }

        // 4. Check for trusted device cookie (Disabled for now)
        /*
        if (isset($_COOKIE['trusted_device'])) {
            $token = $_COOKIE['trusted_device'];
            $trustedDeviceModel = $this->container->get(TrustedDeviceModel::class);

            if ($trustedDeviceModel->verify($userId, $token)) {
                // Valid trusted device - mark as verified
                SessionManager::set('two_factor_verified', true);
                return $handler->handle($request);
            }
        }
        */

        $allowedRoutes = [
            '2fa.setup',
            '2fa.enable',
            '2fa.verify',
            '2fa.verify.post',
            '2fa.disable',
            '2fa.disable.post',
            'auth.logout',
        ];

        if ($routeName && in_array($routeName, $allowedRoutes, true)) {
            return $handler->handle($request);
        }

        // Recording intended route for post-verification redirect
        if ($routeName) {
            SessionManager::set('post_2fa_route', $routeName);
        }

        // 6. Redirect to 2FA verification page
         // 2FA required but not verified - redirect to verification page
        $routeParser = $routeContext->getRouteParser();
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);

        $response = $responseFactory->createResponse(302);
        return $response->withHeader('Location', $routeParser->urlFor('2fa.verify'));
    }
}
