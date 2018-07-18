<?php
namespace GameX\Forms\Settings;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Email as EmailRule;
use \GameX\Core\Exceptions\FormException;

class EmailForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'user_settings_email';

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
			->add(new EmailElement('email', $this->user->email, [
				'title' => 'Email',
				'required' => true
			]))
			->addRule('old_password', new Required())
			->addRule('old_password', new EmailRule());
	}

	/**
	 * @return boolean
	 * @throws FormException
	 */
	protected function processForm() {
		$this->user->email = $this->form->getValue('email');
		$this->user->save();

		return true;
	}
}
