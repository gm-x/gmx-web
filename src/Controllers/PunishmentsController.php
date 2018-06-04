<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;


class PunishmentsController extends BaseController {
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
        return $this->render('punishments/index.twig');
    }
}
