<?php
namespace GameX\Controllers;

use \GameX\Core\BaseController;
//use Cartalyst\Sentinel\Sentinel;
use \GameX\Core\Forms\FormHelper;

class IndexController extends BaseController {
    public function indexAction(array $args) {
        return $this->render('index.twig');
    }

    public function registerAction(array $args) {
        $form = new FormHelper('register');
        $form
            ->addField('email', '', [
                'type' => 'email',
                'title' => 'Email',
                'description' => 'Must be valid email',
                'required' => true,
                'attributes' => [],
            ], ['required', 'email'])
            ->addField('password', '', [
                'type' => 'password',
                'title' => 'Password',
                'description' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 6])
            ->addField('password_repeat', '', [
                'type' => 'password',
                'title' => 'Repeat Password',
                'description' => 'Passwords doesn\'t match',
                'required' => true,
                'attributes' => [],
            ], ['required', 'trim', 'min_length' => 6]);
        $form->processRequest($this->getRequest());

//        /** @var Sentinel $auth */
//        $auth = $this->getContainer()->get('auth');

        return $this->render('index/register.twig', [
            'form' => $form,
        ]);
    }
}
