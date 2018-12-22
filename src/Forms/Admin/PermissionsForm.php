<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Forms\Validator;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\PermissionsModel;
use \GameX\Core\Auth\Models\RolesPermissionsModel;
use \GameX\Models\Server;
use \GameX\Core\Forms\Elements\PermissionAccess as PermissionAccessElement;
use \GameX\Core\Forms\Rules\PermissionAccess as PermissionAccessRule;
use \Exception;

class PermissionsForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_role_permissions';
    
    /**
     * @var RoleModel
     */
	protected $role;

    /**
     * @var array|null
     */
    protected $rolePermissions = null;

    /**
     * @var PermissionsModel[]|null
     */
    protected $permissions = null;

    /**
     * @var Server[]|null
     */
    protected $servers = null;
    
    /**
     * @var array
     */
	protected $list = [
	    'admin' => [
	        'all' => [],
            'server' => []
        ]
    ];

	/**
	 * @param RoleModel $role
	 */
	public function __construct(RoleModel $role) {
		$this->role = $role;
	}
    
    /**
     * @return array
     */
	public function getList() {
        return $this->list;
    }

    /**
     * @throws Exception
     */
	protected function createForm() {
        foreach ($this->getServers() as $resource => $tmp) {
            $this->list['admin']['servers'][$resource] = [];
        }

        foreach ($this->getPermissions() as $permission) {
            if ($permission->type === null) {
                $this->addToForm($permission);
            } else {
                $resources = null;
                switch ($permission->type) {
                    case 'server': {
                        $resources = $this->getServers();
                    } break;
                }
                if ($resources !== null) {
                    foreach ($resources as $resource => $tmp) {
                        $this->addToForm($permission, $resource);
                    }
                }
            }
        }
	}

    /**
     * @return bool
     * @throws Exception
     */
    protected function processForm() {
        /** @var \Illuminate\Database\Connection$db */
        $db = static::$container->get('db')->getConnection();
        $db->beginTransaction();
        try {
            foreach ($this->getPermissions() as $permission) {
                if ($permission->type === 'server') {
                    foreach ($this->getServers() as $serverId => $tmp) {
                        $this->saveAccess($permission, $serverId);
                    }
                } elseif ($permission->type === null) {
                    $this->saveAccess($permission);
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
     * @param PermissionsModel $permission
     * @param int|null $resource
     * @throws Exception
     */
    protected function addToForm(PermissionsModel $permission, $resource = null) {
        $key = $this->getElementKey($permission, $resource);
        if ($resource === null) {
            $this->list['admin']['all'][] = $key;
        } else {
            $this->list['admin'][$permission->type][$resource][] = $key;
        }

        $value = $this->getAccessForPermission($permission, $resource);
        $title = $this->getTranslate('permissions', $permission->group . '_' . $permission->key);

        $this->form
            ->add(new PermissionAccessElement($key, $value, [
                'title' => $title,
            ]));

        $this->form->getValidator()
            ->set($key, false, [
                new PermissionAccessRule()
            ], [
                'check' => Validator::CHECK_ARRAY,
                'trim' => false,
                'default' => 0
            ]);
    }

    /**
     * @param PermissionsModel $permission
     * @param int|null $resource
     * @throws Exception
     */
    protected function saveAccess(PermissionsModel $permission, $resource = null) {
        $key = $this->getElementKey($permission, $resource);
        $access = $this->form->getValue($key);
        if ($access !== null && $access > 0) {
            $model = RolesPermissionsModel::where([
                'role_id' => $this->role->id,
                'permission_id' => $permission->id,
                'resource' => $resource,
            ])->first();
            if (!$model) {
                $model = new RolesPermissionsModel();
                $model->fill([
                    'role_id' => $this->role->id,
                    'permission_id' => $permission->id,
                    'resource' => $resource,
                ]);
            }
            $model->access = $access;
            $model->save();
        } else {
            RolesPermissionsModel::where([
                'role_id' => $this->role->id,
                'permission_id' => $permission->id,
                'resource' => $resource,
            ])->delete();
        }
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

    /**
     * @return PermissionsModel[]
     */
    protected function getPermissions() {
        if ($this->permissions === null) {
            $this->permissions = PermissionsModel::get();
        }
        return $this->permissions;
    }

    /**
     * @return Server[]
     */
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
     * @param PermissionsModel $permission
     * @param int|null $resource
     * @return int
     */
    protected function getAccessForPermission(PermissionsModel $permission, $resource = null) {
        if ($this->rolePermissions === null) {
            $this->rolePermissions = [
                'admin' => [
                    'all' => [],
                    'server' => []
                ]
            ];
            
            /** @var RolesPermissionsModel $permission */
            foreach ($this->role->permissions()->with('permission')->get() as $rolePermission) {
                $p = $rolePermission->permission;
                if ($p->type === 'server') {
                    $this->rolePermissions['admin']['server'][$rolePermission->resource][$p->key] = $rolePermission->access;
                } elseif (!$p->type) {
                    $this->rolePermissions['admin']['all'][$p->key] = $rolePermission->access;
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
    
        $tmp = $tmp[$resource];
        return array_key_exists($permission->key, $tmp)
            ? $tmp[$permission->key]
            : 0;
    }
}
