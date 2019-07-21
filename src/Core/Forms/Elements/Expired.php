<?php
namespace GameX\Core\Forms\Elements;

use GameX\Core\Forms\Element;

class Expired extends BaseElement
{

	protected $created;

	public function __construct($name, $value, array $options = []) {
		$this->created = $options['created'];
		parent::__construct($name, $value/rt, $options);
	}

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'expired';
    }

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		// TODO: Implement getValue() method.
	}

	/**
	 * @param mixed $value
	 * @return Element
	 */
	public function setValue($value)
	{
		// TODO: Implement setValue() method.
	}
}
