<?php
namespace GameX\Core\Forms\Elements;

use \DateTime;

class DateTimeInput extends BaseElement {
    
    protected $format = 'Y-m-d H:i:s';
    
    /**
     * @var DateTime|null
     */
    protected $value;

    /**
	 * @inheritdoc
	 */
	public function getType() {
		return 'datetime';
	}
    
    /**
     * @return DateTime|null
     */
	public function getValue() {
		return $this->value;
	}
    
    /**
     * @param DateTime|string|null $value
     * @return $this
     */
	public function setValue($value) {
	    if ($value instanceof DateTime) {
	        $this->value = $value;
        } elseif($value !== null && !empty($value)) {
	        $date = DateTime::createFromFormat($this->format, $value);
	        $this->value = ($date !== false) ? $date : null;
        } else {
	        $this->value = null;
        }
		return $this;
	}
	
	public function format($format) {
	    return $this->value !== null ? $this->value->format($format) : '';
    }
}
