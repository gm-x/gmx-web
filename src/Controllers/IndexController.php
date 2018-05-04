<?php
namespace GameX\Controllers;

//use Cartalyst\Sentinel\Sentinel;
use GameX\Core\BaseController;

class IndexController extends BaseController {
    public function indexAction(array $args) {
        return $this->render('index.twig');
    }

    public function registerAction(array $args) {
//        /** @var Sentinel $auth */
//        $auth = $this->getContainer()->get('auth');
    }
}
