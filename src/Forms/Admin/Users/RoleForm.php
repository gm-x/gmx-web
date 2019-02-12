<?php

namespace GameX\Forms\Admin\Users;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Validate\Rules\InArray;

class RoleForm extends BaseForm
{

    /**
     * @var string
     */
    protected $name = 'admin_users_role';

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
     * @param RoleHelper $roleHelper
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
        $this->form->add(new Select('role', $this->user->role ? $this->user->role->id : '', $roles, [
                'title' => $this->getTranslate('admin_users', 'role'),
                'required' => true,
                'empty_option' => $this->getTranslate('admin_users', 'role_empty')
            ]));
        
        $this->form->getValidator()->set('role', true, [
                new InArray(array_keys($roles))
            ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        return $this->roleHelper->assignUser($this->form->getValue('role'), $this->user);
    }
}
