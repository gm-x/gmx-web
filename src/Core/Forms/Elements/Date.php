<?php
namespace GameX\Core\Forms\Elements;


class Date extends DateTimeInput {
    
    /**
     * @var string
     */
    protected $format = 'Y-m-d';
    
	/**
	 * @inheritdoc
	 */
	public function getType() {
		return 'date';
	}
}
