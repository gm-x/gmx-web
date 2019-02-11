<?php

namespace GameX\Forms\Admin\Users;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Rules\Email as EmailRule;
use \GameX\Core\Forms\Rules\Callback;

class EmailForm extends BaseForm
{

    /**
     * @var string
     */
    protected $name = 'admin_users_email';

    /**
     * @var UserModel
     */
    protected $user;

    /**
     * @param UserModel $user
     */
    public function __construct(UserModel $user)
    {
        $this->user = $user;
    }

    /**
     * @noreturn
     */
    protected function createForm()
    {
        $this->form->add(new EmailElement('email', $this->user->email, [
                'title' => $this->getTranslate('admin_users', 'email'),
                'required' => true,
            ]));
        
        $user = $this->user;
        $checkUnique = function ($value) use ($user) {
            return UserModel::where('id', '!=', $user->id)
                ->where('email', $value)
                ->count() > 0 ? null : $value;
        };
        
        $this->form->getValidator()
            ->set('email', true, [
                new EmailRule(),
                new Callback($checkUnique, 'Already exists')
            ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        $this->user->email = $this->form->getValue('email');
        return $this->user->save();
    }
}
