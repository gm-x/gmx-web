<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Validate\Rules\Callback;

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
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkExists($value, array $values) {
	    $this->user = $this->authHelper->findUser($value);
	    return $this->user ? $value : null;
    }
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkActivation($value, array $values) {
	    return $this->authHelper->checkActivationCompleted($this->user) ? $value : null;
    }

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new Text('login', '', [
				'title' => $this->getTranslate('inputs', 'login_email'),
				'required' => true,
			]));
		
		$this->form->getValidator()
			->set('login', true, [
                new Callback([$this, 'checkExists'], 'User not found'),
                new Callback([$this, 'checkActivation'], 'User is not activated')
            ]);
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
