<?php
namespace GameX\Core\Forms;

interface Element {

    /**
     * Element constructor.
     * @param string $name
     * @param string $value
     * @param array $options
     */
    public function __construct($name, $value, array $options = []);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return Element
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     * @return Element
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return bool
     */
    public function getIsRequired();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return array
     */
    public function getClasses();

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @return string|null
     */
    public function getFormName();

    /**
     * @param string $formName
     * @return Element
     */
    public function setFormName($formName);

    /**
     * @return bool
     */
    public function getHasError();

    /**
     * @param bool $hasError
     * @return Element
     */
    public function setHasError($hasError);

    /**
     * @return string
     */
    public function getError();

    /**
     * @param string $error
     * @return Element
     */
    public function setError($error);
}
