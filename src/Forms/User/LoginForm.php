<?php

namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Rules\Boolean;
use \Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use \Cartalyst\Sentinel\Checkpoints\ThrottlingException;

class LoginForm extends BaseForm
{
    
    /**
     * @var string
     */
    protected $name = 'login';
    
    /**
     * @var AuthHelper
     */
    protected $authHelper;
    
    /**
     * @var boolean
     */
    protected $mailEnabled;
    
    /**
     * @param AuthHelper $authHelper
     * @param bool $mailEnabled
     */
    public function __construct(AuthHelper $authHelper, $mailEnabled = false)
    {
        $this->authHelper = $authHelper;
        $this->mailEnabled = $mailEnabled;
    }
    
    /**
     * @inheritdoc
     */
    protected function createForm()
    {
        $this->form->add(new Text('login', '', [
                'title' => $this->getTranslate('inputs', 'login_email'),
                'required' => true,
                'icon' => 'user',
            ]))->add(new Password('password', '', [
                'title' => $this->getTranslate('inputs', 'password'),
                'required' => true,
                'icon' => 'lock',
            ]))->add(new Checkbox('remember_me', true, [
                'title' => $this->getTranslate('inputs', 'remember_me'),
                'required' => false,
            ]));
    
        $this->form->getValidator()
            ->set('login', true)
            ->set('password', true)
            ->set('remember_me', false, [
                new Boolean()
            ]);
    }
    
    /**
     * @return bool
     * @throws ValidationException
     */
    protected function processForm()
    {
        try {
            $user = $this->authHelper->loginUser(
                $this->form->getValue('login'),
                $this->form->getValue('password'),
                (bool)$this->form->getValue('remember_me')
            );
    
            if (!$user) {
                throw new ValidationException($this->getTranslate('user', 'bad_login_pass'));
            }
            
            return true;
        } catch (NotActivatedException $e) {
            $message = $this->mailEnabled
                ? $this->getTranslate('user', 'activate_email')
                : $this->getTranslate('user', 'activate');

            throw new ValidationException($message, null, $e);
        } catch (ThrottlingException $e) {
            throw new ValidationException('Too many unsuccessful login attempts have been made against your account', null, $e);
        }
    }
}
