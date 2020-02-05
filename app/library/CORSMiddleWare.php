<?php

namespace App\Library;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class CORSMiddleWare
 * Adds CORS header to response
 * @package App\Library
 */
class CORSMiddleWare implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', $request->getHeaderLine('Origin'))
            ->withHeader('Access-Control-Expose-Headers', 'WWW-Authenticate,Server-Authorization');

        if ($request->getMethod() === 'OPTIONS') {
            $response = $response
                ->withHeader('Access-Control-Allow-Headers', 'Accept,Authorization,Content-Type,If-None-Match')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->withHeader('Access-Control-Max-Age', '86400');
        }

        return $response;
    }
}
