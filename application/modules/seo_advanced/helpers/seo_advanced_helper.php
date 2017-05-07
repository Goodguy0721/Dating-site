<?php

/**
 * Seo advanced module
 *
 * @package 	PG_Core
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Seo advanced management
 *
 * @package 	PG_Core
 * @subpackage 	Seo_advanced
 *
 * @category	helpers
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!function_exists('seo_traker')) {
    /**
     * Display tracker code
     *
     * @param string $placement tracker placement
     *
     * @return void
     */
    function seo_traker($placement = 'top')
    {
        $ci = &get_instance();
        $ci->load->model('Seo_advanced_model');
        $return = $ci->Seo_advanced_model->get_tracker_html($placement);

        return $return;
    }
}
