<?php
namespace GameX\Forms\Settings;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Models\UserModel;
use GameX\Core\Forms\Elements\File;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Exceptions\FormException;

class AvatarForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'user_settings_avatar';

	/**
	 * @var UserModel
	 */
	protected $user;

	/**
	 * @param UserModel $user
	 */
	public function __construct(UserModel $user) {
		$this->user = $user;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new File('avatar', '', [
				'title' => 'Avatar',
				'required' => true
			]))
			->addRule('avatar', new Required());
	}

	/**
	 * @return boolean
	 * @throws FormException
	 */
	protected function processForm() {
	    var_dump($this->form->getValue('avatar'));
	    die();
		return true;
	}
}
