<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Models\Server;
use \GameX\Models\Player;

class AdminController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'admin_index';
	}

	public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args) {
//		TODO: For active menu checks
//		$request->getAttribute('route')->getName();
		return $this->render('admin/index.twig', [
            'servers' => [
                'total' => Server::count(),
                'active' => Server::where('active', 1)->count(),
            ],
		    'users' => [
		        'total' => UserModel::count(),
                'new' => UserModel::whereRaw('DATE(`created_at`) = CURDATE()')->count()
            ],
		    'players' => [
		        'total' => Player::count(),
                'new' => Player::whereRaw('DATE(`created_at`) = CURDATE()')->count()
            ],
        ]);
	}
}
