<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \Cartalyst\Sentinel\Roles\RoleInterface;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Callback;

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
	 * @param RoleInterface $role
	 */
	public function __construct(RoleInterface $role) {
		$this->role = $role;
	}
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkExists($value, array $values) {
        return !RoleModel::where('slug', $value)->exists() ? $value : null;
    }

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
            ->add(new Text('name', $this->role->name, [
                'title' => 'Name',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ]))
            ->add(new Text('slug', $this->role->slug, [
                'title' => 'Slug',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ]))
            ->addRule('name', new Trim())
            ->addRule('name', new Required())
            ->addRule('slug', new Trim())
            ->addRule('slug', new Required());
        
        if (!$this->role->exists) {
            $this->form->addRule('slug', new Callback([$this, 'checkExists'], 'Role already exists'));
        }
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->role->fill($this->form->getValues());
        
        if (!$this->role->exists) {
            $this->role->permissions = [];
        }
        return $this->role->save();
    }
}
