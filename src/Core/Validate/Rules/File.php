<?php
namespace GameX\Core\Validate\Rules;

use \Psr\Http\Message\UploadedFileInterface;

class File extends BaseRule {

    protected $error;
    
    /**
     * @param UploadedFileInterface|null $value
     * @param array $values
     * @return UploadedFileInterface|null
     */
    public function validate($value, array $values) {
        if ($value === null || !($value instanceof UploadedFileInterface)) {
            return null;
        }

        if ($value->getError() === UPLOAD_ERR_OK) {
            return $value;
        }

        switch ($value->getError()) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE: {
                $this->error = 'file_upload_size';
            }

            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_FILE: {
                $this->error = 'file_upload_no_file';
            }

            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE: {
                $this->error = 'file_upload_cant_write';
            }
        }

        return null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return [$this->error];
    }
}
