<?php

namespace Pg\modules\access_permissions\models\access;

use BeatSwitch\Lock\Permissions\Restriction;
use BeatSwitch\Lock\Permissions\Privilege;

/**
 * Access_permissions module
 *
 * @copyright   Copyright (c) 2000-2016
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */


abstract class AccessAbstract extends \Model
{

    /**
     * Full access
     *
     * @var string
     */
    const PRIVILEGE = Privilege::TYPE;

    /**
     * Access available
     *
     * @var string
     */
    const RESTRICTION = Restriction::TYPE;

    /**
     * Partial access
     *
     * @var string
     */
    const INCOMPLETE = 'incomplete';

    /**
     * Access types adat
     *
     * @var array
     */
    public $access_type = [
        self::PRIVILEGE => 'full',
        self::RESTRICTION =>'empty'
    ];

    /**
     * Permissions status
     *
     * @var string
     */
    public $permissions_status = [];

   /**
     * Permission attributes
     *
     * @var array
     */
    protected $fields = [
        'id',
        'caller_type',
        'caller_id',
        'role',
        'type',
        'action',
        'resource_type',
        'resource_id',
        'data',
    ];

    protected $ci;

    /**
     * Class constructor
     *
     * @return AccessAbstract
     */
    public function __construct()
    {
        parent::__construct();

        $this->ci = &get_instance();
    }

    /**
     * Get Access Role
     *
     * @param string $gid
     *
     * @return string
     */
    abstract public function getRole($gid);

    /**
     * Get Field Periods Table
     *
     * @param string $gid
     *
     * @return void
     */
    abstract public function getField($gid);

    /**
     * Permissions list
     *
     * @param integer $id
     *
     * @return array
     */
    abstract public function permissionsList($module, $format);

    /**
     * Validate permissions list
     *
     * @param array $data
     *
     * @return array
     */
    abstract public function validatePermissions(array $data, array $module);

    /**
     * Format permissions list
     *
     * @param array $data
     * @param array $module
     *
     * @return array
     */
    abstract public function formatPermissions(array $data, array $module);

    /**
     * Save permissions list
     *
     * @param array $attrs
     *
     * @return integer
     */
    abstract public function savePermissions(array $attrs);

    /**
     * Return Access object
     *
     * @param array $fields
     * @param array  $where
     *
     * @return boolean/array
     */
    protected function getAccessObject($params)
    {     
         $this->ci->db->select(implode(', ', $this->fields));
         $this->ci->db->from(PERMISSIONS_TABLE);
         if (isset($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }
         if (isset($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }
        if (isset($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->ci->db->where($value, null, false);
            }
        }
        return $this->ci->db->get()->result_array();
    }

}
