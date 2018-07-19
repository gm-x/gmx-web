<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
use \Slim\Http\UploadedFile;

class FileSize extends BaseRule {

	/**
	 * @var integer
	 */
	protected $size;

	public function __construct($size) {
		$this->size = $this->convertToBytes($size);
	}

	/**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        /** @var UploadedFile|null $file */
        $file = $element->getValue();
        if ($file === null) {
            return true;
        }

		return filesize($file->file) <= $this->size;
    }
    
    /**
     * @return array
     */
    public function getMessageKey() {
        return ['file_size', [$this->size]];
    }

	/**
	 * @param $from
	 * @return int
	 */
    protected function convertToBytes($from){
		$number = (int) substr($from,0,-1);
		$letter = strtoupper(substr($from, -1));
		switch($letter) {
			case 'K': {
				return $number * 1024;
			}

			case 'M': {
				return $number * pow(1024, 2);
			}

			case 'G': {
				return $number * pow(1024, 3);
			}

			default: {
				return (int) $from;
			}
		}
	}
}
