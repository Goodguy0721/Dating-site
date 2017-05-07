<?php

namespace Pg\Modules\Likes\Models;

/**
 * Likes install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexander Batukhtin <abatukhtin@pilotgroup.net>
 * */
class Likes_install_model extends \Model
{
    public $CI;
    /**
     * Constructor
     *
     * @return Install object
     */
    protected $action_config = array(
        'likes_add_like' => array(
            'is_percent' => 0,
            'once' => 0,
            'available_period' => array(
                'all'),
            ),
    );

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->model('Install_model');
    }

    public function _arbitrary_installing()
    {
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
