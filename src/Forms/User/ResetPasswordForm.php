<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Exceptions\FormException;

class ResetPasswordForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'reset_password';

	/**
	 * @var AuthHelper
	 */
	protected $authHelper;

	/**
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
			->addRule('login', new Trim())
			->addRule('login', new Required());
	}

	/**
	 * @return array
	 * @throws FormException
	 */
	protected function processForm() {
		$user = $this->authHelper->findUser($this->form->getValue('login'));
		if (!$user) {
			throw new FormException('login', 'User not found');
		}
		$code = $this->authHelper->resetPassword($user);
		return [
			'user' => $user,
			'code' => $code,
		];
	}
}
