<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Permissions;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Core\Auth\Models\RolesPermissionsModel;
use GameX\Core\BaseModel;
use \GameX\Models\Server;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Elements\PermissionAccess;
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
     * @var array
     */
	protected $list = [
	    'admin' => [
	        'all' => [],
            'servers' => []
        ]
    ];

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
        return $this->list;
    }

    /**
     * @throws \Exception
     */
	protected function createForm() {
        foreach ($this->getServers() as $serverId => $tmp) {
            $this->list['admin']['servers'][$serverId] = [];
        }
        
        /** @var PermissionsModel[] $permissions */
        $permissions = PermissionsModel::get();
        foreach ($permissions as $permission) {
            if ($permission->type === 'server') {
                foreach ($this->getServers() as $serverId => $tmp) {
                    $key = $this->getElementKey($permission, $serverId);
                    $this->list['admin']['servers'][$serverId][] = $key;
                    $value = $this->getAccessForPermission($permission, $serverId);
                    $title = $this->getTranslate('permissions', $permission->group . '_' . $permission->key);
                    $this->addToForm($key, $value, $title);
                }
            } elseif ($permission->type === null) {
                $key = $this->getElementKey($permission);
                $this->list['admin']['all'][] = $key;
                $value = $this->getAccessForPermission($permission);
                $title = $this->getTranslate('permissions', $permission->group . '_' . $permission->key);
                $this->addToForm($key, $value, $title);
            }
            
        }
	}

    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm() {
//        /** @var \Illuminate\Database\Connection$db */
//        $db = static::$container->get('db')->getConnection();
//        $db->beginTransaction();
//        try {
//            foreach ($this->getPermissions() as $permission) {
//                $access = 0;
//                foreach (self::ACCESS_LIST as $a => $v) {
//                    $val = $this->form->getValue($this->getElementKey($permission, $a));
//                    if ($val) {
//                        $access |= $a;
//                    }
//                }
//
//                if ($access === 0) {
//                    RolesPermissionsModel::where([
//                        'role_id' => $this->role->id,
//                        'permission_id' => $permission->id
//                    ])->delete();
//                } else {
//                    $model = RolesPermissionsModel::where([
//                        'role_id' => $this->role->id,
//                        'permission_id' => $permission->id
//                    ])->first();
//                    if (!$model) {
//                        $model = new RolesPermissionsModel();
//                        $model->fill([
//                            'role_id' => $this->role->id,
//                            'permission_id' => $permission->id
//                        ]);
//                    }
//                    $model->access = $access;
//                    $model->save();
//                }
//            }
//            $db->commit();
//            return true;
//        } catch (Exception $e) {
//            $db->rollBack();
//            return false;
//        }
        return true;
    }
    
    /**
     * @param string $key
     * @param int $value
     * @param string $title
     */
    protected function addToForm($key, $value, $title) {
        $this->form
            ->add(new PermissionAccess($key, $value, [
                'title' => $title,
            ]));
        
//        $this->form->getValidator()
//            ->set($key, false, [
//                new Boolean()
//            ]);
    }
    
    /**
     * @param PermissionsModel $permission
     * @param int|null $resource
     * @return string
     */
    protected function getElementKey(PermissionsModel $permission, $resource = null) {
        $key = $permission->group . '_' . $permission->key;
        
        if ($resource !== null) {
            $key .= '_' . $permission->type . '_' . $resource;
        }
        return $key;
    }
    
    protected $servers = null;
    
    public function getServers() {
        if ($this->servers === null) {
            $this->servers = [];
            /** @var Server $server */
            foreach (Server::get() as $server) {
                $this->servers[$server->id] = $server->name;
            }
        }
        
        return $this->servers;
    }
    
    /**
     * @var array|null
     */
    protected $rolePermissions = null;
    
    /**
     * @param PermissionsModel $permission
     * @param int|null $resource
     * @return int
     */
    protected function getAccessForPermission(PermissionsModel $permission, $resource) {
        if ($this->rolePermissions === null) {
            $this->rolePermissions = [
                'admin' => [
                    'all' => [],
                    'servers' => []
                ]
            ];
            
            /** @var RolesPermissionsModel $permission */
            foreach ($this->role->permissions()->with('permission')->get() as $permission) {
                $p = $permission->permission;
                if ($p->type === 'server') {
                    $this->rolePermissions['admin']['server'][$permission->resource][$p->key] = $permission->access;
                } elseif (!$p->type) {
                    $this->rolePermissions['admin']['all'][$p->key] = $permission->access;
                }
            }
        }
        
        if (!array_key_exists($permission->group, $this->rolePermissions)) {
            return 0;
        }
        
        $tmp = $this->rolePermissions[$permission->group];
        
        if ($permission->type === null) {
            return array_key_exists($permission->key, $tmp['all'])
                ? $tmp['all'][$permission->key]
                : 0;
        }
        
        if ($resource === null) {
            return 0;
        }
        
        if (!array_key_exists($permission->type, $tmp)) {
            return 0;
        }
        
        $tmp = $tmp[$permission->type];
        if (!array_key_exists($resource, $tmp)) {
            return 0;
        }
    
        $tmp = $tmp[$model->getKey()];
        return array_key_exists($permission->key, $tmp)
            ? $tmp[$permission->key]
            : 0;
    }
}
