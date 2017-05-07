<?php

namespace Pg\Modules\Properties\Models;

/**
 * Properties main model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Properties_model extends \Model
{
    protected $CI;
    public $properties = array(
        'user_type',
    );
    public $module_gid = 'data_properties';

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
    }

    public function get_property($ds_gid, $lang_id = null)
    {
        if (!$ds_gid) {
            return;
        }
        if (!$lang_id) {
            $lang_id = $this->CI->session->userdata('lang_id');
        }

        return $this->pg_language->ds->get_reference($this->module_gid, $ds_gid, $lang_id);
    }
}
