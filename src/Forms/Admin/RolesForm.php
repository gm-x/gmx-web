<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Forms\Elements\Text;

class RolesForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_roles';

	/**
	 * @var RoleModel
	 */
	protected $role;

	/**
	 * @param RoleModel $role
	 */
	public function __construct(RoleModel $role) {
		$this->role = $role;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
            ->add(new Text('name', $this->role->name, [
				'title' => $this->getTranslate($this->name, 'name'),
                'required' => true,
			]));
		
		$this->form->getValidator()
            ->set('name', true);
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->role->fill($this->form->getValues());
        return $this->role->save();
    }
}
