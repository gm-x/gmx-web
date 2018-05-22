<?php

namespace GameX\Core\Forms;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\RequestInterface;
use \GameX\Core\Session\Session;

class FormFactory {
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Form[]
     */
    protected $forms = [];

    /**
     * FormFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->session = $container->get('session');
    }

    /**
     * @param $name
     * @return Form
     */
    public function createForm($name) {
        if (!isset($this->forms[$name])) {
            $this->forms[$name] = new Form($this->session, $name);
        }

        return $this->forms[$name];
    }
}
