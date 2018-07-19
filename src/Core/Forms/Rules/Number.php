<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Number extends BaseRule {

	/**
	 * @var int|null
	 */
	protected $min;

	/**
	 * @var int|null
	 */
	protected $max;

	/**
	 * @param int|null $min
	 * @param int|null $max
	 */
	public function __construct($min = null, $max = null) {
		$this->min = $min;
		$this->max = $max;
	}

	/**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
    	$options = [];
    	if ($this->min !== null) {
    		$options['min_range'] = (int) $this->min;
		}
    	if ($this->max !== null) {
    		$options['max_range'] = (int) $this->max;
		}

        $value = filter_var($element->getValue(), FILTER_VALIDATE_INT, ['options' => $options]);
    	if ($value === false) {
    		return false;
		}
		$element->setValue($value);
		return true;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
    	if ($this->min !== null && $this->max !== null) {
    		return ['int_min_max', [$this->min, $this->max]];
		} elseif ($this->min !== null) {
			return ['int_min', [$this->min]];
		} elseif ($this->max !== null) {
			return ['int_max', [$this->max]];
		} else {
    		return ['int'];
		}
    }
}
