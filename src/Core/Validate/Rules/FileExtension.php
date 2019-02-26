<?php
namespace GameX\Core\Validate\Rules;

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

	/**
	 * @param array $extensions
	 */
	public function __construct(array $extensions) {
		$this->extensions = [];
		foreach ($extensions as $extension) {
			$this->extensions[] = strtolower(trim($extension));
		}
	}
    
    /**
     * @param UploadedFileInterface|null $value
     * @param array $values
     * @return UploadedFileInterface|null
     */
    public function validate($value, array $values) {
        if ($value === null) {
            return null;
        }

		$this->extension = pathinfo($value->getClientFilename(), PATHINFO_EXTENSION);
        return in_array(strtolower($this->extension), $this->extensions, true) ? $value : null;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['file_extension', [$this->extension]];
    }
}
