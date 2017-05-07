<?php

namespace Pg\Modules\Network\Models;

/**
 * Network events model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */

define('NET_EVENTS_HANDLERS_TABLE', DB_PREFIX . 'net_events_handlers');

/**
 * Network model
 */
class Network_events_model extends \Model
{

    private $cache = array();
    private $events_to_skip = array();
    private $events_dir;
    public $fields = array(
        'id',
        'event',
        'module',
        'model',
        'method',
    );

    public function __construct()
    {
        parent::__construct();
        $this->ci = get_instance();
        $this->ci->db->memcache_tables(array(NET_EVENTS_HANDLERS_TABLE));
        $this->ci->load->model('network/models/Network_users_model');
        $this->events_dir = TEMPPATH . Network_model::MODULE_GID . '/events/';
    }

    /**
     * Add event handler
     *
     * @param array $handler
     *
     * @return int
     */
    public function add_handler(array $handler)
    {
        $this->ci->db->insert(NET_EVENTS_HANDLERS_TABLE, $handler);

        return $this->ci->db->insert_id();
    }

    /**
     * Delete event handler
     *
     * @param int|string $val
     *
     * @throws Exception
     *
     * @return bool
     */
    public function delete($val)
    {
        if (is_int($val)) {
            $field = 'id';
        } elseif (is_string($val)) {
            $field = 'event';
        } else {
            throw new Exception('Wrong type');
        }

        return $this->ci->db->where($field, $val)
                ->delete(NET_EVENTS_HANDLERS_TABLE);
    }

    /**
     * Get event handlers
     *
     * @param array|string $event
     *
     * @return array
     */
    public function get($event = null)
    {
        $this->ci->db->select($this->fields)
            ->from(NET_EVENTS_HANDLERS_TABLE);
        if (!empty($event)) {
            if (is_array($event)) {
                $this->ci->db->where_in('event', $event);
            } else {
                $this->ci->db->where('event', $event);
            }
        }
        return $this->ci->db->get()->result_array();
    }

    /**
     * Get event handlers via cache
     *
     * @param type $event
     *
     * @return boolean
     */
    public function get_cache($event = null)
    {
        if (empty($this->cache['handlers'])) {
            $this->cache['handlers'] = $this->get();
        }
        if (empty($event)) {
            return $this->cache['handlers'];
        } elseif (!empty($this->cache['handlers'][$event])) {
            return $this->cache['handlers'][$event];
        } else {
            $handler = $this->get($event);
            if ($handler) {
                $this->cache['handlers'][$event] = $handler;
                return $handler;
            } else {
                return false;
            }
        }
    }

    /**
     * Get events list via cache
     *
     * @return array
     */
    public function get_events()
    {
        if (empty($this->cache['events'])) {
            $handlers = $this->get_cache();
            $this->cache['events'] = array();
            foreach ($handlers as $handler) {
                if (!in_array($handler['event'], $this->cache['events'])) {
                    $this->cache['events'][] = $handler['event'];
                }
            }
        }

        return array_unique($this->cache['events']);
    }

    /**
     * Get connection data
     *
     * @return array
     */
    public function get_connection_data()
    {
        $this->ci->load->model('Network_model');
        $config = $this->ci->Network_model->get_config();

        if (empty($config['fast_server']) || empty($config['key']) || empty($config['domain'])) {
            return array();
        }

        return array(
            'key'    => $config['key'],
            'domain' => $config['domain'],
            'url'    => $config['fast_server'],
        );
    }

    /**
     * Prohibit the processing of the next action.
     *
     * @param string $event
     */
    private function forbidEmiting($event)
    {
        $this->events_to_skip[$event] = true;

        return $this;
    }

    /**
     * Check whether the actions processing is allowed or not.
     * If not, allow it.
     *
     * @param string $event
     *
     * @return boolean
     */
    private function isEmitingAllowed($event)
    {
        if (!empty($this->events_to_skip[$event])) {
            $this->events_to_skip[$event] = false;
            return false;
        } else {
            return true;
        }
    }

    /**
     * Execut event handler
     *
     * @param array $handler
     * @param array $data
     *
     * @return mixed
     */
    private function exec($handler, $data)
    {
        $model = $handler['model'];
        if ($handler['module'] . "_model" == strtolower($handler['model'])) {
            $model_path = $handler['model'];
        } else {
            $model_path = $handler['module'] . '/models/' . $handler['model'];
        }
        $this->ci->load->model($model_path);
        if (method_exists($this->ci->{$model}, $handler['method'])) {
            $this->forbidEmiting($handler['event']);
            $this->ci->{$model}->{$handler['method']}($data);
        } else {
            echo date('H:i:s') . ': error ' . '$CI->{' . $model
            . '}->{' . $handler['method'] . '}(' . serialize($data) . ')' . PHP_EOL;
        }
    }

    /**
     * Prepare incoming data
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareIncomingData($data)
    {
        if (!empty($data['id_user'])) {
            // Replace net id with local id
            $data['id_user'] = $this->ci->Network_users_model->get_local_id_by_net($data['id_user']);
        }
        if (!empty($data['id_to'])) {
            // Replace net id with local id
            $data['id_to'] = $this->ci->Network_users_model->get_local_id_by_net($data['id_to']);
        }
        if (!empty($data['id_contact'])) {
            // Replace net id with local id
            $data['id_contact'] = $this->ci->Network_users_model->get_local_id_by_net($data['id_contact']);
        }

        return $data;
    }

    /**
     * Prepare outcoming data
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareOutcomingData($data)
    {
        if (!empty($data['id_user'])) {
            // Replace local id with net id
            $net_id = $this->ci->Network_users_model->get_net_id_by_local($data['id_user']);
            if (empty($net_id)) {
                return array();
            }
            $data['id_user'] = $net_id;
        }
        if (!empty($data['id_to'])) {
            // Replace local id with net id
            $net_id = $this->ci->Network_users_model->get_net_id_by_local($data['id_to']);
            if (empty($net_id)) {
                return array();
            }
            $data['id_to'] = $net_id;
        }
        if (!empty($data['id_contact'])) {
            // Replace local id with net id
            $net_id = $this->ci->Network_users_model->get_net_id_by_local($data['id_contact']);
            if (empty($net_id)) {
                return array();
            }
            $data['id_contact'] = $net_id;
        }

        return $data;
    }

    /**
     * Handle event
     *
     * @param string $event
     * @param array  $raw_data
     */
    public function handle($event, $raw_data)
    {
        echo date('H:i:s') . ': model handle: ' . $event . PHP_EOL;
        $data = $this->prepareIncomingData($raw_data);
        foreach ($this->get_cache($event) as $handler) {
            $this->exec($handler, $data);
        }
    }

    /**
     * Emit event
     *
     * @param string $event
     * @param array  $raw_data
     *
     * @return boolean
     */
    public function emit($event, $raw_data)
    {
        if (!$this->isEmitingAllowed($event)) {
            return false;
        }

        $prepared_data = $this->prepareOutcomingData($raw_data);
        if (empty($prepared_data)) {
            return false;
        }
        $event_data = array(
            'event' => $event,
            'data'  => $prepared_data,
        );

        $file = $this->get_events_dir() . $event . substr(time(), -5) . rand(11111, 99999);
        $fp = fopen($file, 'w');
        fwrite($fp, json_encode($event_data));
        fclose($fp);
        chmod($file, 0777);
        return true;
    }
    
    /**
     * Get events dir
     *
     * @return string
     */
    public function get_events_dir()
    {
        return $this->events_dir;
    }

}
