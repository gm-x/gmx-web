<?php

namespace GameX\Core\Forms;

use \Slim\Http\Request;
use \Form\Validator;

class FormHelper {
    protected $name = null;
    protected $isSubmitted = false;
    protected $isValid = null;
    protected $fields = [];
    protected $values = [];
    protected $errors = [];
    protected $validator;

    public function __construct($name) {
        $this->name = $name;
        $this->validator = new Validator();

        if (array_key_exists('forms', $_SESSION) && array_key_exists($this->name, $_SESSION['forms'])) {
            $this->values = $_SESSION['forms'][$this->name]['values'];
            $this->errors = $_SESSION['forms'][$this->name]['errors'];
            unset($_SESSION['forms'][$this->name]);
        }
    }

    public function addField($name, $value = '', array $options = [], array $rules = []) {
        $this->fields[$name] = [
            'id' => array_key_exists('id', $options) ? $options['id'] : null,
            'type' => array_key_exists('type', $options) ? $options['type'] : 'text',
            'required' => array_key_exists('required', $options) ? (bool)$options['required'] : false,
            'title' => array_key_exists('title', $options) ? $options['title'] : ucfirst($name),
            'description' => array_key_exists('description', $options) ? $options['description'] : null,
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

    public function processRequest(Request $request) {
        if (!$request->isPost()) {
            return $this;
        }
        $body = $request->getParsedBody();
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
        foreach ($errors as $key => $value) {
            if (array_key_exists($key, $this->errors)) {
                $this->errors[$key] = $values;
            }
        }

        return $this;
    }

    public function getIsSubmitted() {
        return $this->isSubmitted;
    }

    public function getIsValid() {
        return $this->isValid;
    }

    public function getValues() {
        return $this->values;
    }

    public function setValue($name, $value) {
        if (array_key_exists($name, $this->values)) {
            $this->values[$name] = $value;
        }
        return $this;
    }

    public function saveValues() {
        $_SESSION['forms'][$this->name] = [
            'values' => $this->values,
            'errors' => $this->errors,
        ];
    }

    public function getValue($name) {
        return array_key_exists($name, $this->values) ? $this->values[$name] : null;
    }

    public function setError($name) {
        $this->errors[$name] = true;
        $this->isValid = false;
        return $this;
    }

    public function renderInput($name) {
        $id = $this->fields[$name]['id'] !== null ? $this->fields[$name]['id'] : $this->generateId($name);
        $attributes = [];
        foreach($this->fields[$name]['attributes'] as $key => $value) {
            $attributes[] = sprintf('%s="%s"', $key, $value);
        }

        $classes = $this->fields[$name]['classes'];
        if ($this->errors[$name]) {
            $classes[] = 'invalid';
        }
        return '<input
                type="' . $this->fields[$name]['type'] . '"
                id="' . $id . '"
                name="' . $this->generateName($name) . '"
                class="' . implode(' ', $classes) . '"
                value="' . $this->values[$name] . '"
                ' . ($this->fields[$name]['required'] ? ' required' : '') . '
                ' . implode(' ', $attributes) . '
            >';
    }

    public function renderLabel($name) {
        $id = $this->fields[$name]['id'] !== null ? $this->fields[$name]['id'] : $this->generateId($name);
        return '<label for="' . $id . '">' . $this->fields[$name]['title'] . '</label>';
    }

    public function renderError($name) {
        return ($this->errors[$name] && $this->fields[$name]['description'] !== null)
            ? '<span class="helper-text red-text">' . $this->fields[$name]['description'] . '</span>'
            : '';
    }

    protected function generateName($name) {
        return sprintf('%s[%s]', $this->name, $name);
    }

    protected function generateId($name) {
        return 'input' . ucfirst($this->name) . ucfirst($name);
    }
}
