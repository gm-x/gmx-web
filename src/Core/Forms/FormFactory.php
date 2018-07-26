<?php

namespace GameX\Core\Forms;

use \GameX\Core\Session\Session;
use \GameX\Core\Lang\Language;

class FormFactory {
    
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var Language
     */
    protected $language;

    /**
     * @var Form[]
     */
    protected $forms = [];

    /**
     * FormFactory constructor.
     * @param Session $session
     * @param Language $language
     */
    public function __construct(Session $session, Language $language) {
        $this->session = $session;
        $this->language = $language;
    }

    /**
     * @param $name
     * @return Form
     */
    public function createForm($name) {
        if (!isset($this->forms[$name])) {
            $this->forms[$name] = new Form($this->session, $this->language, $name);
        }

        return $this->forms[$name];
    }
}
