<?php

/**
 * active: 1, 0
 * section: bg_image, page, text, forms, alert
 * unit: unit_px, unit_pt, unit_em
 * 
 */
$scheme = array(
	'index_bg_image' => array(
        'active'=>0, 'section'=>'bg_image', 'unit'=>'', 'type'=>'file', 'autochange'=>'no', 'light_default'=> '', 'dark_default'=>''
    ),
	'index_bg_image_bg' => array(
        'active'=>0, 'section'=>'bg_image', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'EEEEF4', 'dark_default'=>'FFFFFF'
    ),
	'index_bg_image_scroll' => array(
        'active'=>0, 'section'=>'bg_image', 'unit'=>'', 'type'=>'checkbox', 'autochange'=>'no', 'light_default'=> '1', 'dark_default'=>'1'
    ),
	'index_bg_image_adjust_width' => array(
        'active'=>0, 'section'=>'bg_image', 'unit'=>'', 'type'=>'checkbox', 'autochange'=>'no', 'light_default'=> '1', 'dark_default'=>'0'
    ),
	'index_bg_image_adjust_height' => array(
        'active'=>0, 'section'=>'bg_image', 'unit'=>'', 'type'=>'checkbox', 'autochange'=>'no', 'light_default'=> '0', 'dark_default'=>'0'
    ),
	'index_bg_image_repeat_x' => array(
        'active'=>0, 'section'=>'bg_image', 'unit'=>'', 'type'=>'checkbox', 'autochange'=>'no', 'light_default'=> '0', 'dark_default'=>'0'
    ),
	'index_bg_image_repeat_y' => array(
        'active'=>0, 'section'=>'bg_image', 'unit'=>'', 'type'=>'checkbox', 'autochange'=>'no', 'light_default'=> '0', 'dark_default'=>'0'
    ),
	'index_bg_image_ver' => array(
        'active'=>0, 'section'=>'bg_image', 'unit'=>'', 'type'=>'text', 'autochange'=>'no', 'light_default'=>2, 'dark_default'=>2
    ),
	
	'html_bg' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'EEEEF4', 'dark_default'=>'343434'
    ),
	'main_bg' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'yes', 'light_default'=> 'F06078', 'dark_default'=>'568D2B'
    ),
	'header_bg' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'yes', 'light_default'=> 'F06078', 'dark_default'=>'272727'
    ),
	'footer_bg' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'yes', 'light_default'=> 'EEEEF4', 'dark_default'=>'252525'
    ),
	'menu_hover_bg' => array(
        'active'=>0, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'ECECEC', 'dark_default'=>'323232'
    ),
	'hover_bg' => array(
        'active'=>0, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '', 'dark_default'=>'272727'
    ),
	'popup_bg' => array(
        'active'=>0, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FFFFFF', 'dark_default'=>'4C4C4C'
    ),
	'highlight_bg' => array(
        'active'=>0, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'footer_bg', 'light_default'=> 'EEEEF4', 'dark_default'=>'4C4C4C'
    ),
	'input_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'yes', 'light_default'=> 'F06078', 'dark_default'=>'396615'
    ),
	'input_bg_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FFFFFF', 'dark_default'=>'343434'
    ),
	'status_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'yes', 'light_default'=> '21A339', 'dark_default'=>'DA7D38'
    ),
	'link_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'yes', 'light_default'=> 'F06078', 'dark_default'=>'7FC24A'
    ),

	'font_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '111111', 'dark_default'=>'B3B3B3'
    ),
	'header_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '111111', 'dark_default'=>'FFFFFF'
    ),
	'descr_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '777777', 'dark_default'=>'FFFFFF'
    ),
	'contrast_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'AAAAAA', 'dark_default'=>'FFFFFF'
    ),
	'delimiter_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'DDDDDD', 'dark_default'=>'666666'
    ),
    'indicator_color' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '21A339', 'dark_default'=>'057119'
    ),
    'content_bg' => array(
        'active'=>1, 'section'=>'page', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FFFFFF', 'dark_default'=>'FFFFFF'
    ),
	

	'font_family' => array(
        'active'=>1, 'section'=>'text', 'unit'=>'', 'type'=>'family', 'autochange'=>'no', 'light_default'=> "'SegoeUINormal', Arial, 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif", 'dark_default'=>"'SegoeUINormal', Arial, 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif"
    ),
	'main_font_size' => array(
        'active'=>1, 'section'=>'text', 'unit'=>'px', 'type'=>'font', 'autochange'=>'no', 'light_default'=> '13', 'dark_default'=>'13'
    ),
	'input_font_size' => array(
        'active'=>1, 'section'=>'text', 'unit'=>'px', 'type'=>'font', 'autochange'=>'no', 'light_default'=> '15', 'dark_default'=>'15'
    ),
	'h1_font_size' => array(
        'active'=>1, 'section'=>'text', 'unit'=>'px', 'type'=>'font', 'autochange'=>'no', 'light_default'=> '20', 'dark_default'=>'20'
    ),
	'h2_font_size' => array(
        'active'=>1, 'section'=>'text', 'unit'=>'px', 'type'=>'font', 'autochange'=>'no', 'light_default'=> '17', 'dark_default'=>'17'
    ),
	'small_font_size' => array(
        'active'=>1, 'section'=>'text', 'unit'=>'px', 'type'=>'font', 'autochange'=>'no', 'light_default'=> '12', 'dark_default'=>'12'
    ),
	'search_btn_font_size' => array(
        'active'=>1, 'section'=>'text', 'unit'=>'px', 'type'=>'font', 'autochange'=>'no', 'light_default'=> '22', 'dark_default'=>'22'
    ),
    
    'alert_error_bg' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'F2DEDE', 'dark_default'=>'CB7C7C'
    ),
    'alert_error_border' => array(
        'active'=>0, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'EBCCD1', 'dark_default'=>'D3929D'
    ),
    'alert_error_color' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'A94442', 'dark_default'=>'602A29'
    ),    
    'alert_success_bg' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'DFF0D8', 'dark_default'=>'ABD699'
    ),
    'alert_success_border' => array(
        'active'=>0, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'D6E9C6', 'dark_default'=>'A8CE88'
    ),
    'alert_success_color' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '3C763D', 'dark_default'=>'213F22'
    ),    
    'alert_info_bg' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FFFFFF', 'dark_default'=>'D9EDF7'
    ),
    'alert_info_border' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'BCE8F1', 'dark_default'=>'7ACFE1'
    ),
    'alert_info_color' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '111111', 'dark_default'=>'111111'
    ),    
    'alert_warning_bg' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FCF8E3', 'dark_default'=>'F4E69F'
    ),
    'alert_warning_border' => array(
        'active'=>0, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FAEBCC', 'dark_default'=>'EFC97B'
    ),
    'alert_warning_color' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '8A6D3B', 'dark_default'=>'67522D'
    ),
    'alert_error_bg' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'F2DEDE', 'dark_default'=>'CB7C7C'
    ),
    'alert_error_border' => array(
        'active'=>0, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'EBCCD1', 'dark_default'=>'D3929D'
    ),
    'alert_error_color' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'A94442', 'dark_default'=>'602A29'
    ),    
    'alert_success_bg' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'DFF0D8', 'dark_default'=>'ABD699'
    ),
    'alert_success_border' => array(
        'active'=>0, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'D6E9C6', 'dark_default'=>'A8CE88'
    ),
    'alert_success_color' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '3C763D', 'dark_default'=>'213F22'
    ),    
    'alert_info_bg' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FFFFFF', 'dark_default'=>'D9EDF7'
    ),
    'alert_info_border' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'BCE8F1', 'dark_default'=>'7ACFE1'
    ),
    'alert_info_color' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '111111', 'dark_default'=>'111111'
    ),    
    'alert_warning_bg' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FCF8E3', 'dark_default'=>'F4E69F'
    ),
    'alert_warning_border' => array(
        'active'=>0, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> 'FAEBCC', 'dark_default'=>'EFC97B'
    ),
    'alert_warning_color' => array(
        'active'=>1, 'section'=>'alert', 'unit'=>'', 'type'=>'color', 'autochange'=>'no', 'light_default'=> '8A6D3B', 'dark_default'=>'67522D'
    ),
);

$sprite = array(
	"icons" => array("file"=>"icons-awesome-[rtl].png", "width" => 100, "height" => 500),	
);

