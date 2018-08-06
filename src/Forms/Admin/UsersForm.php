<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\RoleHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Rules\InArray;

class UsersForm extends BaseForm {

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
	public function __construct(UserModel $user, RoleHelper $roleHelper) {
		$this->user = $user;
		$this->roleHelper = $roleHelper;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
	    $roles = $this->roleHelper->getRolesAsArray();
		$this->form
            ->add(new Select('role', $this->user->role->slug, $roles, [
                'title' => 'Role',
                'error' => 'Required',
                'required' => true,
                'empty_option' => 'Choose role'
            ]));
        
        $this->form->getValidator()
            ->set('title', true, [
                new InArray(array_keys($roles))
            ]);
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        return $this->roleHelper->assignUser($this->form->getValue('role'), $this->user);
    }
}
