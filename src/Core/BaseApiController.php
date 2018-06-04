<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;

abstract class BaseApiController extends BaseController {

	/**
	 * BaseController constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
	}
}
