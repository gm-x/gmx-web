<?php
namespace GameX\Core\Forms\Elements;


class Expire extends DateTimeInput {
    
    /**
     * @var string
     */
    protected $format = 'Y-m-d';
    
	/**
	 * @inheritdoc
	 */
	public function getType() {
		return 'expire';
	}
}
