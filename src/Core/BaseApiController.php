<?php
namespace GameX\Core;

use \Slim\Http\Request;

abstract class BaseApiController extends BaseController {

    /**
     * @param Request $request
     * @return array
     */
    public function getBody(Request $request) {
        $body = $request->getParsedBody();
        return $body !== null ? $body : [];
    }
}
