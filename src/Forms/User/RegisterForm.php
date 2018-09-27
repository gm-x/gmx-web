<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Rules\Email as EmailRule;
use \GameX\Core\Forms\Rules\Length;
use \GameX\Core\Forms\Rules\PasswordRepeat;
use \GameX\Core\Forms\Rules\Callback;

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
	protected $activate;
    
    /**
     * @var UserModel
     */
	protected $user;

	/**
	 * @param AuthHelper $authHelper
	 * @param boolean $activate
	 */
	public function __construct(AuthHelper $authHelper, $activate) {
		$this->authHelper = $authHelper;
		$this->activate = $activate;
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
        return !$this->authHelper->exists($values['login'], $values['email']) ? $value : null;
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
			]));
		
		$this->form->getValidator()
			->set('login', true)
			->set('email', true, [
                new EmailRule(),
                new Callback([$this, 'checkExists'], 'User already exists')
            ])
            ->set('password', true, [
                new Length(AuthHelper::MIN_PASSWORD_LENGTH)
            ])
            ->set('password_repeat', true, [
                new PasswordRepeat('password')
            ]);
	}

	/**
	 * @return bool
	 */
	protected function processForm() {

		$this->user = $this->authHelper->registerUser(
			$this->form->getValue('login'),
			$this->form->getValue('email'),
			$this->form->getValue('password'),
			$this->activate
		);

		return (bool) $this->user;
	}
}
