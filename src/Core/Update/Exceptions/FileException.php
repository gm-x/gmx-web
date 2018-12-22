<?php
namespace GameX\Core\Update\Exceptions;

abstract class FileException extends  \Exception {
    /**
     * @var string
     */
    protected $filePath;
    
    /**
     * @param string $filePath
     */
    public function __construct($filePath) {
        parent::__construct();
        $this->filePath = $filePath;
    }
    
    /**
     * @return string
     */
    public function getFilePath() {
        return $this->filePath;
    }
}
