<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController {
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
//    	/** @var \Stash\Pool $item$cache */
//		$cache = $this->getContainer('cache');
//		$cache->setLogger($this->getContainer('log'));
//		$item = $cache->getItem('test');
//		$data = $item->get();
//		if ($item->isMiss()) {
//			$item->lock();
//			$data = date('d.m.Y H:i:s');
//			$item->set($data);
//			$item->expiresAfter(60);
//			$cache->save($item);
//		}
        return $this->render('index/index.twig');
    }
}
