<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

$_theme["gid"] = "flatty";
$_theme["type"] = "user";
$_theme["name"] = "User area theme";
$_theme["description"] = "Default user side template; Pg Theme";
$_theme["default_scheme"] = "default";
$_theme["setable"] = "1";
$_theme["scss"] = "1";
$_theme["template_engine"] = "twig";

$_theme["logo_width"] = "111";
$_theme["logo_height"] = "21";
$_theme["mini_logo_width"] = "30";
$_theme["mini_logo_height"] = "30";
$_theme["logo_default"] = "logo.png";
$_theme["mini_logo_default"] = "mini_logo.png";


$_theme["schemes"] = array(
	"default" => array(
			"name" => "Default color scheme",
			"active" => "1",
			"color_settings" => 'a:46:{s:14:"index_bg_image";s:0:"";s:17:"index_bg_image_bg";s:6:"EEEEF4";s:21:"index_bg_image_scroll";s:1:"1";s:27:"index_bg_image_adjust_width";s:1:"1";s:28:"index_bg_image_adjust_height";b:0;s:23:"index_bg_image_repeat_x";b:0;s:23:"index_bg_image_repeat_y";b:0;s:18:"index_bg_image_ver";s:1:"2";s:7:"html_bg";s:6:"EEEEF4";s:7:"main_bg";s:6:"F06078";s:9:"header_bg";s:6:"F06078";s:9:"footer_bg";s:6:"EEEEF4";s:13:"menu_hover_bg";s:6:"ECECEC";s:8:"hover_bg";b:0;s:8:"popup_bg";s:6:"FFFFFF";s:12:"highlight_bg";s:6:"FEB0A5";s:11:"input_color";s:6:"F06078";s:14:"input_bg_color";s:6:"FFFFFF";s:12:"status_color";s:6:"21A339";s:10:"link_color";s:6:"F06078";s:10:"font_color";s:6:"111111";s:12:"header_color";s:6:"111111";s:11:"descr_color";s:6:"777777";s:14:"contrast_color";s:6:"AAAAAA";s:15:"delimiter_color";s:6:"DDDDDD";s:10:"content_bg";s:6:"FFFFFF";s:11:"font_family";s:82:"\'SegoeUINormal\', Arial, \'Lucida Grande\',\'Lucida Sans Unicode\', Verdana, sans-serif";s:14:"main_font_size";s:2:"13";s:15:"input_font_size";s:2:"15";s:12:"h1_font_size";s:2:"20";s:12:"h2_font_size";s:2:"17";s:15:"small_font_size";s:2:"12";s:20:"search_btn_font_size";s:2:"22";s:15:"indicator_color";s:6:"21A339";s:14:"alert_error_bg";s:6:"F2DEDE";s:18:"alert_error_border";s:6:"EBCCD1";s:17:"alert_error_color";s:6:"A94442";s:16:"alert_success_bg";s:6:"DFF0D8";s:20:"alert_success_border";s:6:"D6E9C6";s:19:"alert_success_color";s:6:"3C763D";s:13:"alert_info_bg";s:6:"FFFFFF";s:17:"alert_info_border";s:6:"BCE8F1";s:16:"alert_info_color";s:6:"111111";s:16:"alert_warning_bg";s:6:"FCF8E3";s:20:"alert_warning_border";s:6:"FAEBCC";s:19:"alert_warning_color";s:6:"8A6D3B";}',
			"scheme_type" => 'light',
            "preset" => 'default',
		)
);

$_theme["social_schemes"] = array(
	"default" => array(
			"name" => "Default color scheme",
			"active" => "1",
			"color_settings" => 'a:46:{s:14:"index_bg_image";s:18:"index_bg_image.jpg";s:17:"index_bg_image_bg";s:6:"EEEEF4";s:21:"index_bg_image_scroll";s:1:"1";s:27:"index_bg_image_adjust_width";s:1:"1";s:28:"index_bg_image_adjust_height";b:0;s:23:"index_bg_image_repeat_x";b:0;s:23:"index_bg_image_repeat_y";b:0;s:18:"index_bg_image_ver";s:1:"2";s:7:"html_bg";s:6:"f5f6f8";s:7:"main_bg";s:6:"3498DB";s:9:"header_bg";s:6:"E6E6E6";s:9:"footer_bg";s:6:"E2EFF7";s:13:"menu_hover_bg";s:6:"E6E6E6";s:8:"hover_bg";b:0;s:8:"popup_bg";s:6:"FFFFFF";s:12:"highlight_bg";s:6:"EEEEEE";s:11:"input_color";s:6:"3498DB";s:14:"input_bg_color";s:6:"FFFFFF";s:12:"status_color";s:6:"28C4E6";s:10:"link_color";s:6:"3498DB";s:10:"font_color";s:6:"111111";s:12:"header_color";s:6:"0C1820";s:11:"descr_color";s:6:"777777";s:14:"contrast_color";s:6:"AAAAAA";s:15:"delimiter_color";s:6:"dddddd";s:10:"content_bg";s:6:"FFFFFF";s:11:"font_family";s:82:"\'SegoeUINormal\', Arial, \'Lucida Grande\',\'Lucida Sans Unicode\', Verdana, sans-serif";s:14:"main_font_size";s:2:"13";s:15:"input_font_size";s:2:"15";s:12:"h1_font_size";s:2:"20";s:12:"h2_font_size";s:2:"17";s:15:"small_font_size";s:2:"12";s:20:"search_btn_font_size";s:2:"22";s:15:"indicator_color";s:6:"E14D4C";s:14:"alert_error_bg";s:6:"F2DEDE";s:18:"alert_error_border";s:6:"EBCCD1";s:17:"alert_error_color";s:6:"A94442";s:16:"alert_success_bg";s:6:"DFF0D8";s:20:"alert_success_border";s:6:"D6E9C6";s:19:"alert_success_color";s:6:"3C763D";s:13:"alert_info_bg";s:6:"FFFFFF";s:17:"alert_info_border";s:6:"BCE8F1";s:16:"alert_info_color";s:6:"111111";s:16:"alert_warning_bg";s:6:"FCF8E3";s:20:"alert_warning_border";s:6:"FAEBCC";s:19:"alert_warning_color";s:6:"8A6D3B";}',
			"scheme_type" => 'light',
            "preset" => 'default',
		)
);

$_theme["css"] = array(
	"style" => array("file"=>"style-[dir].css", "media"=>"screen"),
	"mobile" => array("file"=>"mobile-[dir].css", "media"=>"screen"),
);

