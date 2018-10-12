<?php
namespace GameX\Core\Update;

class Manifest {
    protected $manifest;

    public function __construct($path) {
        $this->manifest = json_decode(file_get_contents($path), true);
    }

    public function getVersion() {
        return $this->manifest['version'];
    }

    public function getFiles() {
        return $this->manifest['files'];
    }
}