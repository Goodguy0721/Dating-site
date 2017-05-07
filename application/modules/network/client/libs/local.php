<?php

use Pg\Modules\Network\Models\Network_model;

class Local
{
    private static $obj;
    private $ci;

    public function __construct()
    {
        $this->getCi();
        $this->ci->load->model('Network_model');
        $this->ci->load->model('network/models/Network_events_model');
        $this->ci->load->model('network/models/Network_actions_model');
    }

    public static function single()
    {
        if (empty(self::$obj)) {
            self::$obj = new self();
        }

        return self::$obj;
    }

    private function getCi()
    {
        $uri = 'start';
        $_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'] = $uri;
        $index = realpath(__DIR__ . '/../../../../../index.php');
        ob_start();
        include $index;
        ob_end_clean();
        $this->ci = &get_instance();
        if (empty($this->ci)) {
            throw new Exception('Can\'t init codeigniter');
        }
        $this->ci->db->initialize();
    }

    public function action($action, $params = array())
    {
        $result = call_user_func_array(array($this->ci->Network_actions_model, $action), $params ?: array());

        return $result;
    }

    public function get_events_dir()
    {
        return $this->ci->Network_events_model->get_events_dir();
    }

    public function get_logs_dir()
    {
        return $this->ci->Network_model->get_logs_dir();
    }

    public function get_events()
    {
        return $this->ci->Network_events_model->get_events();
    }

    public function get_handler($event)
    {
        return $this->ci->Network_events_model->get_cache($event);
    }

    public function handle($event, $data)
    {
        return $this->ci->Network_events_model->handle($event, $data);
    }

    public function get_connection_data()
    {
        $data = $this->ci->Network_events_model->get_connection_data();
        $url_arr = parse_url($data['url']);

        return array(
            'url' => $url_arr['scheme'] . '://' . $url_arr['host']
                . (isset($url_arr['port']) ? ':' . $url_arr['port'] : ''),
            'namespace' => rtrim($url_arr['path'], '/'),
            'key'       => $data['key'],
            'domain'    => $data['domain'],
        );
    }

    public function save_daemon_pid($pid)
    {
        $this->ci->Network_model->set_config(array('daemon_pid' => $pid));
    }

    public function is_debug()
    {
        return (bool) DISPLAY_ERRORS;
    }

    public function log($level, $message, $file = 'log')
    {
        log_message($level, $message, Network_model::MODULE_GID, $file);

        return $this;
    }
}
