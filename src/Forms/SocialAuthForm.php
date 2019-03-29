<?php
namespace GameX\Forms;

use GameX\Core\Auth\Helpers\AuthHelper;
use GameX\Core\Auth\Models\UserModel;
use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\SocialHelper;
use \GameX\Core\Auth\Models\UserSocialModel;
use \Hybridauth\User\Profile;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Validate\Rules\Email as EmailRule;
use \GameX\Core\Validate\Rules\Length;
use \GameX\Core\Validate\Rules\PasswordRepeat;
use \GameX\Core\Validate\Rules\Callback;

class SocialAuthForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'social_auth';

    /**
     * @var string
     */
	protected $provider;

    /**
     * @var SocialHelper
     */
    protected $profile;

	/**
	 * @var SocialHelper
	 */
	protected $socialHelper;

	/**
	 * @var AuthHelper
	 */
	protected $authHelper;

    /**
     * @var bool
     */
	protected $activate;

    /**
     * @var UserModel
     */
    protected $user;
    
    /**
     * @var UserSocialModel
     */
	protected $socialUser;

	/**
	 * @param string $provider
	 * @param Profile $profile
	 * @param SocialHelper $socialHelper
	 * @param AuthHelper $authHelper
	 * @param bool $activate
	 */
	public function __construct($provider, Profile $profile, SocialHelper $socialHelper, AuthHelper $authHelper, $activate = true)
    {
		$this->provider = $provider;
		$this->profile = $profile;
		$this->socialHelper = $socialHelper;
		$this->authHelper = $authHelper;
		$this->activate = $activate;
	}

    /**
     * @return UserModel
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * @return UserSocialModel
     */
	public function getSocialUser()
    {
	    return $this->socialUser;
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
	protected function createForm()
    {
		$this->form
			->add(new Text('login', $this->profile->displayName, [
				'title' => $this->getTranslate('inputs', 'login'),
				'required' => true,
			]))
			->add(new EmailElement('email', $this->profile->email, [
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
     * @throws \Exception
     */
	protected function processForm()
    {
        $this->user = $this->authHelper->registerUser(
            $this->form->getValue('login'),
            $this->form->getValue('email'),
            $this->form->getValue('password'),
            $this->activate
        );
        if (!$this->user) {
            return false;
        }

        $this->socialUser = $this->socialHelper->register($this->provider, $this->profile, $this->user);
        return (bool) $this->socialUser;
	}
}
