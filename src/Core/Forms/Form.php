<?php

namespace GameX\Core\Forms;

use function Form\Rule\bool;
use \Psr\Http\Message\RequestInterface;
use \SlimSession\Helper;
use \Form\Validator;

class Form {
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Helper
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
     * Form constructor.
     * @param RequestInterface $request
     * @param Helper $session
     * @param $name
     */
    public function __construct(RequestInterface $request, Helper $session, $name) {
        $this->request = $request;
        $this->session = $session;
        $this->name = $name;
        $this->validator = new Validator();

        $this->loadValues();
    }

    /**
     * From destructor
     */
    public function __destruct() {
        $this->saveValues();
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
     * @return $this
     */
    public function processRequest() {
        if (!$this->request->isPost()) {
            return $this;
        }
        $body = $this->request->getParsedBody();
        $values = array_key_exists($this->name, $body) && is_array($body[$this->name])
            ? $body[$this->name]
            : [];

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
            if (array_key_exists($key, $this->fields)) {
                $this->errors[$key] = $this->fields[$key]['error'];
            }
        }

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
     * @param $name
     * @param $key
     * @return mixed|null
     */
    public function getFieldData($name, $key) {
        if (!array_key_exists($name, $this->fields)) {
            return null;
        }

        if (!array_key_exists($key, $this->fields[$name])) {
            return null;
        }

        return $this->fields[$name][$key];
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
    protected function saveValues() {
        if ($this->isValid) {
            return;
        }

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
