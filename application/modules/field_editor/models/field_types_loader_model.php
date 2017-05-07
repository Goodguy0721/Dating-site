<?php

namespace Pg\Modules\Field_editor\Models;

use Pg\Modules\Field_editor\Models\Fields\Field_type_model;

/**
 * Field types loader Model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 * */
class Field_types_loader_model
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function __get($var)
    {
        if (!$var) {
            return '';
        }
        $model_name = NS_MODULES . "Field_editor\\Models\\Fields\\" . ucfirst($var) . "_field_model";
        if (class_exists($model_name)) {
            $this->{$var} = new $model_name();
        } else {
            $this->{$var} = new Field_type_model();
        }

        return $this->{$var};
    }
}
