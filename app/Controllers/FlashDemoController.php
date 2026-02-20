<?php

namespace App\Controllers;

use App\Helpers\FlashMessage;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FlashDemoController extends BaseController
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }
    // TODO: Implement the methods below
    public function index(Request $request, Response $response, array $args): Response
    {

        $data = [
            'title' => 'Flash Messages Demo'
        ];

        return $this->render($response, 'flash-demo/index.php', $data);
    }

    public function success(Request $request, Response $response, array $args): Response
    {
        FlashMessage::success('This is a success message!');
        return $this->redirect($request, $response, 'flash.demo');
    }

    public function error(Request $request, Response $response, array $args): Response
    {
        FlashMessage::error('This is an error message.');
        return $this->redirect($request, $response, 'flash.demo');
    }

    public function info(Request $request, Response $response, array $args): Response
    {
        FlashMessage::info('This is an informational message.');
        return $this->redirect($request, $response, 'flash.demo');
    }

    public function warning(Request $request, Response $response, array $args): Response
    {
        FlashMessage::warning('This is a warning message.');
        return $this->redirect($request, $response, 'flash.demo');
    }

    public function multiple(Request $request, Response $response, array $args): Response
    {
        FlashMessage::success('Operation completed successfully.');
        FlashMessage::info('Here is some additional information.');
        FlashMessage::warning('Remember to double-check your data.');
        FlashMessage::error('An error also occurred.');

        return $this->redirect($request, $response, 'flash.demo');
    }
}
