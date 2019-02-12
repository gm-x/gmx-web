<?php

namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Validate\Rules\Boolean;

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
     * @param AuthHelper $authHelper
     */
    public function __construct(AuthHelper $authHelper)
    {
        $this->authHelper = $authHelper;
    }
    
    /**
     * @inheritdoc
     */
    protected function makeForm(Form $form)
    {
        $form->add(new Text('login', '', [
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
    }
    
    /**
     * @inheritdoc
     */
    protected function makeValidator(Validator $validator)
    {
        $validator->set('login', true)->set('password', true)->set('remember_me', false, [
                new Boolean()
            ]);
    }
    
    /**
     * @return bool
     * @throws \GameX\Core\Exceptions\ValidationException
     */
    protected function processForm()
    {
        $this->authHelper->loginUser($this->form->getValue('login'), $this->form->getValue('password'),
            (bool)$this->form->getValue('remember_me'));
        return true;
    }
}
