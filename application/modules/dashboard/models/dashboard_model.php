<?php

namespace Pg\Modules\Dashboard\Models;

use Pg\Libraries\Data\DataProvider;

define("DASHBOARD_TABLE", DB_PREFIX . "dashboard");

class Dashboard_model extends \Model
{
    /**
     * Module GUID
     *
     * @var string
     */
    const MODULE_GID = 'dashboard';
    
    /**
     * Module table
     *
     * @var string
     */
    const MODULE_TABLE = DASHBOARD_TABLE;
    
    public $events = [];
    
    protected $ci;
        
    /**
     * Dashboard object properties
     *
     * @var array
     */
    protected $fields = [
        self::MODULE_TABLE => [
            'id',
            'module',
            'type',
            'fk_object_id',
            'data',
            'status',
            'date_created',
            'date_modified',
        ],
    ];   
    
    private $provider = null;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->ci = &get_instance();
                
        $this->provider = DataProvider::getProvider();
    }
    
    private function getEventData($field_name, $field_value)
    {
        return $this->provider
                    ->setSource(self::MODULE_TABLE, $this->fields[self::MODULE_TABLE])
                    ->setCriteriaEqual($field_name, $field_value)
                    ->getObject();
    }
    
    public function getEventById($event_id)
    {
        return $this->getEventData('id', $event_id);
    }
    
    public function getEventsList() 
    {
        return $this->provider
                    ->setSource(self::MODULE_TABLE, $this->fields[self::MODULE_TABLE])
                    ->setOrderBy('id', 'DESC')
                    ->getList();
    }
    
    public function getEventByObject($module, $type, $object_id) 
    {
        return $this->provider
                    ->setSource(self::MODULE_TABLE, $this->fields[self::MODULE_TABLE])
                    ->setCriteriaEqual('module', $module)
                    ->setCriteriaEqual('type', $type)
                    ->setCriteriaEqual('fk_object_id', $object_id)
                    ->getObject();
    }
    
    public function getEventsByObjects($module, $type, $object_ids) 
    {
        return $this->provider
                    ->setSource(self::MODULE_TABLE, $this->fields[self::MODULE_TABLE])
                    ->setCriteriaEqual('module', $module)
                    ->setCriteriaEqual('type', $type)
                    ->setCriteriaEqual('fk_object_id', $object_ids)
                    ->getList();
    }
    
    public function formatEvents($data)
    {
        $modules = [];        
        foreach ($data as $key => $event) {
            if (!empty($event['data'])) {
               $event['data'] = (array)unserialize($event['data']); 
            } else {
                $event['data'] = [];
            }

            if (!isset($event['data']['dashboard_action_name'])) {
                $event['data']['dashboard_action_name'] = 'link_section_action';
            }        
            $event['data']['dashboard_status'] = $event['status'];
            
            $modules[$event['module']][$key] = $event['data'];
        }
        
        foreach ($modules as $module => $values) {
            if (!$this->ci->pg_module->is_module_active($module)) {
                continue;
            }
            
            $model = ucfirst($module . '_model');
            
            $this->ci->load->model($model);
            $values = $this->ci->{$model}->formatDashboardRecords($values);

            foreach ($values as $key => $value) {
                if(!isset($value['id'])) {
                    continue;
                }
                
                $data[$key]['data'] = $value;
            }

        }
        return $data;
    }   
    
    public function processEvent($event_data) 
    {
        $model = ucfirst($event_data['module'] . '_model');
        $this->ci->load->model($model);
        
        $ids = [];
        $statuses = [];
        
        $events = $this->getEventsByObjects($event_data['module'], $event_data['type'], (array)$event_data['id']);
        foreach ($events as $event) {
            if (!isset($ids[$event['fk_object_id']])) {
                $ids[$event['fk_object_id']] = $event['id'];
                $statuses[$event['fk_object_id']] = $event['status'];
            }
        }
        
        $data = $this->ci->{$model}->getDashboardData($event_data['id'], $event_data['status']);        
        if ($data !== false) {
            if (isset($data['dashboard_new_object'])) {
                $is_check_object_exists = !$data['dashboard_new_object'];
            } else {
                if(isset($statuses[$event_data['id']])) {
                    $is_check_object_exists = $event_data['status'] == $statuses[$event_data['id']];
                } else {
                    $is_check_object_exists = false;
                }
                
            }
            if (isset($ids[$event_data['id']]) && $is_check_object_exists) {
                $event_id = $ids[$event_data['id']];
            } else {
                $event_id = null;
            }
            
            $save_data = [            
                'data' => serialize($data),
                'status' => $event_data['status'],
                'date_modified' => date('Y-m-d H:i:s'),
            ];
                
            if (!$event_id) {
                $save_data['module'] = $event_data['module'];
                $save_data['type'] = $event_data['type'];
                $save_data['fk_object_id'] = $event_data['id'];
                $save_data['date_created'] = $save_data['date_modified'];
            }
        
            $this->provider->setSource(self::MODULE_TABLE, $this->fields[self::MODULE_TABLE])
                           ->save($event_id, $save_data);               
        } elseif (!empty($ids)) {
            $save_data = [
                'status' => $event_data['status'],
                'date_modified' => date('Y-m-d H:i:s'),
            ];
            $this->provider->setSource(self::MODULE_TABLE, $this->fields[self::MODULE_TABLE])
                           ->save($ids, $save_data);
        }
    }
    
    public function clear() 
    {
        $item_life_time = $this->ci->pg_module->get_module_config('dashboard', 'item_life_time');
        $item_count_limit = $this->ci->pg_module->get_module_config('dashboard', 'item_count_limit');

        $this->provider
             ->setSource(self::MODULE_TABLE, $this->fields[self::MODULE_TABLE])
             ->setCriteriaGreater('date_created', date('Y-m-d H:i:s', time() - $item_life_time * 86400))
             ->delete();
        
        $this->provider
             ->setSource(self::MODULE_TABLE, $this->fields[self::MODULE_TABLE])
             ->setLimit($item_count_limit)
             ->setOrderBy('id', 'DESC')
             ->delete();
    }
}

