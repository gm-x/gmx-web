<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
use \Slim\Http\UploadedFile;

class Image extends BaseRule {

	const SIZE_WIDTH = 0;
	const SIZE_HEIGHT = 0;
	const SIZE_TYPE = 2;

	/**
	 * @var array
	 */
	protected $message = null;

	/**
	 * @var array|null
	 */
	protected $types;

	/**
	 * @var int|array|null
	 */
	protected $width;

	/**
	 * @var int|array|null
	 */
	protected $height;

	/**
	 * @param array|null $types
	 * @param array|int|null $width
	 * @param array|int|null $height
	 */
	public function __construct(array $types = null, $width = null, $height = null) {
		$this->types = $types;
		$this->width = $width;
		$this->height = $height;
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
    
        $size = getimagesize($value->file);
        if ($size === false) {
        	$this->message = ['image'];
        	return null;
		}

		if ($this->types !== null && !in_array($size[self::SIZE_TYPE], $this->types)) {
			$this->message = ['image'];
			return null;
		}

		if ($this->width !== null) {
        	if (is_array($this->width)) {
        		if (!$this->validateSize($size[self::SIZE_WIDTH], $this->width, 'width')) {
					return null;
				}
			} elseif ($size[self::SIZE_WIDTH] !== $this->width) {
				$this->message = ['image_width', [$this->width]];
        		return null;
			}
		}

		if ($this->height !== null) {
			if (is_array($this->height)) {
				if (!$this->validateSize($size[self::SIZE_HEIGHT], $this->height, 'height')) {
					return null;
				}
			} elseif ($size[self::SIZE_HEIGHT] !== $this->height) {
				$this->message = ['image_height', [$this->height]];
				return null;
			}
		}

		return $value;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return $this->message;
    }

	/**
	 * @param int $size
	 * @param array $expected
	 * @param string $prefix
	 * @return bool
	 */
    protected function validateSize($size, array $expected, $prefix) {
    	list ($min, $max) = $expected;
    	if ($min && $size < $min) {
    		$this->message = ['image_' . $prefix . '_min', [$min]];
    		return false;
		} elseif ($max && $size > $max) {
			$this->message = ['image_' . $prefix . '_max', [$max]];
			return false;
		} else {
    		return true;
		}
	}
}
