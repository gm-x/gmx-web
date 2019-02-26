<?php

namespace GameX\Forms\Admin\Users;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\RoleHelper;
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
    protected $name = 'admin_users_edit';

    /**
     * @var UserModel
     */
    protected $user;

    /**
     * @var RoleHelper
     */
    protected $roleHelper;

    /**
     * @param UserModel $user
     */
    public function __construct(UserModel $user, RoleHelper $roleHelper)
    {
        $this->user = $user;
        $this->roleHelper = $roleHelper;
    }

    /**
     * @noreturn
     */
    protected function createForm()
    {
        $roles = $this->roleHelper->getRolesAsArray();

        $this->form
            ->add(new EmailElement('email', $this->user->email, [
                'title' => $this->getTranslate('admin_users', 'email'),
                'required' => true,
            ]))
            ->add(new Select('role', $this->user->role ? $this->user->role->id : '', $roles, [
                'title' => $this->getTranslate('admin_users', 'role'),
                'required' => false,
                'empty_option' => $this->getTranslate('admin_users', 'role_empty')
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
            ->set('role', false, [
                new InArray(array_keys($roles))
            ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        $this->user->email = $this->form->getValue('email');
        if ($this->user->role !== $this->form->getValue('role')) {
            $this->roleHelper->assignUser($this->form->getValue('role'), $this->user);
        }
        return $this->user->save();
    }
}
