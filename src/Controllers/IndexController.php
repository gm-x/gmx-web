<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
use GameX\Core\FlashMessages;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController {
    public function indexAction(RequestInterface $request, ResponseInterface $response, array $args) {
//        return $this->render('index/index.twig');
        /** @var FlashMessages $flash */
        $flash = $this->getContainer('flash');
        $messages = $flash->getMessages();
        var_dump($messages);
        if ($request->isPost()) {
            $this->addFlashMessage('error', 'test error');
            return $this->redirect('index');
        }
        return $this->render('index/index.twig');
    }
}
