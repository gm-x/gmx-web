<?php
namespace GameX\Core\Forms\Elements;

use \Psr\Http\Message\UploadedFileInterface;

class File extends Input {
    
    /**
     * @var UploadedFileInterface
     */
    protected $value;

    /**
     * @inheritdoc
     */
    public function getType() {
        return 'file';
    }

	/**
	 * @return string
	 */
	public function getExtension() {
		return pathinfo($this->value->getClientFilename(), PATHINFO_EXTENSION);
	}
}
