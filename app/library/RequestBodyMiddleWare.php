<?php

namespace App\Library;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RequestBodyMiddleWare implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strstr($contentType, 'application/json')) {
            $contents = json_decode($request->getBody()->getContents(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            }
        }

        if (strstr($contentType, 'multipart/form-data')) {
            $contents = $this->parse_multipart_form_data($request->getBody()->getContents());
            $request = $request->withParsedBody($contents);
        }

        if (strstr($contentType, 'application/x-www-form-urlencoded')) {
            $contents = $this->parse_multipart_form_data_url_encoded($request->getBody()->getContents());
            $request = $request->withParsedBody($contents);
        }

        return $handler->handle($request);
    }

    private function parse_multipart_form_data($input)
    {
        $a_data = array();
        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block) {
            if (empty($block))
                continue;

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visible char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE) {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            } // parse all other fields
            else {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }

        return $a_data;
    }

    private function parse_multipart_form_data_url_encoded($input)
    {
        $result = array();

        foreach (explode('&', $input) as $chunk) {
            $param = explode("=", $chunk);
            if ($param) {
                $result[$param[0]] = $param[1];
            }
        }

        return $result;
    }
}
