<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;

class PlayersController extends BaseApiController {
    public function indexAction(Request $request, Response $response, array $args) {
        return $response->withJson([
			'success' => true,
        ]);
    }
}
