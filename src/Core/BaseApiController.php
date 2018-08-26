<?php
namespace GameX\Core;

use \Slim\Http\Request;
use \Gamex\Models\Server;

abstract class BaseApiController extends BaseController {

    /**
     * @param Request $request
     * @return array
     */
    public function getBody(Request $request) {
        $body = $request->getParsedBody();
        return $body !== null ? $body : [];
    }

    /**
     * @param Request $request
     * @return Server
     */
    protected function getServer(Request $request) {
        return $request->getAttribute('server');
    }
}
