<?php
namespace GameX\Core\Forms\Elements;

class FormSelect extends FormInput {

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
    public function __construct($name, $value, array $options = []) {
		parent::__construct($name, $value, $options);

		if (array_key_exists('options', $options)) {
			$this->options = (array) $options['options'];
		}

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
