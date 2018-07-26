<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Callback;

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
     * @var UserModel
     */
	protected $user;
    
    /**
     * @var string
     */
	protected $code;

	/**
	 * @param AuthHelper $authHelper
	 */
	public function __construct(AuthHelper $authHelper) {
		$this->authHelper = $authHelper;
	}
    
    /**
     * @return UserModel
     */
	public function getUser() {
	    return $this->user;
    }
    
    /**
     * @return string
     */
    public function getCode() {
	    return $this->code;
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
    public function checkActivation() {
	    return $this->authHelper->checkActivationCompleted($this->user);
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
			->addRule('login', new Required())
            ->addRule('login', new Callback([$this, 'checkExists'], 'User not found'))
            ->addRule('login', new Callback([$this, 'checkActivation'], 'User is not activated'));
	}

	/**
	 * @return bool
	 */
	protected function processForm() {
	    if (!$this->user) {
	        return false;
        }
		$this->code = $this->authHelper->resetPassword($this->user);
		return true;
	}
}
