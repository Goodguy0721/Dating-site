<?php

namespace Pg\Libraries\Acl\Driver;

use BeatSwitch\Lock\Callers\Caller;
use BeatSwitch\Lock\Drivers\Driver as IDriver;
use BeatSwitch\Lock\Permissions\Permission;
use BeatSwitch\Lock\Permissions\PermissionFactory;
use BeatSwitch\Lock\Roles\Role;

class DbDriver implements IDriver
{

    /**
     * Tavle name
     * 
     * @var string
     */
    const PERMISSIONS_TABLE = 'acl';

    /**
     * @var \CodeIgniter
     */
    protected $ci;

    /**
     * Permission attributes
     * 
     * @var array
     */
    protected $fields = [
        self::PERMISSIONS_TABLE => [
            'id',
            'caller_type',
            'caller_id',
            'role',
            'type',
            'action',
            'resource_type',
            'resource_id',
        ],
    ];

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->ci = &get_instance();
    }

    /**
     * Returns all the permissions for a caller
     *
     * @param \BeatSwitch\Lock\Callers\Caller $caller
     * @return \BeatSwitch\Lock\Permissions\Permission
     */
    public function getCallerPermissions(Caller $caller)
    {
        $results = $this->ci->db
                ->select(['type', 'action', 'resource_type', 'resource_id',])
                ->from(DB_PREFIX . self::PERMISSIONS_TABLE)
                ->where('caller_type', $caller->getCallerType())
                ->where('caller_id', $caller->getCallerId())
                ->get()->result_array();
        return PermissionFactory::createFromData($results);
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
        $this->ci->db->insert(DB_PREFIX . self::PERMISSIONS_TABLE, [
            'caller_type' => $caller->getCallerType(),
            'caller_id' => (int) $caller->getCallerId(),
            'type' => $permission->getType(),
            'action' => $permission->getAction(),
            'resource_type' => $permission->getResourceType(),
            'resource_id' => (int) $permission->getResourceId(),
        ]);
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
        $this->ci->db
            ->where('caller_type', $caller->getCallerType())
            ->where('caller_id', $caller->getCallerId())
            ->where('type', $permission->getType())
            ->where('action', (int) $permission->getAction())
            ->where('resource_type', (int) $permission->getResourceType())
            ->where('resource_id', (int) $permission->getResourceId())
            ->delete(DB_PREFIX . self::PERMISSIONS_TABLE);
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
        return $this->ci->db
                ->select('1')
                ->from(DB_PREFIX . self::PERMISSIONS_TABLE)
                ->where('caller_type', $caller->getCallerType())
                ->where('caller_id', $caller->getCallerId())
                ->where('type', $permission->getType())
                ->where('action', $permission->getAction())
                ->where('resource_type', $permission->getResourceType())
                ->where('resource_id', $permission->getResourceId())
                ->get()
                ->result_array();
    }

    /**
     * Returns all the permissions for a role
     *
     * @param \BeatSwitch\Lock\Roles\Role $role
     * @return \BeatSwitch\Lock\Permissions\Permission
     */
    public function getRolePermissions(Role $role)
    {
        $results = $this->ci->db
                ->select(['type', 'action', 'resource_type', 'resource_id',])
                ->from(DB_PREFIX . self::PERMISSIONS_TABLE)
                ->where('role', $role->getRoleName())->get()->result_array();
        return PermissionFactory::createFromData($results);
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
        $this->ci->db->insert(DB_PREFIX . self::PERMISSIONS_TABLE, [
            'role' => $role->getRoleName(),
            'type' => $permission->getType(),
            'action' => $permission->getAction(),
            'resource_type' => $permission->getResourceType(),
            'resource_id' => (int) $permission->getResourceId(),
        ]);
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
        $query = $this->ci->db
            ->where('role', $role->getRoleName())
            ->where('type', $permission->getType())
            ->where('action', $permission->getAction());

        if (is_null($permission->getResourceType())) {
            $query->whereNull('resource_type');
        } else {
            $query->where('resource_type', $permission->getResourceType());
        }

        if (is_null($permission->getResourceId())) {
            $query->whereNull('resource_id');
        } else {
            $query->where('resource_id', $permission->getResourceId());
        }
        $query->delete();
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
        $this->ci->db
            ->select('1')
            ->from(DB_PREFIX . self::PERMISSIONS_TABLE)
            ->where('role', $role->getRoleName())
            ->where('type', $permission->getType())
            ->where('action', $permission->getAction());

        if (!is_null($permission->getResourceType())) {
            $this->ci->db->where('resource_type', $permission->getResourceType());
        }

        if (!is_null($permission->getResourceId())) {
            $this->ci->db->where('resource_id', $permission->getResourceId());
        }

        return (bool) $this->ci->db->get()->result_array();
    }

}
