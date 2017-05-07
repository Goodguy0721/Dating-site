<?php

namespace Pg\Libraries\Acl\Driver;

use BeatSwitch\Lock\Callers\Caller;
use BeatSwitch\Lock\Drivers\Driver as IDriver;
use BeatSwitch\Lock\Permissions\Permission;
use BeatSwitch\Lock\Permissions\PermissionFactory;
use BeatSwitch\Lock\Roles\Role;

class ArrayDriver implements IDriver
{

    /**
     * @var \CodeIgniter
     */
    protected $ci;

    /**
     * The caller permissions cache at runtime
     *
     * @var array
     */
    protected $callerPermissions = [];

    /**
     * The role permissions cache at runtime
     *
     * @var array
     */
    protected $rolePermissions = [];
    private $permissionsRaw = [];

    /**
     * Class constructor
     */
    public function __construct(array $permissions)
    {
        $this->ci = &get_instance();
        $this->permissionsRaw = $permissions;
    }

    /**
     * Returns all the permissions for a caller
     *
     * @param \BeatSwitch\Lock\Callers\Caller $caller
     * @return \BeatSwitch\Lock\Permissions\Permission[]
     */
    public function getCallerPermissions(Caller $caller)
    {
        $key = $caller->getCallerType();
        if(array_key_exists($key, $this->callerPermissions)) {
            return $this->callerPermissions[$key];
        } elseif(!array_key_exists($key, get($this->permissionsRaw['callers']))) {
            return [];
        } else {
            $permissions = get($this->permissionsRaw['callers'][$key]);
            return $this->callerPermissions[$key] = PermissionFactory::createFromData($permissions);
        }
    }

    /**
     * Stores a new permission for a caller
     *
     * @param \BeatSwitch\Lock\Callers\Caller $caller
     * @param \BeatSwitch\Lock\Permissions\Permission
     * @return void
     */
    public function storeCallerPermission(Caller $caller, Permission $permission)
    {
        $this->permissionsRaw['callers'][$caller->getCallerType()][] = [
            'type' => $permission->getType(),
            'action' => $permission->getAction(),
            'resource_type' => $permission->getResourceType(),
            'resource_id' => $permission->getResourceId(),
        ];
        $this->resetPermissionsCacheForCaller($caller);
    }

    /**
     * Removes a permission for a caller
     *
     * @param \BeatSwitch\Lock\Callers\Caller $caller
     * @param \BeatSwitch\Lock\Permissions\Permission
     * @return void
     */
    public function removeCallerPermission(Caller $caller, Permission $permission)
    {
        $record_index = array_search([
            'type' => $permission->getType(),
            'action' => $permission->getAction(),
            'resource_type' => $permission->getResourceType(),
            'resource_id' => $permission->getResourceId(),
            ], $this->permissionsRaw['callers'][$caller->getCallerType()]
        );
        if (false !== $record_index) {
            unset($this->permissionsRaw['callers'][$caller->getCallerType()][$record_index]);
            $this->resetPermissionsCacheForCaller($caller);
        }
    }

    /**
     * Checks if a permission is stored for a caller
     *
     * @param \BeatSwitch\Lock\Callers\Caller $caller
     * @param \BeatSwitch\Lock\Permissions\Permission
     * @return bool
     */
    public function hasCallerPermission(Caller $caller, Permission $permission)
    {
        return isset($this->permissionsRaw['callers'][$caller->getCallerType()]) &&
                false !== array_search([
                'type' => $permission->getType(),
                'action' => $permission->getAction(),
                'resource_type' => $permission->getResourceType(),
                'resource_id' => $permission->getResourceId(),
                ], $this->permissionsRaw['callers'][$caller->getCallerType()]
        );
    }

    /**
     * Returns all the permissions for a role
     *
     * @param \BeatSwitch\Lock\Roles\Role $role
     * @return \BeatSwitch\Lock\Permissions\Permission[]
     */
    public function getRolePermissions(Role $role)
    {
        $key = $role->getRoleName();

        // If we've saved the caller permissions we don't need to fetch them again.
        if (array_key_exists($key, $this->rolePermissions)) {
            return $this->rolePermissions[$key];
        }

        $results = get($this->permissionsRaw['roles'][$key], []);

        return $this->rolePermissions[$key] = PermissionFactory::createFromData($results);
    }

    /**
     * Stores a new permission for a role
     *
     * @param \BeatSwitch\Lock\Roles\Role $role
     * @param \BeatSwitch\Lock\Permissions\Permission
     * @return void
     */
    public function storeRolePermission(Role $role, Permission $permission)
    {
        $this->permissionsRaw['roles'][$role->getRoleName()][] = [
            'type' => $permission->getType(),
            'action' => $permission->getAction(),
            'resource_type' => $permission->getResourceType(),
            'resource_id' => $permission->getResourceId(),
        ];
        $this->resetPermissionsCacheForRole($role);
    }

    /**
     * Removes a permission for a role
     *
     * @param \BeatSwitch\Lock\Roles\Role $role
     * @param \BeatSwitch\Lock\Permissions\Permission
     * @return void
     */
    public function removeRolePermission(Role $role, Permission $permission)
    {
        $record_index = array_search([
            'type' => $permission->getType(),
            'action' => $permission->getAction(),
            'resource_type' => $permission->getResourceType(),
            'resource_id' => $permission->getResourceId(),
            ], $this->permissionsRaw['roles'][$role->getRoleName()]
        );
        if (false !== $record_index) {
            unset($this->permissionsRaw['roles'][$role->getRoleName()][$record_index]);
            $this->resetPermissionsCacheForRole($role);
        }
    }

    /**
     * Checks if a permission is stored for a role
     *
     * @param \BeatSwitch\Lock\Roles\Role $role
     * @param \BeatSwitch\Lock\Permissions\Permission
     * @return bool
     */
    public function hasRolePermission(Role $role, Permission $permission)
    {
        return isset($this->permissionsRaw['roles'][$role->getRoleName()]) && 
                false !== array_search([
                'type' => $permission->getType(),
                'action' => $permission->getAction(),
                'resource_type' => $permission->getResourceType(),
                'resource_id' => $permission->getResourceId(),
                ], $this->permissionsRaw['roles'][$role->getRoleName()]
        );
    }

    /**
     * @param \BeatSwitch\Lock\Callers\Caller $caller
     */
    protected function resetPermissionsCacheForCaller(Caller $caller)
    {
        unset($this->callerPermissions[$caller->getCallerType()]);
    }

    /**
     * @param \BeatSwitch\Lock\Roles\Role $role
     */
    protected function resetPermissionsCacheForRole(Role $role)
    {
        unset($this->rolePermissions[$role->getRoleName()]);
    }

}
