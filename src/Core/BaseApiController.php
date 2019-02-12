<?php

namespace GameX\Core;

use \Slim\Http\Request;
use \Slim\Http\Response;
use \Gamex\Models\Server;

abstract class BaseApiController extends BaseController
{
    
    /**
     * @param Request $request
     * @return array
     */
    public function getBody(Request $request)
    {
        $body = $request->getParsedBody();
        return $body !== null ? $body : [];
    }
    
    /**
     * @param Request $request
     * @return Server
     */
    protected function getServer(Request $request)
    {
        return $request->getAttribute('server');
    }
    
    /**
     * @param Response $response
     * @param int $status
     * @param mixed $data
     * @return Response
     */
    protected function response(Response $response, $status, $data)
    {
        return $response->withStatus($status)->withJson($data);
    }
}
