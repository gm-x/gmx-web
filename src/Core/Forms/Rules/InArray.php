<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class InArray extends BaseRule {

	/**
	 * @var array
	 */
	protected $values;

	/**
	 * @param array $values
	 */
	public function __construct(array $values) {
		$this->values = $values;
	}

	/**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        return in_array($element->getValue(), $this->values);
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['in_array'];
    }
}
