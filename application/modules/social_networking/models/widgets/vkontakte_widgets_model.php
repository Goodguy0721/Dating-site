<?php

namespace Pg\Modules\Social_networking\Models\Widgets;

/**
 * Social networking vkontakte widgets model
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

class Vkontakte_widgets_model extends \Model
{
    public $ci;
    public $widget_types = array(
        'comments',
        'like',
        'share',
    );
    public $openapi = false;
    public $head_loaded = false;
    private $has_key = false;

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    public function get_header($service_data = array(), $locale = '', $types = array())
    {
        $header = '';
        $appid = isset($service_data['app_key']) ? $service_data['app_key'] : false;
        if ($appid) {
            $this->has_key = true;
        }
        $header .= '
	<script>
		var vkontakte = [];
		var vkontakte_share = [];';
        if (!$this->openapi) {
            $header .= $appid ? ('
		loadScripts("//userapi.com/js/api/openapi.js?48", function(){
				VK.init({apiId: ' . $appid . ', onlyWidgets: true});
				for(var i in vkontakte){
					vkontakte[i]();
				}
			},
			[],
			{async: false}
		);') : '';
            $this->openapi = true;
        }

        $header .= '
		loadScripts("//vk.com/js/api/share.js?11", function(){
				$(".vk_share").html(VK.Share.button(false,{type: "button"}));
			},
			[],
			{async: false}
		);
	</script>';
        $this->head_loaded = (bool) $header;

        return $header;
    }

    public function get_like()
    {
        return $this->head_loaded && $this->openapi ? '
	<div id="vk_like"></div>
	<script type="text/javascript">
		vkontakte.push(function(){
			VK.Widgets.Like("vk_like", {type: "button", verb: 1, height: 28});
		});
	</script>' : '';
    }

    public function get_share()
    {
        return $this->head_loaded ? '<div class="vk_share"></div>' : '';
    }

    public function get_comments()
    {
        return $this->head_loaded && $this->openapi && $this->has_key ? '
	<div id="vk_comments"></div>
	<script type="text/javascript">
		window.onload = function () {
			VK.Widgets.Comments("vk_comments", {limit: 10, width: "496", attach: "*"});
        }
	</script>' : '';
    }
}
