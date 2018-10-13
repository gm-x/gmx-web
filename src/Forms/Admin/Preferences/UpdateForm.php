<?php
namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \GameX\Core\Update\Updater;
use \GameX\Core\Update\Manifest;
use \GameX\Core\Forms\Validator;
use \GameX\Core\Forms\Elements\File as FileInput;
use \GameX\Core\Forms\Rules\File as FileRule;
use \GameX\Core\Forms\Rules\FileExtension;
use \GameX\Core\Forms\Rules\FileSize;
use \Psr\Http\Message\UploadedFileInterface;
use \ZipArchive;

class UpdateForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_preferences_update';

	/**
	 * @var Updater
	 */
	protected $updater;

    /**
     * @var Manifest
     */
	protected $manifest;

	/**
	 * @param Updater $updater
	 */
	public function __construct(Updater $updater) {
		$this->updater = $updater;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
	    $this->form->add(new FileInput('updates', '', [
            'title' => 'Updates',
            'required' => true
        ]));
	    $this->form->getValidator()
            ->set('updates', true, [
                new FileRule(),
                new FileExtension(['zip']),
                new FileSize('10M'),
            ], [
                'check' => Validator::CHECK_EMPTY,
                'trim' => false,
            ]);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm() {
        try {
            /** @var UploadedFileInterface $value */
            $value = $this->form->getValue('updates');
            $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('GameX', true) . DIRECTORY_SEPARATOR;
    
            if (!is_dir($tempDir)) {
                if (!mkdir($tempDir, 0777, true)) {
                    throw new \Exception('Can\'t create folder ' . $tempDir);
                }
            }
            
            $value->moveTo($tempDir . 'uploads.zip');
            
            $archive = new ZipArchive();
            $archive->open($tempDir . 'uploads.zip',ZipArchive::CHECKCONS);
            $archive->extractTo($tempDir);

            
            $updates = new Manifest($tempDir . 'manifest.json');
            $this->updater->run($updates);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
