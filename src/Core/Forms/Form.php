<?php
namespace GameX\Core\Forms;

use GameX\Core\Forms\Elements\Password;
use \Psr\Http\Message\ServerRequestInterface;
use \GameX\Core\Session\Session;
use \GameX\Core\Lang\Language;
use \GameX\Core\Forms\Elements\File;
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
     * @var Element[]
     */
    protected $elements = [];
    
    /**
     * @var bool
     */
    protected $isSaved = false;

    /**
     * Form constructor.
     * @param Session $session
     * @param Language $language
     * @param $name
     */
    public function __construct(Session $session, Language $language, $name) {
        $this->session = $session;
        $this->validator = new Validator($language);
        $this->name = $name;

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
     * @param Element $element
     * @return $this
     */
    public function add(Element $element) {
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
     * @return Element
     * @throws Exception
     */
    public function get($name) {
        if (!$this->exists($name)) {
            \GameX\Core\Utils::logBacktrace();
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
     * @return Form
     */
    public function processRequest(ServerRequestInterface $request) {
        if (!$request->isPost()) {
            return $this;
        }

        $body = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $fails = 0;
        if (array_key_exists($this->name, $body) && is_array($body[$this->name])) {
            $body = $body[$this->name];
        } else {
            $fails++;
        }
        if (array_key_exists($this->name, $files) && is_array($files[$this->name])) {
            $files = $files[$this->name];
        } else {
            $fails++;
        }
        
        if ($fails >= 2) {
            return $this;
        }

        $this->isSubmitted = true;
        $values = array_merge($body, $files);
        $result = $this->validator->validate($values);
        
        $this->isValid = $result->getIsValid();
        foreach ($this->elements as $element) {
            $key = $element->getName();
            $element->setValue($result->getValue($key));
            if ($result->hasError($key)) {
                $element->setHasError(true)->setError($result->getError($key));
            } else {
                $element->setHasError(false);
            }
        }
        
        return $this;
    }

    public function saveValues() {
        $this->writeValues();
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
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
     * @return mixed
     * @throws Exception
     */
    public function getValue($name) {
        return $this->get($name)->getValue();
    }
    
    /**
     * @return Element[]
     */
    public function getElements() {
        return $this->elements;
    }

    /**
     * @param $name
     * @param $error
     * @return $this
     * @throws Exception
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
        if ($this->isSaved) {
            return;
        }
        $values = []; $errors = [];
        foreach ($this->elements as $element) {
            if (!($element instanceof Password)) {
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
        $this->isSaved = true;
    }
    
    /**
     * @return string
     */
    protected function getSessionKey() {
        return 'form_' . $this->name;
    }
}
