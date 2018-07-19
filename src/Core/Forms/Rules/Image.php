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
	protected $message = [];

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
	 * Image constructor.
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

		$size = getimagesize($file->file);
        if ($size === false) {
        	$this->message = ['image'];
        	return false;
		}

		if ($this->types !== null && !in_array($size[self::SIZE_TYPE], $this->types)) {
			$this->message = ['image'];
			return false;
		}

		if ($this->width !== null) {
        	if (is_array($this->width)) {
        		if (!$this->validateSize($size[self::SIZE_WIDTH], $this->width, 'width')) {
					return false;
				}
			} elseif ($size[self::SIZE_WIDTH] !== $this->width) {
				$this->message = ['image_width', [$this->width]];
        		return false;
			}
		}

		if ($this->height !== null) {
			if (is_array($this->height)) {
				if (!$this->validateSize($size[self::SIZE_HEIGHT], $this->height, 'height')) {
					return false;
				}
			} elseif ($size[self::SIZE_HEIGHT] !== $this->height) {
				$this->message = ['image_height', [$this->height]];
				return false;
			}
		}

		return true;
    }
    
    /**
     * @return array
     */
    public function getMessageKey() {
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
			$this->message = ['image_' . $prefix . '_max', [$min]];
			return false;
		} else {
    		return true;
		}
	}
}
