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

	/**
	 * @inheritdoc
	 */
	public function getValue() {
		return $this->value->format('Y-m-d');
	}

	/**
	 * @inheritdoc
	 */
	public function setValue($value) {
		$this->value = new DateTime($value);
		return $this;
	}

	/**
	 * @return int
	 */
	public function getDate() {
		return $this->value;
	}
}
