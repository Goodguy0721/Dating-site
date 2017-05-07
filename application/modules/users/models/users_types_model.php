<?php

namespace Pg\Modules\Users\Models;

/**
 * User types model
 *
 * @package 	PG_Dating
 *
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
define("USERS_TYPES_TABLE", DB_PREFIX . "users_types");

/**
 * Users types main model
 *
 * @package 	PG_Dating
 * @subpackage 	Users
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2016 PG Dating Pro
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Users_types_model extends \Model
{

    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $ci;
    protected $fields = [
        'id',
        'name',
        'parent_id',
        'date_created',
    ];
    private $cache = [];

    public function __construct()
    {
        parent::__construct();
        $this->ci = get_instance();
        $this->ci->db->memcache_tables([USERS_TYPES_TABLE]);
    }

    public function addTypes(array $user_types)
    {
        foreach ($user_types as $user_type) {
            $this->ci->db->ignore()->insert(USERS_TYPES_TABLE, $user_type);
        }
        // TODO: update `user_type` field in USERS_TABLE
        return true;
    }

    protected function getType($field, $value)
    {
        if (empty($this->cache[$field][$value])) {
            $this->cache[$field][$value] = $this->ci->db->select(implode(', ', $this->fields))
                    ->from(USERS_TYPES_TABLE)
                    ->where($field, $value)
                    ->get()->result_array();
        }
        return $this->cache[$field][$value];
    }

    private function alterUsersTable()
    {
        // TODO: add new enum value in users table
        /*$this->ci->load->dbforge();

        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 3,
                'null' => false,
                'auto_increment' => true,
            ),
            'id_link_1' => array(
                'type' => 'INT',
                'constraint' => 3,
                'null' => false,
            ),
            'id_link_2' => array(
                'type' => 'INT',
                'constraint' => 3,
                'null' => false,
            ),
            'date_add' => array(
                'type' => 'DATETIME',
            ),
            'sorter' => array(
                'type' => 'INT',
                'constraint' => 3,
                'null' => false,
            ),
        );
        $this->ci->dbforge->add_field($fields);
        $this->ci->dbforge->add_key('id', true);
        $this->ci->dbforge->add_key(array('id_link_1', 'id_link_2'));
        $this->ci->dbforge->add_key('sorter');
        $this->ci->dbforge->create_table($table_name);*/
    }

    public function getTypes()
    {
        return $this->ci->db->select(implode(', ', $this->fields))
                ->from(USERS_TYPES_TABLE)
                ->get()->result_array();
    }

    public function getTypeById($id)
    {
        if (!is_int($id)) {
            throw new \BadMethodCallException('Invalid param');
        }
        return $this->getType('id', $id)[0];
    }

    public function getTypeByName($name)
    {
        return $this->getType('name', $name)[0];
    }

    public function getTypeWithChildren($id)
    {
        if (!is_int($id)) {
            throw new \BadMethodCallException('Invalid param');
        }
        $table = USERS_TYPES_TABLE;
        $fields = 'T2.' . implode(',T2.', $this->fields);
        $query = "SELECT $fields
                FROM (
                    SELECT
                        @r AS _id,
                        (SELECT @r := parent_id FROM $table WHERE id = _id) AS parent_id,
                        @l := @l + 1 AS lvl
                    FROM
                        (SELECT @r := $id, @l := 0) vars,
                        $table t
                    WHERE @r <> 0) T1
                JOIN $table T2
                ON T1._id = T2.id
                ORDER BY T1.lvl DESC;";
        return $this->ci->db->query($query)->result_array();
    }

    public function getAncestors($id)
    {
        //TBD
    }

}
