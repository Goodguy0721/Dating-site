<?php

require_once LIBPATH . "Analytics/Segment.php";

class Analytics
{
    private $ci;
    private $write_key = 'sxADAtqAR8Ik7UmtcvxF7zkrKiMmzclI';
    
    public $segment_analytics;
    public $debug = false;

    public function __construct()
    {
        $this->ci = &get_instance();
        
        $options = array(
            'consumer' => "lib_curl",
            'batch_size' => 1,
            'integrations' => array(
                "All" => false,
                "Amplitude" => true
            ),
            'debug' => $this->debug,
            'error_handler' => function($code, $msg) {
                $this->debug($code, $msg);
            },
        );

        $this->segment_analytics = new Segment();
        $this->segment_analytics->init($this->write_key, $options);
    }
    
    public function track($message = null, $demo_mode_only = true)
    {       
        if(!is_array($message) && $message) {
            $message = array('event' => $message);
        }
        
        $this->setMessage($message);

        if($demo_mode_only) {
            if(DEMO_MODE) {
                $this->segment_analytics->track($message);
            }
        } else {
            $this->segment_analytics->track($message);
        }
        
        $this->segment_analytics->flush();
    }
    
    public function page($message = array())
    {
        $this->setMessage($message);
        $this->segment_analytics->page($message);
    }
    
    public function identify($message = array())
    {
        $this->setMessage($message);
        $this->segment_analytics->identify($message);
    }

    private function setMessage(array &$message) 
    {
        if(!isset($message['userId'])) {
            $message['userId'] = $this->getUserId();
        }
    }
    
    private function getUserId()
    {
        $user_type = $this->ci->session->userdata('auth_type');
        $user_row = $_SERVER['HTTP_HOST'] . '-';
        
        if(!$user_type) {
            $user_row .= 'guest';
        } else {
            if($user_type == 'user') {
                $user_row .= $this->ci->session->userdata('user_id');
            } else {
                $user_row .= $user_type;
            }
        }
        
        return $user_row; 
    }
    
    public function ajaxTrack($category, $gid)
    {
        if($category && $gid) {
            require_once LIBPATH . "Analytics/analytics_pages.php";
            
            if(isset($analytics_pages[$category][$gid])) {
                $this->track($analytics_pages[$category][$gid]);
            }
        }
    }
    
    public function debug($code, $msg) {
        print_r($code . ': ' . $msg);
    }
}

