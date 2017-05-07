<?php

namespace Pg\Libraries\Acl\Driver\Decorator;

use Pg\Libraries\Acl\Driver\Decorator\Decorator as DriverDecorator;
use BeatSwitch\Lock\Callers\Caller;
use BeatSwitch\Lock\Drivers\Driver as IDriver;
use BeatSwitch\Lock\Permissions\Permission;
use BeatSwitch\Lock\Roles\Role;

/**
 * Caching decorator for a driver
 */
class Cached extends DriverDecorator
{

    protected $driver;

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

    public function __construct(IDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * 
     * @param Caller $caller
     * @return \BeatSwitch\Lock\Permissions\Permission
     */
    public function getCallerPermissions(Caller $caller)
    {
        $key = $this->getCallerKey($caller);

        // If we've saved the caller permissions we don't need to fetch them again.
        if (!array_key_exists($key, $this->callerPermissions)) {
            $this->callerPermissions[$key] = $this->driver->getCallerPermissions($caller);
        }

        return $this->callerPermissions[$key];
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
        $this->driver->storeCallerPermission($caller, $permission);
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
        $this->driver->removeCallerPermission($caller, $permission);
        $this->resetPermissionsCacheForCaller($caller);
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
        return $this->driver->hasCallerPermission($caller, $permission);
    }

    /**
     * Returns all the permissions for a role
     *
     * @param \BeatSwitch\Lock\Roles\Role $role
     * @return \BeatSwitch\Lock\Permissions\Permission
     */
    public function getRolePermissions(Role $role)
    {
        $key = $this->getRoleKey($role);

        // If we've saved the caller permissions we don't need to fetch them again.
        if (!array_key_exists($key, $this->rolePermissions)) {
            $this->rolePermissions[$key] = $this->driver->getRolePermissions($role);
        }

        return $this->rolePermissions[$key];
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
        $this->driver->storeRolePermission($role, $permission);
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
        $this->driver->removeRolePermission($role, $permission);
        $this->resetPermissionsCacheForRole($role);
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
        $this->driver->hasRolePermission($role, $permission);
    }

    /**
     * Creates a key to store the caller's permissions
     *
     * @param \BeatSwitch\Lock\Callers\Caller $caller
     * @return string
     */
    private function getCallerKey(Caller $caller)
    {
        return 'caller_' . $caller->getCallerType() . '_' . $caller->getCallerId();
    }

    /**
     * Creates a key to store the role's permissions
     *
     * @param \BeatSwitch\Lock\Roles\Role $role
     * @return string
     */
    private function getRoleKey(Role $role)
    {
        return 'role_' . $role->getRoleName();
    }

    /**
     * @param \BeatSwitch\Lock\Callers\Caller $caller
     */
    protected function resetPermissionsCacheForCaller(Caller $caller)
    {
        unset($this->callerPermissions[$this->getCallerKey($caller)]);
    }

    /**
     * @param \BeatSwitch\Lock\Roles\Role $role
     */
    protected function resetPermissionsCacheForRole(Role $role)
    {
        unset($this->rolePermissions[$this->getRoleKey($role)]);
    }

}
