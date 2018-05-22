<?php

namespace GameX\Core\Forms;

use \Psr\Http\Message\ServerRequestInterface;
use \Form\Validator;
use \GameX\Core\Session\Session;

class Form {
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
     * Form constructor.
     * @param ServerRequestInterface $request
     * @param Session $session
     * @param $name
     */
    public function __construct(Session $session, $name) {
        $this->session = $session;
        $this->name = $name;
        $this->validator = new Validator();

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
        $this->action = (string) $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param $name
     * @param string $value
     * @param array $options
     * @param array $rules
     * @return $this
     */
    public function add($name, $value = '', array $options = [], array $rules = []) {
        $this->fields[$name] = [
            'id' => array_key_exists('id', $options) ? (string)$options['id'] : $this->generateFieldId($name),
            'name' => array_key_exists('name', $options) ? (string)$options['name'] : $this->generateFieldName($name),
            'type' => array_key_exists('type', $options) ? (string)$options['type'] : 'text',
            'required' => array_key_exists('required', $options) ? (bool)$options['required'] : false,
            'title' => array_key_exists('title', $options) ? (string)$options['title'] : ucfirst($name),
            'error' => array_key_exists('error', $options) ? (string)$options['error'] : '',
            'classes' => array_key_exists('classes', $options) ? (array)$options['classes'] : [],
            'attributes' => array_key_exists('attributes', $options) ? (array)$options['attributes'] : [],
			'values' => array_key_exists('values', $options) ? (array)$options['values'] : [],
        ];
        if (!array_key_exists($name, $this->values)) {
            $this->values[$name] = $value;
        }
        if (!array_key_exists($name, $this->errors)) {
            $this->errors[$name] = false;
        }
        $this->validator->setRules($name, $rules);

        return $this;
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
            if (array_key_exists($key, $this->values)) {
                $this->values[$key] = $value;
            }
        }
        $errors = $this->validator->getErrors();
        foreach ($errors as $key => $error) {
            if ($this->checkExistsField($key)) {
                $this->errors[$key] = $this->fields[$key]['error'];
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
     * @param $isValid
     * @return $this
     */
    public function setIsValid($isValid) {
        $this->isValid = (bool)$isValid;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getValues() {
        return $this->values;
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getValue($name) {
        return array_key_exists($name, $this->values)
            ? $this->values[$name]
            : null;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setValue($name, $value) {
        if (array_key_exists($name, $this->values)) {
            $this->values[$name] = $value;
        }
        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getError($name) {
        return array_key_exists($name, $this->errors)
            ? $this->errors[$name]
            : null;
    }

    /**
     * @param $name
     * @param $error
     * @return $this
     */
    public function setError($name, $error) {
        $this->errors[$name] = $error;
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

	/**
	 * @param $name
	 * @return string|null
	 */
    public function getType($name) {
    	return $this->checkExistsField($name)
			? $this->fields[$name]['type']
			: null;
	}

	public function getFieldValues($name) {
    	return $this->checkExistsField($name)
			? $this->fields[$name]['values']
			: [];
	}

    /**
     * @param $name
     * @param $key
     * @return mixed|null
     */
    public function getFieldData($name, $key) {
        if (!$this->checkExistsField($name)) {
            return null;
        }

        if (!array_key_exists($key, $this->fields[$name])) {
            return null;
        }

        return $this->fields[$name][$key];
    }

    protected function checkExistsField($name) {
		return array_key_exists($name, $this->fields);
	}

    /**
     * @param $name
     * @return string
     */
    protected function generateFieldName($name) {
        return sprintf('%s[%s]', $this->name, $name);
    }

    /**
     * @param $name
     * @return string
     */
    protected function generateFieldId($name) {
        return 'input' . ucfirst($this->name) . ucfirst($name);
    }

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
        $values = [];
        foreach ($this->fields as $key => $field) {
            if (!array_key_exists($key, $this->values)) {
                continue;
            }

            if ($field['type'] == 'password') {
                continue;
            }

            $values[$key] = $this->values[$key];
        }

        $this->session->set($this->getSessionKey(), [
            'values' => $values,
            'errors' => $this->errors,
        ]);
    }

    protected function getSessionKey() {
        return 'form_' . $this->name;
    }
}
