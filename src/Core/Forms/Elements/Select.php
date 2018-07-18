<?php
namespace GameX\Core\Forms\Elements;

class Select extends Input {

	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 * @var null|string
	 */
	protected $emptyOption = null;

	/**
	 * @inheritdoc
	 */
    public function __construct($name, $value, array $options = [], array $params = []) {
		parent::__construct($name, $value, $params);

		$this->options = $options;
		if (array_key_exists('empty_option', $options)) {
			$this->emptyOption = (string) $options['empty_option'];
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getType() {
		return 'select';
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return bool
	 */
	public function getEmptyOption() {
		return $this->emptyOption;
	}
}
