<?php
namespace Pg\Modules\Forum\Models;

/**
* Forum install model
*
* @package PG_Dating
* @subpackage application
* @category	modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

class Forum_install_model extends \Model 
{
	private $ci;
	private $menu = array(
		'admin_menu' => array(
			'action' => 'none',
			'items' => array(
				'other_items' => array(
					'action'=>'none',
					'items' => array(
						'forum_menu_item' => array('action' => 'create', 'link' => 'admin/forum', 'status' => 1, 'sorter' => 2)
					)
				)
			)
		),
		'admin_forum_menu' => array(
			'action' => 'create',
			'name' => 'Forum section menu',
			'items' => array(
				'forum-index-item' => array('action' => 'create', 'link' => 'admin/forum', 'status' => 1),
				'forum-bans-item' => array('action' => 'create', 'link' => 'admin/forum/bans', 'status' => 0)
			)
		),
		'user_top_menu' => array(
			'action' => 'none',
			'items' => array(
				'user-menu-communication' => array(
					'action' => 'none',
					'items' => array(
						'forum_item' => array('action' => 'create', 'link' => 'forum', 'status' => 1, 'sorter' => 30),
					)
				)
			)
		),
	);
	
	private $moderation_types = array(
		array(
			"name" => "forum",
			"mtype" => "-1",
			"module" => "forum",
			"model" => "Forum_categories_model",
			"check_badwords" => "1",
			"method_get_list" => "",
			"method_set_status" => "",
			"method_delete_object" => "",
			"allow_to_decline" => "0",
			"template_list_row" => "",
		)
	);
	private $_seo_pages = array(
		'index',
		'topics',
		'messages',
	);

	function __construct() 
	{
		parent::__construct();
		$this->ci = & get_instance();
		//// load langs
		$this->ci->load->model('Install_model');
	}

	public function install_menu() 
	{
		$this->ci->load->helper('menu');
		foreach($this->menu as $gid => $menu_data){
			$this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data["action"], $menu_data["name"]);
			linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]["items"]);
		}
	}

	public function install_menu_lang_update($langs_ids = null) 
	{
		if(empty($langs_ids)) return false;
		$langs_file = $this->ci->Install_model->language_file_read('forum', 'menu', $langs_ids);

		if(!$langs_file) { log_message('info', 'Empty menu langs data'); return false; }

		$this->ci->load->helper('menu');

		foreach($this->menu as $gid => $menu_data){
			linked_install_process_menu_items($this->menu, 'update', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_file);
		}
		return true;
	}

	public function install_menu_lang_export($langs_ids) 
	{
		if(empty($langs_ids)) return false;
		$this->ci->load->helper('menu');

		$return = array();
		foreach($this->menu as $gid => $menu_data){
			$temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_ids);
			$return = array_merge($return, $temp);
		}
		return array( "menu" => $return );
	}

	public function deinstall_menu() 
	{
		$this->ci->load->helper('menu');
		foreach($this->menu as $gid => $menu_data){
			if($menu_data['action'] == 'create'){
				linked_install_set_menu($gid, 'delete');
			}else{
				linked_install_delete_menu_items($gid, $this->menu[$gid]['items']);
			}
		}
	}

	public function install_site_map() 
	{
		//// Site map
		$this->ci->load->model('Site_map_model');
		$site_map_data = array(
			'module_gid' => 'forum',
			'model_name' => 'Forum_categories_model',
			'get_urls_method' => 'get_sitemap_urls',
		);
		$this->ci->Site_map_model->set_sitemap_module('forum', $site_map_data);
	}

	private $moderators_methods = array(
		array('module' => 'forum', 'method' => 'index', 'is_default' => 1),
		array('module' => 'forum', 'method' => 'bans', 'is_default' => 1),
	);

	/**
	 * moderators module methods
	 */
	public function install_moderators() 
	{
		// install moderators permissions
		$this->ci->load->model('moderators/models/moderators_model');
		foreach($this->moderators_methods as $method){
			$this->ci->moderators_model->save_method(null, $method);
		}
	}

	public function install_moderators_lang_update($langs_ids = null) 
	{
		$langs_file = $this->ci->Install_model->language_file_read('forum', 'moderators', $langs_ids);

		// install moderators permissions
		$this->ci->load->model('moderators/models/moderators_model');
		$params['where']['module'] = 'forum';
		$methods = $this->ci->moderators_model->get_methods_lang_export($params);

		foreach($methods as $method){
			if(!empty($langs_file[$method['method']])){
				$this->ci->moderators_model->save_method($method['id'], array(), $langs_file[$method['method']]);
			}
		}
	}

	public function install_moderators_lang_export($langs_ids) 
	{
		$this->ci->load->model('moderators/models/moderators_model');
		$params['where']['module'] = 'forum';
		$methods =  $this->ci->moderators_model->get_methods_lang_export($params, $langs_ids);
		foreach($methods as $method){
			$return[$method['method']] = $method['langs'];
		}
		return array('moderators' => $return);
	}

	public function deinstall_moderators() 
	{
		// delete moderation methods in moderators
		$this->ci->load->model('moderators/models/moderators_model');
		$params['where']['module'] = 'forum';
		$this->ci->moderators_model->delete_methods($params);
	}

	function _arbitrary_installing() 
	{
		///// Seo
		$seo_data = array(
			'module_gid' => 'forum',
			'model_name' => 'Forum_categories_model',
			'get_settings_method' => 'get_seo_settings',
			'get_rewrite_vars_method' => 'request_seo_rewrite',
			'get_sitemap_urls_method' => 'get_sitemap_xml_urls',
		);
		$this->ci->pg_seo->set_seo_module('forum', $seo_data);
	}

	public function deinstall_site_map() 
	{
		$this->ci->load->model('Site_map_model');
		$this->ci->Site_map_model->delete_sitemap_module('forum');
	}
	
	public function install_moderation () 
	{
		// Moderation
		$this->ci->load->model('moderation/models/Moderation_type_model');
		foreach($this->moderation_types as $mtype) {
			$mtype['date_add'] = date("Y-m-d H:i:s");
			$this->ci->Moderation_type_model->save_type(null, $mtype);
		}
	}
	
	public function install_moderation_lang_update($langs_ids = null) 
	{
		if(!is_array($langs_ids)) $langs_ids = (array)$langs_ids;
		$langs_file = $this->ci->Install_model->language_file_read('forum', 'moderation', $langs_ids);

		if(!$langs_file) {
			log_message('info', 'Empty moderation langs data');
			return false;
		}
		$this->ci->load->model('moderation/models/Moderation_type_model');
		$this->ci->Moderation_type_model->update_langs($this->moderation_types, $langs_file);
														
	}

	public function install_moderation_lang_export($langs_ids = null) 
	{
		if(!is_array($langs_ids)) $langs_ids = (array)$langs_ids;
		$this->ci->load->model('moderation/models/Moderation_type_model');
		return array('moderation' => $this->ci->Moderation_type_model->export_langs($this->moderation_types, $langs_ids));
	}

	public function deinstall_moderation() {
		$this->ci->load->model('moderation/models/Moderation_type_model');
		foreach($this->moderation_types as $mtype) {
			$type = $this->ci->Moderation_type_model->get_type_by_name($mtype["name"]);
			$this->ci->Moderation_type_model->delete_type($type['id']);
		}
	}

	public function _arbitrary_lang_install($langs_ids = null) 
	{
		$langs_file = $this->ci->Install_model->language_file_read('forum', 'arbitrary', $langs_ids);
		if (!$langs_file) {
			log_message('info', 'Empty forum arbitrary langs data');
			return false;
		}
		foreach ($this->_seo_pages as $page) {
			$post_data = array(
				'title' => $langs_file["seo_tags_{$page}_title"],
				'keyword' => $langs_file["seo_tags_{$page}_keyword"],
				'description' => $langs_file["seo_tags_{$page}_description"],
				'header' => $langs_file["seo_tags_{$page}_header"],
				'og_title' => $langs_file["seo_tags_{$page}_og_title"],
				'og_type' => $langs_file["seo_tags_{$page}_og_type"],
				'og_description' => $langs_file["seo_tags_{$page}_og_description"],
			);
			$this->ci->pg_seo->set_settings('user', 'forum', $page, $post_data);
		}
	}

	/**
	 * Export module languages
	 * 
	 * @param array $langs_ids languages identifiers
	 * @return array
	 */
	public function _arbitrary_lang_export($langs_ids = null) 
	{
		if (empty($langs_ids)) {
			return false;
		}
		$seo_settings = $this->pg_seo->get_all_settings_from_cache('user', 'forum');
		$lang_ids = array_keys($this->ci->pg_language->languages);
		foreach ($seo_settings as $seo_page) {
			$prefix = 'seo_tags_' . $seo_page['method'];
			foreach ($lang_ids as $lang_id) {
				$meta = 'meta_' . $lang_id;
				$og = 'og_' . $lang_id;
				$arbitrary_return[$prefix . '_title'][$lang_id] = $seo_page[$meta]['title'];
				$arbitrary_return[$prefix . '_keyword'][$lang_id] = $seo_page[$meta]['keyword'];
				$arbitrary_return[$prefix . '_description'][$lang_id] = $seo_page[$meta]['description'];
				$arbitrary_return[$prefix . '_header'][$lang_id] = $seo_page[$meta]['header'];
				$arbitrary_return[$prefix . '_og_title'][$lang_id] = $seo_page[$og]['og_title'];
				$arbitrary_return[$prefix . '_og_type'][$lang_id] = $seo_page[$og]['og_type'];
				$arbitrary_return[$prefix . '_og_description'][$lang_id] = $seo_page[$og]['og_description'];
			}
		}
		return array('arbitrary' => $arbitrary_return);
	}

	function _arbitrary_deinstalling() 
	{
		$this->ci->pg_seo->delete_seo_module('forum');
	}
	
	/**
	 * Install banners links
	 */
	public function install_banners() 
	{
		///// add banners module
		$this->ci->load->model("banners/models/Banner_group_model");
		$this->ci->Banner_group_model->set_module("forum", "forum_categories_model", "_banner_available_pages");
		$this->add_banners();
	}

	/**
	 * Import banners languages
	 */
	public function install_banners_lang_update() 
	{
		$lang_ids = array_keys($this->ci->pg_language->languages);
		$lang_id = $this->ci->pg_language->get_default_lang_id();
		$lang_data[$lang_id] = "Q&A pages";
		$this->ci->pg_language->pages->set_string_langs("banners", "banners_group_forum_groups", $lang_data, $lang_ids);
	}

	/**
	 * Unistall banners links
	 */
	public function deinstall_banners() 
	{
		// delete banners module
		$this->ci->load->model("banners/models/Banner_group_model");
		$this->ci->Banner_group_model->delete_module("forum");
		$this->remove_banners();
	}

	/**
	 * Add default banners
	 */
	public function add_banners() 
	{
		$this->ci->load->model("Users_model");
		$this->ci->load->model("banners/models/Banner_group_model");
		$this->ci->load->model("banners/models/Banner_place_model");

		$group_attrs = array(
			'date_created' => date("Y-m-d H:i:s"),
			'date_modified' => date("Y-m-d H:i:s"),
			'price' => 1,
			'gid' => 'forum_groups',
			'name' => 'Forum pages'
		);
		$group_id = $this->ci->Banner_group_model->create_unique_group($group_attrs);
		$all_places = $this->ci->Banner_place_model->get_all_places();
		if ($all_places) {
			foreach ($all_places as $key => $value) {
				if ($value['keyword'] != 'bottom-banner' && $value['keyword'] != 'top-banner')
					continue;
				$this->ci->Banner_place_model->save_place_group($value['id'], $group_id);
			}
		}

		///add pages in group
		$this->ci->load->model("forum_categories_model");
		$pages = $this->ci->forum_categories_model->_banner_available_pages();
		if ($pages) {
			foreach ($pages as $key => $value) {
				$page_attrs = array(
					"group_id" => $group_id,
					"name" => $value["name"],
					"link" => $value["link"],
				);
				$this->ci->Banner_group_model->add_page($page_attrs);
			}
		}
	}

	/**
	 * Remove banners
	 */
	public function remove_banners() 
	{
		$this->ci->load->model("banners/models/Banner_group_model");
		$group_id = $this->ci->Banner_group_model->get_group_id_by_gid("forum_groups");
		$this->ci->Banner_group_model->delete($group_id);
	}

}
