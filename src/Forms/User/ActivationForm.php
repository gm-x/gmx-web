<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Exceptions\FormException;

class ActivationForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'activation';

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
			->addRule('login', new Trim())
			->addRule('login', new Required());
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
		$this->authHelper->activateUser($user, $this->code);
		return $user;
	}
}
