<?php

namespace GameX\Core\Forms;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\UriInterface;
use \GameX\Core\Helpers\UriHelper;
use \Form\Validator;
use \GameX\Core\Session\Session;
use \ArrayAccess;
use \Exception;

class Form implements ArrayAccess {
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isSubmitted = false;

    /**
     * @var bool
     */
    protected $isValid = true;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var string
     */
    protected $action;

    /**
     * @var FormElement[]
     */
    protected $elements = [];

    /**
     * Form constructor.
     * @param Session $session
     * @param $name
     */
    public function __construct(Session $session, $name) {
        $this->session = $session;
        $this->name = $name;
        $this->validator = new Validator([], ['stop_on_error' => false]);

        $this->loadValues();
    }

    /**
     * From destructor
     */
    public function __destruct() {
        if (!$this->isValid) {
            $this->writeValues();
        }
    }

    /**
     * @param string $action
     * @return $this
     */
    public function setAction($action) {
        if ($action instanceof UriInterface) {
            $this->action = UriHelper::getUrl($action, false);
        } else {
                $this->action = (string) $action;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param FormElement $element
     * @return $this
     */
    public function add(FormElement $element) {
        $this->elements[$element->getName()] = $element;

        if (array_key_exists($element->getName(), $this->values)) {
            $element->setValue($this->values[$element->getName()]);
        }

        if (array_key_exists($element->getName(), $this->errors)) {
            $element->setHasError(true)->setError($this->errors[$element->getName()]);
        }

        $element->setFormName($this->name);

        return $this;
    }

    /**
     * @param string $name
     * @return FormElement
     * @throws Exception
     */
    public function get($name) {
        if (!$this->exists($name)) {
            throw new Exception('Element not found');
        }

        return $this->elements[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists($name) {
        return array_key_exists($name, $this->elements);
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return $this
     */
    public function processRequest(ServerRequestInterface $request) {
        if (!$request->isPost()) {
            return $this;
        }

        $body = $request->getParsedBody();
        if (!array_key_exists($this->name, $body) || !is_array($body[$this->name])) {
            return $this;
        }

        $values = $body[$this->name];

        $this->isSubmitted = true;
        $this->isValid = $this->validator->validate($values);
        $values = $this->validator->getValues();
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $this->elements)) {
                $this->elements[$key]->setValue($value);
            }
        }
        $errors = $this->validator->getErrors();
        foreach ($errors as $key => $error) {
            if ($this->exists($key)) {
                $this->get($key)->setHasError(true);
            }
        }

        return $this;
    }

    public function saveValues() {
        $this->writeValues();
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSubmitted() {
        return $this->isSubmitted;
    }

    /**
     * @return bool
     */
    public function getIsValid() {
        return $this->isValid;
    }

    /**
     * @return string[]
     */
    public function getValues() {
    	$values = [];
    	foreach ($this->elements as $element) {
    		$values[$element->getName()] = $element->getValue();
		}
        return $values;
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getValue($name) {
        return $this->get($name)->getValue();
    }

    /**
     * @param $name
     * @param $error
     * @return $this
     */
    public function setError($name, $error) {
        $this
            ->get($name)
            ->setHasError(true)
            ->setError($error);
        $this->isValid = false;
        return $this;
    }

    /**
     * @return Validator
     */
    public function getValidator() {
        return $this->validator;
    }

    /**
     * @param string $name
     * @param array $rules
     * @return $this;
     */
    public function setRules($name, array $rules) {
        $this->validator->setRules($name, $rules);
        return $this;
    }

    /**
     * @param string $field
     * @param array $rules
     * @return $this
     */
    public function addRules($field, array $rules) {
        $this->validator->addRules([
            $field => $rules
        ]);
        return $this;
    }

    public function offsetExists($name) {
        return $this->exists($name);
    }

    public function offsetGet($name) {
        return $this->get($name);
    }

    public function offsetSet($name, $value) {}
    public function offsetUnset($name) {}

    /**
     * Load values and errors from session
     */
    protected function loadValues() {
        $key = $this->getSessionKey();
        $sessionData = $this->session->get($key);
        if ($sessionData !== null) {
            $this->values = $sessionData['values'];
            $this->errors = $sessionData['errors'];
            $this->session->delete($key);
        }
    }

    /**
     * Save values and errors to session
     */
    protected function writeValues() {
        $values = []; $errors = [];
        foreach ($this->elements as $element) {
            if ($element->getType() != 'password') {
                $values[$element->getName()] = $element->getValue();
            }
            if ($element->getHasError()) {
                $errors[$element->getName()] = $element->getError();
            }
        }
		$key = $this->getSessionKey();
        $this->session->set($key, [
        	'values' => $values,
        	'errors' => $errors,
		]);
    }

    protected function getSessionKey() {
        return 'form_' . $this->name;
    }
}
