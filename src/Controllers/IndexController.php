<?php
namespace GameX\Controllers;

use GameX\Core\BaseController;

class IndexController extends BaseController {
    public function indexAction(array $args) {
        return $this->render('index.twig');
    }
}
