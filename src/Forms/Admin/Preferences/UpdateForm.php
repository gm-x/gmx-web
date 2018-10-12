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
	 * @param Updater $updater
	 */
	public function __construct(Updater $updater) {
		$this->updater = $updater;
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
            $old = new Manifest(__DIR__ . 'old.json');
            $new = new Manifest(__DIR__ . 'new.json');
            $this->updater->run($old, $new);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
