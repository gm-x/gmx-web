<?php
namespace GameX\Forms\Admin\Preferences;

use \GameX\Core\BaseForm;
use \GameX\Core\Update\Updater;
use \GameX\Core\Update\Manifest;
use \GameX\Core\Forms\Elements\Hidden;

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
	 * @param Manifest $manifest
	 */
	public function __construct(Updater $updater, Manifest $manifest) {
		$this->updater = $updater;
		$this->manifest = $manifest;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
	    $this->form->add(new Hidden('update', ''));
	    $this->form->getValidator()->set('update', false);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm() {
        try {
            $updates = new Manifest(__DIR__ . 'manifest.json');
            $this->updater->run($this->manifest, $updates);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
