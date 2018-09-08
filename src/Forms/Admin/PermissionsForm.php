<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Permissions;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Core\Auth\Models\RolesPermissionsModel;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\Boolean;
use Symfony\Component\Config\Definition\Exception\Exception;

class PermissionsForm extends BaseForm {
    
    const ACCESS_LIST = [
        Permissions::ACCESS_LIST => 'list',
        Permissions::ACCESS_VIEW => 'view',
        Permissions::ACCESS_CREATE => 'create',
        Permissions::ACCESS_EDIT => 'edit',
        Permissions::ACCESS_DELETE => 'delete',
    ];

	/**
	 * @var string
	 */
	protected $name = 'admin_role_permissions';

	/**
	 * @var Permissions
	 */
	protected $manager;
    
    /**
     * @var RoleModel
     */
	protected $role;
    
    /**
     * @var PermissionsModel[]
     */
	protected $permissionsList = null;

	/**
	 * @param RoleModel $role
	 * @param Permissions $permissions
	 */
	public function __construct(RoleModel $role, Permissions $permissions) {
	    $this->manager = $permissions;
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
     * @throws \Exception
     */
	protected function createForm() {
        foreach ($this->getPermissions() as $permission) {
            foreach (self::ACCESS_LIST as $access => $v)
            $this->addToForm($permission, $access);
        }
	}

    /**
     * @return bool
     * @throws \Exception
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
        if ($this->permissionsList === null) {
            /** @var PermissionsModel[] $permissions */
            $this->permissionsList = PermissionsModel::whereNull('type')
                ->get();
        }
        
        return $this->permissionsList;
    }

    /**
     * @param PermissionsModel $permission
     * @param int $access
     * @throws \Exception
     */
    protected function addToForm(PermissionsModel $permission, $access) {
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
