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

	/**
	 * @param $size
	 */
	public function __construct($size) {
		$this->size = $this->convertToBytes($size);
	}
    
    /**
     * @param UploadedFile|null $value
     * @param array $values
     * @return UploadedFile|null
     */
    public function validate($value, array $values) {
        if ($value === null) {
            return null;
        }

		return filesize($value->file) <= $this->size ? $value : null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
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
