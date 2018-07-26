<?php
namespace GameX\Core\Auth\Models;

use \Cartalyst\Sentinel\Permissions\PermissionsInterface;
use \Cartalyst\Sentinel\Permissions\PermissionsTrait;

class PermissionsModel implements PermissionsInterface {

	use PermissionsTrait;

	/**
	 * @return array
	 */
	protected function createPreparedPermissions() {
		$prepared = [];

		foreach ($this->secondaryPermissions as $keys => $value) {
			foreach ($this->extractClassPermissions($keys) as $key) {
				// If the value is not in the array, we're opting in
				if (! array_key_exists($key, $prepared)) {
					$prepared[$key] = $value;

					continue;
				}

				// If our value is in the array and equals false, it will override
				if ($value === false) {
					$prepared[$key] = $value;
				}
			}
		}

		return $prepared;
	}
}
