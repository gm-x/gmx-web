<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController {
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {

    	/** @var Menu $menu */
    	$menu = $this->getContainer('menu');

		$menu->add(new MenuItem('Index', 'index'));
		$menu->add(new MenuItem('Admin', 'admin_index', [], 'admin.*'));
		$menu->add(new MenuItem('Local server', 'admin_servers_groups_list', ['server' => 1], 'admin.*'));

        return $this->render('index/index.twig', [
			'menu' => $menu
		]);
    }
}
