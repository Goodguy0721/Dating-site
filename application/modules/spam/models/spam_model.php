<?php

namespace Pg\Modules\Spam\Models;

/**
 * Spam alert Model
 *
 * @package PG_RealEstate
 * @subpackage Spam
 *
 * @category	models
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Spam_model extends \Model
{
    const MODULE_GID = 'spam';
    
    const EVENT_ALERT_CHANGED = 'spam_alert_changed';
    
    const STATUS_ALERT_ADDED = 'alert_added';
    const STATUS_ALERT_SAVED = 'alert_saved';
    const STATUS_ALERT_DELETED = 'alert_deleted';
    const STATUS_CONTENT_DELETED = 'content_deleted';
    
    const TYPE_SPAM_ALERT = 'spam_alert';
    
    public $dashboard_events = [
        self::EVENT_ALERT_CHANGED,
    ];
        
    /**
     * link to CodeIgniter object
     *
     * @var object
     */
    protected $ci;
    
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }
    
    public function formatDashboardRecords($data) 
    {
        $this->ci->load->model('spam/models/Spam_alert_model');
                
        $this->ci->Spam_alert_model->set_format_settings([
            'get_type' => true, 'get_content' => true, 'get_reason' => true, 'get_link' => true, 'get_deletelink' => true]);
        $data = $this->ci->Spam_alert_model->format_alert($data);
        $this->ci->Spam_alert_model->set_format_settings([
            'get_type' => false, 'get_content' => false, 'get_reason' => false, 'get_link' => false, 'get_deletelink' => false]);
    
        foreach ($data as $key => $value) {
            $this->ci->view->assign('data', $value);                
            $data[$key]['content'] = $this->ci->view->fetch('dashboard', 'admin', 'spam');
        }
        
        return $data;
    }
    
    public function getDashboardData($spam_id, $status) 
    {
        if ($status != self::STATUS_ALERT_ADDED) {
            return false;
        }
        
        $this->ci->load->model('spam/models/Spam_alert_model');
        
        $data = $this->ci->Spam_alert_model->get_alert_by_id($spam_id, false);
        $data['dashboard_header'] = 'header_spam_alert';
        $data['dashboard_action_link'] = 'admin/spam';
        $data['report_count'] = $this->ci->Spam_alert_model->getObjectAlertsCount($data['id_type'], $data['id_object']);
        
        return $data;
    }
}
