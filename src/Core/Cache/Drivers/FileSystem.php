<?php

namespace GameX\Core\Cache\Drivers;

use \Stash\Driver\FileSystem as FileSystemOriginal;

class FileSystem extends FileSystemOriginal
{
	/**
	 * @inheritDoc
	 */
	public function storeData($key, $data, $expiration)
	{
		$path = $this->makePath($key);

		// MAX_PATH is 260 - http://msdn.microsoft.com/en-us/library/aa365247(VS.85).aspx
		if (strlen($path) > 259 &&  stripos(PHP_OS, 'WIN') === 0) {
			throw new Stash\Exception\WindowsPathMaxLengthException();
		}

		if (!file_exists($path)) {
			if (!is_dir(dirname($path))) {
				if (!@mkdir(dirname($path), $this->dirPermissions, true)) {
					return false;
				}
			}

			if (!(touch($path) && chmod($path, $this->filePermissions))) {
				return false;
			}
		}

		$storeString = $this->getEncoder()->serialize($this->makeKeyString($key), $data, $expiration);
		$result = file_put_contents($path, $storeString, LOCK_EX);

		return false !== $result;
	}
}