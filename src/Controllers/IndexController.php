<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController {
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
    	/** @var \o80\i18n\I18N $lang */
    	$lang = $this->getContainer('lang');
        return $this->render('index/index.twig', [
        	'title' => $lang->get('Generic', 'title')
		]);
    }
}
