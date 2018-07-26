<?php
namespace GameX\Core\Lang\Loaders;

use \GameX\Core\Lang\Interfaces\Loader;
use \GameX\Core\Lang\Exceptions\CantReadException;

class JSONLoader implements Loader {
    protected $baseDir;

    /**
     * JSONLoader constructor.
     * @param string $baseDir
     */
    public function __construct($baseDir) {
        $this->baseDir = $baseDir;
    }

    /**
     * {@inheritDoc}
     */
    public function loadSection($language, $section) {
        $filePath = $this->baseDir . DIRECTORY_SEPARATOR .$language . DIRECTORY_SEPARATOR . $section . '.json';
        if (!is_readable($filePath)) {
            throw new CantReadException('Can\'t read file ' . $filePath);
        }

        $data = file_get_contents($filePath);
        if (!$data) {
            throw new CantReadException('Can\'t read file ' . $filePath);
        }
        $data = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CantReadException('Can\'t read file ' . $filePath);
        }
        return $data;
    }
}
