<?php
namespace GameX\Core\Forms\Elements;

class Select extends BaseElement {

	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 * @var null|string
	 */
	protected $emptyOption = null;
    
    /**
     * @var string
     */
	protected $value;

	/**
	 * @inheritdoc
	 */
    public function __construct($name, $value, array $options = [], array $params = []) {
		parent::__construct($name, $value, $params);

		$this->options = $options;
		if (array_key_exists('empty_option', $params)) {
			$this->emptyOption = (string) $params['empty_option'];
		}
	}
    
    /**
     * @inheritdoc
     */
    public function getValue() {
        return $this->value;
    }
    
    /**
     * @inheritdoc
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
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
	public function hasEmptyOption() {
		return $this->emptyOption !== null;
	}

	/**
	 * @return string
	 */
	public function getEmptyOption() {
		return $this->emptyOption;
	}
}
