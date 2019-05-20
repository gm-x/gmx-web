<?php
namespace GameX\Core\Validate\Rules;

use \GameX\Core\Validate\Rules\DateTime as DateTimeRule;

class Expire extends DateTimeRule {
    
    /**
     * @var string
     */
    protected $format = 'Y-m-d';

	/**
	 * @inheritdoc
	 */
	public function validate($value, array $values) {
		if (!is_array($value)) {
			return null;
		}

		if (isset($value['forever']) && filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
			return 0; // TODO return special value
		} elseif(isset($value['for_time'])) {
			return 0;
		} elseif (isset($value['to_date'])) {
			return \DateTime::createFromFormat($this->format, $value);
		}
		return null;
	}

    /**
     * @return array
     */
	protected function getMessage() {
        return ['expire'];
    }
}
