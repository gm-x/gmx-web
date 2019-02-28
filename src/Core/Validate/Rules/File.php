<?php
namespace GameX\Core\Validate\Rules;

use \Psr\Http\Message\UploadedFileInterface;

class File extends BaseRule {
    
    /**
     * @param UploadedFileInterface|null $value
     * @param array $values
     * @return UploadedFileInterface|null
     */
    public function validate($value, array $values) {
        if ($value === null || !($value instanceof UploadedFileInterface)) {
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
