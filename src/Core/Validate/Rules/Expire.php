<?php
namespace GameX\Core\Validate\Rules;

use \GameX\Core\Validate\Rules\DateTime as DateTimeRule;
use \Carbon\Carbon;

class Expire extends DateTimeRule {

	const FOR_TIME = [
		'years',
		'months',
		'days',
		'weeks',
		'hours',
		'minutes',
		'seconds'
	];
    
    /**
     * @var string
     */
    protected $format = 'Y-m-d';

	/**
	 * @inheritdoc
	 */
	public function validate($value, array $values) {
		if (!is_array($value) || !isset($value['type'])) {
			return null;
		}

		switch ($value['type']) {
			case 'for_time': {
				return $$this->isValidForTime($value)
					? Carbon::now()->add($value['for_time_value'], $value['for_time_type'])
					: null;
			}

			case 'to_date': {
				return Carbon::createFromFormat($this->format, $value['to_date_value']);
			}

			default: {
				return null;
			}
		}
	}

    /**
     * @return array
     */
	protected function getMessage() {
        return ['expire'];
    }

	/**
	 * @param array $value
	 * @return bool
	 */
    protected function isValidForTime(array $value) {
		return isset($value['for_time_type'])
			&& isset($value['for_time_value'])
			&& in_array($value['for_time_type'], self::FOR_TIME, true);
    }
}
