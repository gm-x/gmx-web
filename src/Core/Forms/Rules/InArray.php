<?php
namespace GameX\Core\Forms\Rules;

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
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return in_array($value, $this->values) ? $value : null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['in_array'];
    }
}
