<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Constants\Admin\PunishmentsConstants;
use \Slim\Exception\NotFoundException;

class PunishmentsController extends BaseAdminController {

    /**
     * @return string
     */
    protected function getActiveMenu() {
        return PunishmentsConstants::ROUTE_LIST;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        return $this->render('admin/players/punishments/index.twig', []);
    }
}
