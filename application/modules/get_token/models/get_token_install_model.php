<?php

namespace Pg\Modules\Get_token\Models;

/**
 * Get token install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Mikhail Chernov <mchernov@pilotgroup.net>
 * */
class Get_token_install_model extends \Model
{
    protected $action_config = array(
        'get_token_mobile_auth' => array(
            'is_percent' => 0,
            'once' => 1,
            'available_period' => array(
                'once'),
            ),
    );
    public $CI;

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
    }

    public function _arbitrary_installing()
    {
        if ((extension_loaded('xmlwriter'))) {
            $this->CI->pg_module->set_module_config('get_token', 'use_xml', true);
        }
    }

    public function _arbitrary_deinstalling()
    {
    }

    public function install_bonuses()
    {

    }

    public function install_bonuses_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->model("bonuses/models/Bonuses_util_model");
        $langs_file = $this->CI->Install_model->language_file_read("bonuses", "ds", $langs_ids);

        if (!$langs_file) {
            log_message("info", "Empty bonuses langs data");
            return false;
        }
        $this->CI->Bonuses_util_model->update_langs($langs_file);

        $this->CI->load->model("bonuses/models/Bonuses_actions_config_model");
        $this->CI->Bonuses_actions_config_model->setActionsConfig($this->action_config);
        return true;
    }

    public function install_bonuses_lang_export()
    {

    }

    public function uninstall_bonuses()
    {

    }
}
