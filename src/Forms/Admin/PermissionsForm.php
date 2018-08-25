<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Permissions\Manager;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\Boolean;

class PermissionsForm extends BaseForm {
    
    const ACCESS_LIST = [
        Manager::ACCESS_LIST => 'list',
        Manager::ACCESS_VIEW => 'view',
        Manager::ACCESS_CREATE => 'create',
        Manager::ACCESS_EDIT => 'edit',
        Manager::ACCESS_DELETE => 'delete',
    ];

	/**
	 * @var string
	 */
	protected $name = 'admin_role_permissions';

	/**
	 * @var Manager
	 */
	protected $manager;
    
    /**
     * @var RoleModel
     */
	protected $role;
    
    /**
     * @var PermissionsModel[]
     */
	protected $permissions = null;

	/**
	 * @param Manager $manager
	 * @param RoleModel $role
	 */
	public function __construct(Manager $manager, RoleModel $role) {
		$this->manager = $manager;
		$this->role = $role;
	}
    
    /**
     * @return array
     */
	public function getList() {
        $result = [];
        foreach ($this->getPermissions() as $permission) {
            $tmp = [];
            foreach (self::ACCESS_LIST as $access => $v) {
                $tmp[] = $this->getElementKey($permission, $access);
            }
            $result[] = [
                'title' => $this->getTranslate('permissions', $permission->group . '_' . $permission->key),
                'items' => $tmp
            ];
        }
        return $result;
    }

	/**
	 * @noreturn
	 */
	protected function createForm() {
        foreach ($this->getPermissions() as $permission) {
            foreach (self::ACCESS_LIST as $access => $v)
            $this->addToForm($permission, $access);
        }
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
//        $this->role->fill($this->form->getValues());
//
//        if (!$this->role->exists) {
//            $this->role->permissions = [];
//        }
//        return $this->role->save();
        return true;
    }
    
    /**
     * @return PermissionsModel[]
     */
    protected function getPermissions() {
        if ($this->permissions === null) {
            /** @var PermissionsModel[] $permissions */
            $this->permissions = PermissionsModel::whereNull('type')
                ->get();
        }
        
        return $this->permissions;
    }
    
    /**
     * @param PermissionsModel $permission
     * @param int $access
     */
    protected function addToForm($permission, $access) {
        $key = $this->getElementKey($permission, $access);
        $this->form
            ->add(new Checkbox($key, $this->hasAccessToPermission($permission, $access), [
                'title' => $this->getTranslate('permissions', self::ACCESS_LIST[$access]),
            ]));
        
        $this->form->getValidator()
            ->set($key, false, [
                new Boolean()
            ]);
    }
    
    /**
     * @param PermissionsModel $permission
     * @param int $access
     * @return bool
     */
    protected function hasAccessToPermission(PermissionsModel $permission, $access) {
        return $this->manager->hasAccessToPermission($this->role, $permission->group, $permission->key, $access);
    }
    
    /**
     * @param PermissionsModel $permission
     * @param int $access
     * @return string
     */
    protected function getElementKey(PermissionsModel $permission, $access) {
        return $permission->group . '_' . $permission->key . '_' . $access;
    }
}
