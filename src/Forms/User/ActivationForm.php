<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Rules\Callback;

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
     * @var UserModel
     */
	protected $user;

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
     * @return UserModel
     */
    public function getUser() {
        return $this->user;
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
    public function checkCode($value, array $values) {
        return $this->authHelper->checkActivationExists($this->user, $this->code) ? $value : null;
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
                new Callback([$this, 'checkCode'], 'Bad code')
            ]);
	}

	/**
	 * @return bool
	 */
	protected function processForm() {
		$this->authHelper->activateUser($this->user, $this->code);
		return false;
	}
}
