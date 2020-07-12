<?php

namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \GameX\Core\Update\Updater;
use \GameX\Core\Update\Manifest;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Forms\Elements\File as FileInput;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Rules\File as FileRule;
use \GameX\Core\Validate\Rules\Boolean;
use \GameX\Core\Validate\Rules\FileExtension;
use \GameX\Core\Validate\Rules\FileSize;
use \Psr\Http\Message\UploadedFileInterface;
use \ZipArchive;
use \GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Update\Exceptions\LastVersionException;
use \GameX\Core\Update\Exceptions\IsModifiedException;
use \GameX\Core\Update\Exceptions\FileNotExistsException;
use \GameX\Core\Update\Exceptions\CanWriteException;
use \GameX\Core\Update\Exceptions\ActionException;

class UpdateForm extends BaseForm
{
    
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
     * @var bool
     */
    protected $hasAccessToEdit;
    
    /**
     * @param Updater $updater
     */
    public function __construct(Updater $updater, $hasAccessToEdit = true)
    {
        $this->updater = $updater;
        $this->hasAccessToEdit = $hasAccessToEdit;
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
        $this->form
	        ->add(new FileInput('updates', '', [
	            'title' => $this->getTranslate('admin_preferences', 'updates_file'),
	            'required' => true,
	            'disabled' => !$this->hasAccessToEdit,
	        ]))
	        ->add(new Checkbox('force', false, [
		        'title' => $this->getTranslate('admin_preferences', 'updates_force'),
		        'required' => false,
		        'disabled' => !$this->hasAccessToEdit,
	        ]))
	        ->add(new Checkbox('dependencies', true, [
		        'title' => $this->getTranslate('admin_preferences', 'update_dependencies'),
		        'required' => false,
		        'disabled' => !$this->hasAccessToEdit,
	        ]));

        $this->form->getValidator()
	        ->set('updates', true, [
                new FileRule(),
                new FileExtension(['zip']),
                new FileSize('10M'),
            ], [
                'check' => Validator::CHECK_EMPTY,
                'trim' => false,
            ])
	        ->set('force', false, [
		        new Boolean()
	        ])
	        ->set('dependencies', false, [
		        new Boolean()
	        ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        try {
            ignore_user_abort(true);
            set_time_limit(60);
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
            $archive->open($tempDir . 'uploads.zip', ZipArchive::CHECKCONS);
            $archive->extractTo($tempDir);

            $updates = new Manifest($tempDir . 'manifest.json');
            $this->updater->run(
            	$updates,
	            (bool) $this->form->getValue('force'),
	            (bool) $this->form->getValue('dependencies')
            );
            return true;
        } catch (LastVersionException $e) {
            throw new ValidationException('You already have last version', 0, $e);
        } catch (IsModifiedException $e) {
            throw new ValidationException('File ' . $e->getFilePath() . ' is modified', 0, $e);
        } catch (FileNotExistsException $e) {
            throw new ValidationException('File ' . $e->getFilePath() . ' doesn\'t exists', 0, $e);
        } catch (CanWriteException $e) {
            throw new ValidationException('Can\'t write to file ' . $e->getFilePath(), 0, $e);
        } catch (ActionException $e) {
            throw new ValidationException('Something went wrong while updating', 0, $e);
        }
    }
}
