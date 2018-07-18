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
     * @return $this
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     * @return $this
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
     * @return $this
     */
    public function setFormName($formName);

    /**
     * @return bool
     */
    public function getHasError();

    /**
     * @param bool $hasError
     * @return $this
     */
    public function setHasError($hasError);

    /**
     * @return string
     */
    public function getError();

    /**
     * @param string $error
     * @return $this
     */
    public function setError($error);
}
