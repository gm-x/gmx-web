<?php

namespace GameX\Core\Forms;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\RequestInterface;
use \GameX\Core\Session\Session;

class FormFactory {
    /**
     * @var RequestInterface
     */
    protected $request;

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
        $this->request = $container->get('request');
        $this->session = $container->get('session');
    }

    /**
     * @param $name
     * @return Form
     */
    public function createForm($name) {
        if (!isset($this->forms[$name])) {
            $this->forms[$name] = new Form($this->request, $this->session, $name);
        }

        return $this->forms[$name];
    }
}
