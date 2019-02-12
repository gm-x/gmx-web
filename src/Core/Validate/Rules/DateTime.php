<?php
namespace GameX\Core\Validate\Rules;

class DateTime extends BaseRule {
    
    /**
     * @var string
     */
    protected $format = 'Y-m-d H:i:s';
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        $date = \DateTime::createFromFormat($this->format, $value);
        return($date !== false) ? $date : null;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['date_time'];
    }
}
