<?php

namespace Pg\Modules\Languages\Models;

/**
 * Languages module
 *
 * @copyright	Copyright (c) 2000-2016
 * @author	Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Languages_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    private $ci;

    /**
     * Constructor
     *
     * @return Languages_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    /**
     * Module check permission to edit data source
     *
     * @param array $module
     *
     * @return array
     */
    public function isDisabledDSActions(array $module)
    {
        $model_name = ucfirst($module['module_gid']) . "_model";
        $model = NS_MODULES . ucfirst($module['module_gid']) . "\\Models\\" . $model_name;
        if (class_exists($model)) {
            $this->ci->load->model($model_name);
            if (isset($this->ci->{$model_name}->is_disabled_action_ds)) {
                $module['is_disabled_action_ds'] = $this->ci->{$model_name}->is_disabled_action_ds;
            }
        }
        return $module;
    }
}