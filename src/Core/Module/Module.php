<?php
namespace GameX\Core\Module;

use \Iterator;

class Module  implements Iterator {

	/**
	 * @var ModuleInterface[]
	 */
	protected $modules = [];

	/**
	 * @var integer
	 */
	protected $position = 0;

	/**
	 * @param ModuleInterface $module
	 * @return $this
	 */
	public function addModule(ModuleInterface $module) {
		$this->modules[] = $module;
		return $this;
	}

	/**
	 * @return ModuleInterface[]
	 */
	public function getModules() {
		return $this->modules;
	}

	/**
	 * @inheritdoc
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * @inheritdoc
	 */
	public function current() {
		return $this->modules[$this->position];
	}

	/**
	 * @inheritdoc
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * @inheritdoc
	 */
	public function next() {
		++$this->position;
	}

	/**
	 * @inheritdoc
	 */
	public function valid() {
		return isset($this->modules[$this->position]);
	}
}
