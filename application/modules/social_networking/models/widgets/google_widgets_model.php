<?php

namespace Pg\Modules\Social_networking\Models\Widgets;

/**
 * Social networking google widgets model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Google_widgets_model extends \Model
{
    public $ci;
    public $widget_types = array(
        'like',
    );
    public $head_loaded = false;

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    public function get_header($service_data = array(), $locale = '', $types = array())
    {
        $header = '';
        $lang = $this->ci->pg_language->get_lang_by_id($this->ci->pg_language->current_lang_id);
        $lang_code = isset($lang['code']) ? $lang['code'] : false;
        if (in_array('like', $types) && $lang_code) {
            $header = '<script type="text/javascript">window.___gcfg = {lang: \'' . $lang_code . '\'};(function() { var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true; po.src = \'https://apis.google.com/js/plusone.js\'; var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s); })();</script>';
        }
        $this->head_loaded = $header ? true : false;

        return $header;
    }

    public function get_like()
    {
        return $this->head_loaded ? '<g:plusone></g:plusone>' : '';
    }
}
