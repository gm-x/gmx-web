<?php
namespace GameX\Core\Menu;

class Menu implements \Iterator {

	/**
	 * @var integer
	 */
	protected $position = 0;

	/**
	 * @var string
	 */
	protected $active;

	/**
	 * @var MenuItem[]
	 */
	protected $items = [];

	public function __construct() {
		$this->position = 0;
	}

	/**
	 * @param string $route
	 */
	public function setActiveRoute($route) {
		$this->active = $route;
	}

	/**
	 * @return string
	 */
	public function getActiveRoute() {
		return $this->active;
	}

	/**
	 * @param MenuItem $item
	 * @return $this
	 */
	public function add(MenuItem $item) {
		$this->items[] = $item;
		return $this;
	}

	public function getItems() {
		return $this->items;
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->items[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return isset($this->items[$this->position]);
	}
}
