<?php

namespace App\Library;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class QueryStringMiddleWare implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $queryString = $request->getServerParams()['QUERY_STRING'];
        $queryParams = $this->parseQueryString($queryString);
        $request = $request->withQueryParams($queryParams);

        return $handler->handle($request);
    }

    /**
     * Parse query string and duplicated keys as array
     * @param $str
     * @return array
     */
    private function parseQueryString($str): array
    {
        // result array
        $output = [];

        // split on outer delimiter
        $pairs = explode('&', $str);

        // loop through each pair
        foreach ($pairs as $i) {
            // split into name and value
            list($name, $value) = explode('=', $i, 2);

            // if name ends with []
            if ($this->endsWith($name, '[]')) {
                # format name
                $name = substr($name, 0, -2);
                # stick multiple values into an array
                if (!is_array($output[$name])) {
                    $output[$name] = [];
                }
                $output[$name][] = $value;
            }
            // if name already exists
            elseif (isset($output[$name])) {
                # stick multiple values into an array
                if (is_array($output[$name])) {
                    $output[$name][] = $value;
                } else {
                    $output[$name] = [$output[$name], $value];
                }
            }
            // otherwise, simply stick it in a scalar
            else {
                $output[$name] = $value;
            }
        }

        return $output;
    }

    /**
     * Denotes if a string ends with another string
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}