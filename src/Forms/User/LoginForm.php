<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\Required;

class LoginForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'login';

	/**
	 * @var AuthHelper
	 */
	protected $authHelper;

	/**
	 * LoginForm constructor.
	 * @param AuthHelper $authHelper
	 */
	public function __construct(AuthHelper $authHelper) {
		$this->authHelper = $authHelper;
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
				'required' => true,
			]))
			->add(new Checkbox('remember_me', true, [
				'title' => $this->getTranslate('inputs', 'remember_me'),
				'required' => false,
			]))
			->addRule('login', new Required())
			->addRule('password', new Required());
	}

	/**
	 * @return boolean
	 */
	protected function processForm() {
		$this->authHelper->loginUser(
			$this->form->getValue('login'),
			$this->form->getValue('password'),
			(bool)$this->form->getValue('remember_me')
		);
		return true;
	}
}
