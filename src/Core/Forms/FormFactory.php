<?php

namespace GameX\Core\Forms;

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
     * @param Session $session
     */
    public function __construct(Session $session) {
        $this->session = $session;
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
