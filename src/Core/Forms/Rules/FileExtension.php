<?php
namespace GameX\Core\Forms\Rules;


use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
use \Psr\Http\Message\UploadedFileInterface;

class FileExtension extends BaseRule {

	/**
	 * @var array
	 */
	protected $extensions;

	/**
	 * @var string
	 */
	protected $extension;

	public function __construct(array $extensions) {
		$this->extensions = [];
		foreach ($extensions as $extension) {
			$this->extensions[] = strtolower(trim($extension));
		}
	}

	/**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        /** @var UploadedFileInterface|null $file */
        $file = $element->getValue();
        if ($file === null) {
            return true;
        }

		$this->extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        return in_array(strtolower($element->getExtension()), $this->extensions, true);
    }
    
    /**
     * @return array
     */
    public function getMessageKey() {
        return ['file_extension', [$this->extension]];
    }
}
