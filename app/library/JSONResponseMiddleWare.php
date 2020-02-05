<?php

namespace App\Library;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class JSONResponseMiddleWare
 * Adds JSON content-type header to response
 * @package App\Library
 */
class JSONResponseMiddleWare implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        // Add header only if not defined
        if (empty($response->getHeaderLine('Content-Type'))) {
            return $response->withHeader('Content-Type', 'application/json');
        }

        return $response;
    }
}
