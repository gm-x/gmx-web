<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Email as EmailRule;
use \GameX\Core\Forms\Rules\Length;
use \GameX\Core\Forms\Rules\PasswordRepeat;
use \GameX\Core\Exceptions\ValidationException;

class RegisterForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'register';

	/**
	 * @var AuthHelper
	 */
	protected $authHelper;

	/**
	 * @var boolean
	 */
	protected $emailEnabled;

	/**
	 * LoginForm constructor.
	 * @param AuthHelper $authHelper
	 * @param boolean $emailEnabled
	 */
	public function __construct(AuthHelper $authHelper, $emailEnabled) {
		$this->authHelper = $authHelper;
		$this->emailEnabled = $emailEnabled;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new Text('login', '', [
				'title' => $this->getTranslate('inputs', 'login'),
				'required' => true,
			]))
			->add(new EmailElement('email', '', [
				'title' => $this->getTranslate('inputs', 'email'),
				'required' => true,
			]))
			->add(new Password('password', '', [
				'title' => $this->getTranslate('inputs', 'password'),
				'required' => true,
			]))
			->add(new Password('password_repeat', '', [
				'title' => $this->getTranslate('inputs', 'password_repeat'),
				'required' => true,
			]))
			->addRule('login', new Trim())
			->addRule('login', new Required())
			->addRule('email', new Required())
			->addRule('email', new EmailRule())
			->addRule('password', new Length(['min' => 6]))
			->addRule('password_repeat', new PasswordRepeat(['element' => 'password']));
	}

	/**
	 * @return boolean
	 * @throws  ValidationException
	 */
	protected function processForm() {
		if ($this->authHelper->exists($this->form->getValue('login'), $this->form->getValue('email'))) {
			throw new ValidationException('User already exists');
		}

		$user = $this->authHelper->registerUser(
			$this->form->getValue('login'),
			$this->form->getValue('email'),
			$this->form->getValue('password'),
			!$this->emailEnabled
		);

		return $user;
	}
}
