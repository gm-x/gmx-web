<?php

namespace GameX\Core\Validate\Rules;

use \Carbon\Carbon;
use \Carbon\CarbonInterval;

class Expired extends BaseRule
{
    const FOR_TIME = [
        'years',
        'months',
        'days',
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
        if (!is_array($value)) {
            return null;
        }

        if (isset($value['forever']) && filter_var($value['forever'], FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        if (empty($value['type'])) {
            $value['type'] = 'datetime';
        }

        switch ($value['type']) {
            case 'datetime': {
                if (empty($value['datetime'])) {
                    return null;
                }
                return Carbon::createFromFormat($this->format, $value['datetime']);
            }

            case 'interval': {
                if (!$this->isValidForTime($value)) {
                    return null;
                }
                $interval = CarbonInterval::make($value['count'] . $value['interval']);
                return Carbon::now()->add($interval);
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getMessage() {
        return ['expired'];
    }

    /**
    * @param array $value
    * @return bool
    */
    protected function isValidForTime(array $value) {
        return isset($value['count'])
            && isset($value['interval'])
            && $value['count'] > 0
            && in_array($value['interval'], self::FOR_TIME, true);
    }
}