<?php
namespace GameX\Core\Forms\Elements;

use \DateTime;

class Date extends Input {

	/**
	 * @inheritdoc
	 */
	public function getType() {
		return 'date';
	}
	
	public function setDate(DateTime $dateTime) {
	   
    }

	/**
	 * @return int
	 */
    public function getDate() {
    	return $this->value;
	}
}
