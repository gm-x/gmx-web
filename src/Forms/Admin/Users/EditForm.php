<?php

namespace GameX\Forms\Admin\Users;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Validate\Rules\Email as EmailRule;
use \GameX\Core\Validate\Rules\InArray;
use \GameX\Core\Validate\Rules\Callback;

class EditForm extends BaseForm
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
        $this->form
            ->add(new EmailElement('email', $this->user->email, [
                'title' => $this->getTranslate('admin_users', 'email'),
                'required' => true,
            ]))
            ->add(new Select('status', $this->user->status, [
                UserModel::STATUS_PENDING => 'Pending',
                UserModel::STATUS_ACTIVE => 'Active',
                UserModel::STATUS_BANNED => 'Banned',
            ], [
                'title' => $this->getTranslate('admin_users', 'status'),
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
            ])
            ->set('status', true, [
                new InArray([
                    UserModel::STATUS_PENDING,
                    UserModel::STATUS_ACTIVE,
                    UserModel::STATUS_BANNED,
                ])
            ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        $this->user->email = $this->form->getValue('email');
        $this->user->status = $this->form->getValue('status');
        return $this->user->save();
    }
}
