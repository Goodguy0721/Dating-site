<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('fast_navigation_helper')) {

        /**
         * Fast navigation helper function
         *
         * @return void
         */
	function fast_navigation_helper() {
		$ci = &get_instance();
		return $ci->view->fetch('form','admin', 'fast_navigation');
	}	
}