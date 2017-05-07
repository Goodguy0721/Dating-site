<?php

namespace Pg\Modules\Social_networking\Models\Widgets;

/**
 * Social networking linkdin widget model
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

class Linkedin_widgets_model extends \Model
{
    public $ci;
    public $widget_types = array(
        'share',
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
        if (in_array('share', $types)) {
            $header .= '<script src="//platform.linkedin.com/in.js"></script>';
        }
        $this->head_loaded = (bool) $header;

        return $header;
    }

    public function get_share()
    {
        if ($this->head_loaded) {
            return <<<'EOD'
<script type="IN/Share" data-counter="right"></script>
<script>
	if(!$('.IN-widget').length && 'object' === typeof IN && 'function' === typeof IN.parse) {
		$(function(){IN.parse();});
	}
</script>
EOD;
        } else {
            return '';
        }
    }
}
