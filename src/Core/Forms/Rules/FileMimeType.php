<?php
namespace GameX\Core\Forms\Rules;

use \Slim\Http\UploadedFile;

class FileMimeType extends BaseRule {

	/**
	 * @var array
	 */
	protected $types;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @param array $types
	 */
	public function __construct(array $types) {
		$this->types = [];
		foreach ($types as $type) {
			$this->types[] = strtolower(trim($type));
		}
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

		$this->type = mime_content_type($value->file);
        return in_array(strtolower($this->type), $this->types, true) ? $value : null;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['file_mime_type', [$this->type]];
    }
}
