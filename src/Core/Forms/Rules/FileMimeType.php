<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
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

		$this->type = mime_content_type($file->file);
        return in_array(strtolower($this->type), $this->types, true);
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['file_mime_type', [$this->type]];
    }
}
