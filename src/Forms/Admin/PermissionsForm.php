<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Permissions\Manager;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Core\Auth\Models\RolesPermissionsModel;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\Boolean;
use Symfony\Component\Config\Definition\Exception\Exception;

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
	 * @param RoleModel $role
	 */
	public function __construct(RoleModel $role) {
	    $this->manager = static::$container->get('permissions');
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
        /** @var \Illuminate\Database\Connection$db */
        $db = static::$container->get('db')->getConnection();
        $db->beginTransaction();
        try {
            foreach ($this->getPermissions() as $permission) {
                $access = 0;
                foreach (self::ACCESS_LIST as $a => $v) {
                    $val = $this->form->getValue($this->getElementKey($permission, $a));
                    if ($val) {
                        $access |= $a;
                    }
                }
        
                if ($access === 0) {
                    RolesPermissionsModel::where([
                        'role_id' => $this->role->id,
                        'permission_id' => $permission->id
                    ])->delete();
                } else {
                    $model = RolesPermissionsModel::where([
                        'role_id' => $this->role->id,
                        'permission_id' => $permission->id
                    ])->first();
                    if (!$model) {
                        $model = new RolesPermissionsModel();
                        $model->fill([
                            'role_id' => $this->role->id,
                            'permission_id' => $permission->id
                        ]);
                    }
                    $model->access = $access;
                    $model->save();
                }
            }
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
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
