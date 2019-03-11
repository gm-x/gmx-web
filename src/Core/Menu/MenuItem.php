<?php
namespace GameX\Core\Menu;

class MenuItem {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $route;

	/**
	 * @var array
	 */
	protected $params = [];

	/**
	 * @var string|null
	 */
	protected $permission = null;
    
    /**
     * @var string|null
     */
	protected $icon = null;

	/**
	 * MenuItem constructor.
	 * @param string $title
	 * @param string $route
	 * @param array $params
	 * @param string|null $permission
	 * @param string|null $icon
	 */
	public function __construct($title, $route, array $params = [], $permission = null, $icon = null) {
		$this->title = $title;
		$this->route = $route;
		$this->params = $params;
		$this->permission = $permission;
		$this->icon = $icon;
	}

	/**
	 * @param string $key
	 * @return array|null|string
	 */
	public function __get($key) {
		switch ($key) {
			case 'title': {
				return $this->getTitle();
			}

			case 'route': {
				return $this->getRoute();
			}

			case 'params': {
				return $this->getParams();
			}

			case 'permission': {
				return $this->getPermission();
			}

			case 'icon': {
				return $this->getIcon();
			}

			default: {
				return null;
			}
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @return null|string
	 */
	public function getPermission() {
		return $this->permission;
	}

	/**
	 * @return null|string
	 */
	public function getIcon() {
		return $this->icon;
	}
}
