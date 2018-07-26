<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\PasswordRepeat;
use \GameX\Core\Exceptions\FormException;

class ResetPasswordCompleteForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'reset_password';

	/**
	 * @var AuthHelper
	 */
	protected $authHelper;

	/**
	 * @var string
	 */
	protected $code;

	/**
	 * @param AuthHelper $authHelper
	 * @param string $code
	 */
	public function __construct(AuthHelper $authHelper, $code) {
		$this->authHelper = $authHelper;
		$this->code = $code;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new Text('login', '', [
				'title' => $this->getTranslate('inputs', 'login_email'),
				'required' => true,
			]))
			->add(new Password('password', '', [
				'title' => $this->getTranslate('inputs', 'password'),
				'error' => $this->getTranslate('labels', 'required'),
				'required' => true,
			]))
			->add(new Password('password_repeat', '', [
				'title' => $this->getTranslate('inputs', 'password_repeat'),
				'error' => 'Passwords does not match',
				'required' => true,
			]))
			->addRule('login', new Trim())
			->addRule('login', new Required())
			->addRule('password', new Trim())
			->addRule('password', new Required())
			->addRule('password_repeat', new Trim())
			->addRule('password_repeat', new PasswordRepeat(['element' => 'password']));
	}

	/**
	 * @return \GameX\Core\Auth\Models\UserModel
	 * @throws FormException
	 */
	protected function processForm() {
		$user = $this->authHelper->findUser($this->form->getValue('login'));
		if (!$user) {
			throw new FormException('login', 'User not found');
		}
		$this->authHelper->resetPasswordComplete($user, $this->form->getValue('password'), $this->code);
		return $user;
	}
}
