<?php
/**
* Blogs api controller
*
* @package PG_Dating
* @subpackage application
* @category	modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

Class Api_Blogs extends Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Blogs_model');
	}

}
