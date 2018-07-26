<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\PasswordRepeat;
use \GameX\Core\Forms\Rules\Callback;
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
     * @var UserModel
     */
	protected $user;

	/**
	 * @param AuthHelper $authHelper
	 * @param string $code
	 */
	public function __construct(AuthHelper $authHelper, $code) {
		$this->authHelper = $authHelper;
		$this->code = $code;
	}
    
    /**
     * @return UserModel
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * @param Form $form
     * @return bool
     */
    public function checkExists(Form $form) {
        $this->user = $this->authHelper->findUser($form->getValue('login'));
        return (bool) $this->user;
    }
    
    /**
     * @return bool
     */
    public function checkCode() {
        return (bool) $this->authHelper->checkActivationExists($this->user, $this->code);
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
            ->addRule('login', new Callback([$this, 'checkExists'], 'User not found'))
            ->addRule('login', new Callback([$this, 'checkCode'], 'Bad code'))
			->addRule('password', new Trim())
			->addRule('password', new Required())
			->addRule('password_repeat', new Trim())
			->addRule('password_repeat', new PasswordRepeat('password'));
	}

	/**
	 * @return bool
	 * @throws FormException
	 */
	protected function processForm() {
		if (!$this->user) {
			return false;
		}
		$this->authHelper->resetPasswordComplete($this->user, $this->form->getValue('password'), $this->code);
		return true;
	}
}
