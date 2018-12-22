<?php
namespace GameX\Core\Forms\Elements;

use \GameX\Core\Forms\Element;

abstract class BaseElement implements Element {
    /**
     * @var string
     */
    protected $id;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var bool
     */
    protected $isRequired = false;
    
    /**
     * @var string
     */
    protected $title;
    
    /**
     * @var string|null
     */
    protected $description;
    
    /**
     * @var string
     */
    protected $icon;

    /**
     * @var array
     */
    protected $classes = [];
    
    /**
     * @var array
     */
    protected $attributes = [];
    
    /**
     * @var null|string
     */
    protected $formName = null;
    
    /**
     * @var bool
     */
    protected $hasError = false;
    
    /**
     * @var string
     */
    protected $error;
    
    /**
     * Input constructor.
     * @param string $name
     * @param mixed $value
     * @param array $options
     */
    public function __construct($name, $value, array $options = []) {
        $this->name = (string) $name;
        $this->setValue($value);
        $this->id = $this->replaceIdValue(
            array_key_exists('id', $options) ? (string) $options['id'] : $this->generateFieldId($name)
        );
        $this->title = array_key_exists('title', $options) ? (string) $options['title'] : ucfirst($name);
        $this->description = array_key_exists('description', $options) ? (string) $options['description'] : null;
        if (array_key_exists('required', $options)) {
            $this->isRequired = (bool) $options['required'];
        }
        if (array_key_exists('icon', $options)) {
            $this->icon = (string) $options['icon'];
        }
        if (array_key_exists('classes', $options)) {
            $this->classes = (array) $options['classes'];
        }
        if (array_key_exists('attributes', $options)) {
            $this->attributes = (array) $options['attributes'];
        }
    }
    
    /**
     * @inheritdoc
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * @inheritdoc
     */
    public function setName($name) {
        $this->name = (string) $name;
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * @inheritdoc
     */
    public function getIsRequired() {
        return $this->isRequired;
    }
    
    /**
     * @inheritdoc
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * @inheritdoc
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * @inheritdoc
     */
    public function getClasses() {
        return $this->classes;
    }
    
    /**
     * @inheritdoc
     */
    public function getAttributes() {
        return $this->attributes;
    }
    
    /**
     * @inheritdoc
     */
    public function getFormName() {
        return $this->formName;
    }
    
    /**
     * @inheritdoc
     */
    public function setFormName($formName) {
        $this->formName = (string) $formName;
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function getHasError() {
        return $this->hasError;
    }
    
    /**
     * @inheritdoc
     */
    public function setHasError($hasError) {
        $this->hasError = (bool) $hasError;
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * @inheritdoc
     */
    public function setError($error) {
        $this->error = (string) $error;
        return $this;
    }
    
    public function getType() {
        return '';
    }
    
    /**
     * @inheritdoc
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @inheritdoc
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @param $name
     * @return string
     */
    protected function generateFieldId($name) {
        return 'input-' . ucfirst($this->name) . '-' . ucfirst($name);
    }
    
    /**
     * @param string $text
     * @return string
     */
    protected function replaceIdValue($text) {
        $text = strtolower(htmlentities($text));
        $text = str_replace(get_html_translation_table(), "-", $text);
        $text = str_replace(" ", "-", $text);
        $text = preg_replace("/[-]+/i", "-", $text);
        return $text;
    }
}
