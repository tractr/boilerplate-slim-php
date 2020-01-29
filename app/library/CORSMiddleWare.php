<?php

namespace App\Library;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CORSMiddleWare implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        return $handler->handle($request)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', $request->getHeader('Origin'))
            ->withHeader('Access-Control-Expose-Headers', 'WWW-Authenticate,Server-Authorization');
    }
}
