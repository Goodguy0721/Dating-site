<?php
namespace Pg\Modules\Forum\Controllers;

use Pg\Libraries\View;

/**
* Forum api controller
*
* @package PG_Dating
* @subpackage application
* @category	modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

Class Api_Forum extends \Controller 
{

	function __construct() 
	{
		parent::__construct();
		$this->load->model('Forum_model');
	}

	/**
	 * Forum list
	 *
	 */
	public function forum_list() 
	{
		$language = $this->pg_language->current_lang_id;
		$params = array();
		$params['where']['status'] = 1;
		$params['where_in']['language'] = array('0', $language);
		$params['where']['show_results'] = 1;
		$forum = $this->Forum_model->get_forum_list(null, null, array('date_add' => 'ASC'), $params);
		$this->set_api_content('data', array('forum' => $forum, 'language' => $language));
	}

}
