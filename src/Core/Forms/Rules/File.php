<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
use \Slim\Http\UploadedFile;

class File extends BaseRule {
    
    /**
     * @param UploadedFile|null $value
     * @param array $values
     * @return UploadedFile|null
     */
    public function validate($value, array $values) {
        if ($value === null || !($value instanceof UploadedFile)) {
            return null;
        }

		return $value->getError() === UPLOAD_ERR_OK ? $value : null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['file'];
    }
}
