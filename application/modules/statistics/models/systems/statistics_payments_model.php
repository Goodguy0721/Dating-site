<?php

/**
 * Statistics module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
namespace Pg\Modules\Statistics\Models\systems;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Statistics main model
 *
 * @package 	PG_Dating
 * @subpackage 	Statistics
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Statistics_payments_model extends \Model
{
    /**
     * Module GUID
     *
     * @var string
     */
    const MODULE_GID = 'statistics';

    /**
     * Module table
     *
     * @var string
     */
    const MODULE_TABLE = 'statistics_payments';

    /**
     * System GUID
     *
     * @var string
     */
    const SYSTEM_GID = 'payments';

    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $ci;

    /**
     * Statistics object properties
     *
     * @var array
     */
    protected $fields = array(
        self::MODULE_TABLE => array(
            'object_id',
            'amount_count',
            'amount_total',
        ),
    );

    protected $events = array(
        'payment_send',
    );

    /**
     * Settings for formatting statistics object
     *
     * @var array
     */
    protected $format_settings = array(

    );

    /**
     * Class constructor
     *
     * @return Statistics_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->ci->db->memcache_tables(array(DB_PREFIX . self::MODULE_TABLE));
    }

    public function parseFile($file)
    {
        $fp = fopen($file, "r");
        if ($fp) {
            while (($buffer = fgets($fp, 4096)) !== false) {
                $stat_point = json_decode($buffer);
                $method = "processEvent_" . $stat_point->gid;
                $this->{$method}($stat_point->params);
            }
            if (!feof($fp)) {
                $this->view->output("Error: unexpected fgets() fail\n");

                $this->view->render();
            }
            fclose($fp);
        }
        
        $this->ci->db
            ->set('amount_day_14', 'amount_day_13', false)
            ->set('amount_day_13', 'amount_day_12', false)
            ->set('amount_day_12', 'amount_day_11', false)
            ->set('amount_day_11', 'amount_day_10', false)
            ->set('amount_day_10', 'amount_day_9', false)
            ->set('amount_day_9', 'amount_day_8', false)
            ->set('amount_day_8', 'amount_day_7', false)
            ->set('amount_day_7', 'amount_day_6', false)
            ->set('amount_day_6', 'amount_day_5', false)
            ->set('amount_day_5', 'amount_day_4', false)
            ->set('amount_day_4', 'amount_day_3', false)
            ->set('amount_day_3', 'amount_day_2', false)
            ->set('amount_day_2', 'amount_day_1', false)
            ->set('amount_day_1', 'amount_count', false)
            ->set('amount_total', 'amount_total + amount_count', false)
            ->set('amount_count', 0)
            ->set('transactions_day_14', 'transactions_day_13', false)
            ->set('transactions_day_13', 'transactions_day_12', false)
            ->set('transactions_day_12', 'transactions_day_11', false)
            ->set('transactions_day_11', 'transactions_day_10', false)
            ->set('transactions_day_10', 'transactions_day_9', false)
            ->set('transactions_day_9', 'transactions_day_8', false)
            ->set('transactions_day_8', 'transactions_day_7', false)
            ->set('transactions_day_7', 'transactions_day_6', false)
            ->set('transactions_day_6', 'transactions_day_5', false)
            ->set('transactions_day_5', 'transactions_day_4', false)
            ->set('transactions_day_4', 'transactions_day_3', false)
            ->set('transactions_day_3', 'transactions_day_2', false)
            ->set('transactions_day_2', 'transactions_day_1', false)
            ->set('transactions_day_1', 'transactions_count', false)
            ->set('transactions_total', 'transactions_total + transactions_count', false)
            ->set('transactions_count', 0)
            ->update(DB_PREFIX . self::MODULE_TABLE);            
            
    }

    private function processEvent_payment_send($params)
    {
        $this->createStatPoint(null, [
            'amount_count' => ['action' => 'sum', 'value' => $params->amount], 
            'transactions_count' => ['action' => 'inc']
        ]);
    }

    public function createStatPoint($object_id, $stat_point)
    {
        $stat_point['object_id']['action'] = 'set';
        
        if ($object_id) {
            $stat_point['object_id']['value'] = $object_id;
        } else {
            $stat_point['object_id']['value'] = 0;
        }
        
        return $this->_setStatPoint($stat_point);
    }
    
    public function getStatPoints($object_id, $stat_point_gids)
    {      
        $results = 
            $this->ci->db->select(implode(',', $stat_point_gids))
                         ->from(DB_PREFIX . self::MODULE_TABLE)
                         ->where('object_id', $object_id ? $object_id : 0)
                         ->get()
                         ->result_array();
                 
        if (!empty($results) && is_array($results)) {
            return $results[0];
        }

        return false;
    }

    public function validateStatPoint($object_id, $stat_point)
    {
        return $stat_point;
    }

    private function _setStatPoint($data)
    {
        $update_str = '';
        $fields_upd = array();

        foreach ($data as $field => $val) {
            switch ($val['action']) {
                case 'sum': 
                    $data[$field] = $val['value'];
                    $fields_upd[] = "`{$field}` = `" . $field . "` + " . $val['value'];
                break;
                case 'inc':
                    $data[$field] = 1;
                    $fields_upd[] = "`{$field}` = `" . $field . "` + 1";
                break;
                default:
                    $data[$field] = $val['value'];
                    $fields_upd[] = "`{$field}` = " . $this->ci->db->escape($val['value']) . "";
                break;
            }
        }
        $update_str = implode(', ', $fields_upd);
        $sql = $this->ci->db->insert_string(DB_PREFIX . self::MODULE_TABLE, $data) . " ON DUPLICATE KEY UPDATE {$update_str}";

        $this->ci->db->query($sql);

        return $this->ci->db->affected_rows();
    }

    public function get_system_data()
    {
        foreach ($this->fields[self::MODULE_TABLE] as $key => $value) {
            if ($value == 'object_id') {
                continue;
            }
            $data[$key]['field_name'] = l('stats_field_name_' . $value, 'statistics');
            $data[$key]['field_description'] = l('stats_field_description_' . $value, 'statistics');
        }

        return $data;
    }
    public function get_events_list($format = false)
    {
        if ($format == false) {
            return $this->events;
        } else {
            foreach ($this->events as $key => $value) {
                $data[$key]['field_name'] = l('event_' . $value . '_name', 'statistics');
                $data[$key]['field_description'] = l('event_' . $value . '_description', 'statistics');
            }

            return $data;
        }
    }

    public function install_system()
    {
        $file = MODULEPATH . 'statistics/models/systems_tables/structure_install_' . self::SYSTEM_GID . '.sql';
        if (file_exists($file)) {
            $this->ci->load->model('install/models/Install_model');
            $return = $this->ci->Install_model->simple_execute_sql($file);
            if (empty($return)) {
                $system_data = [
                    'module' => self::SYSTEM_GID,
                    'model' => 'Statistics_payments_model',
                    'cb_create' => 'test',
                    'cb_drop' => 'test',
                    'cb_process' => 'test',
                    'stat_points' => [],
                    'scheduler' => date('Y-m-d H:i:s'),
                    'status' => 1,
                ];    
                    
                foreach ($this->events as $event) {
                    $system_data['stat_points'][] = ['gid' => $event, 'status' => 1];
                }
                    
                $system_data['stat_points'] = serialize($system_data['stat_points']);
                                
                $this->ci->load->model('Statistics_model');
                $this->ci->Statistics_model->saveSystem(null, $system_data);
                    
                return true;
            }
        }

        return false;
    }

    public function uninstall_system()
    {
        $file = MODULEPATH . 'statistics/models/systems_tables/structure_deinstall_' . self::SYSTEM_GID . '.sql';
        if (file_exists($file)) {
            $this->ci->load->model('install/models/Install_model');
            $return = $this->ci->Install_model->simple_execute_sql($file);
            if (empty($return)) {
                return true;
            }
        }

        return false;
    }

    public function reset_system_statistics()
    {
        if ($this->ci->db->truncate(DB_PREFIX . self::MODULE_TABLE)) {
            return true;
        }

        return false;
    }
}
