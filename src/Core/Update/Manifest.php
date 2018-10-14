<?php
namespace GameX\Core\Update;

class Manifest {
    
    /**
     * @var string
     */
    protected $dir;
    
    /**
     * @var array
     */
    protected $manifest;

    public function __construct($path) {
        $this->dir = dirname($path) . DIRECTORY_SEPARATOR;
        $this->manifest = json_decode(file_get_contents($path), true);
    }
    
    /**
     * @return string
     */
    public function getDir() {
        return $this->dir;
    }

    public function getVersion() {
        return $this->manifest['version'];
    }

    public function getFiles() {
        return $this->manifest['files'];
    }
}
