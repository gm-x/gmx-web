<?php
namespace GameX\Core\Validate\Rules;

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
     * @inheritdoc
     */
    public function validate($value, array $values) {
    	$options = [];
    	if ($this->min !== null) {
    		$options['min_range'] = (int) $this->min;
		}
    	if ($this->max !== null) {
    		$options['max_range'] = (int) $this->max;
		}

        $value = filter_var($value, FILTER_VALIDATE_INT, ['options' => $options]);
    	return $value !== false ? $value : null;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
    	if ($this->min !== null && $this->max !== null) {
    		return ['number_min_max', [$this->min, $this->max]];
		} elseif ($this->min !== null) {
			return ['number_min', [$this->min]];
		} elseif ($this->max !== null) {
			return ['number_max', [$this->max]];
		} else {
    		return ['number'];
		}
    }
}
