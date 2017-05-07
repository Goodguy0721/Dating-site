<?php

namespace Pg\Modules\Questions\Models;

/**
 * Questions install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Rozhentsov Konstantin <nsavanaev@pilotgroup.net>
 **/
class Questions_install_model extends \Model
{
    protected $CI;

    /**
     * Menu configuration
     *
     * @params
     */
    protected $menu = array(
        'user_top_menu' => array(
            'action' => 'none',
            'name'   => 'Questions section menu',
            'items'  => array(
                'user-menu-communication' => array(
                    'action' => 'none',
                    'items'  => array(
                        'questions_item' => array('action' => 'create', 'link' => 'questions/index', 'status' => 1, 'sorter' => 5, 'indicator_gid' => 'new_questions_item'),
                    ),
                ),
            ),
        ),

        "admin_menu" => array(
            "action" => "none",
            "items"  => array(
                "content_items" => array(
                    "action" => "none",
                    "items"  => array(
                        "add_ons_items" => array(
                            "action" => "none",
                            "items"  => array(
                                "questions_menu_item" => array("action" => "create", "link" => "admin/questions/index", "status" => 1, "sorter" => 1),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'admin_questions_menu' => array(
            'action' => 'create',
            'name'   => 'Admin mode - Add-ons - Questions menu',
            'items'  => array(
                'questions_settings_item' => array('action' => 'create', 'link' => 'admin/questions/settings', 'status' => 1, 'sorter' => 3),
                'admin_questions_item'    => array('action' => 'create', 'link' => 'admin/questions/admin_questions', 'status' => 1, 'sorter' => 1),
                'users_questions_item'    => array('action' => 'create', 'link' => 'admin/questions/users_questions', 'status' => 1, 'sorter' => 2),
            ),
        ),

    );

    /**
     * Indicators configuration
     */
    private $menu_indicators = array(
        array(
            'gid'                  => 'new_questions_item',
            'delete_by_cron'       => false,
            'auth_type'            => 'user',
        ),
    );

    /**
     * Fields depended on languages
     *
     * @var array
     */
    private $lang_dm_data = array(
        array(
            "module"        => "questions",
            "model"         => "Questions_model",
            "method_add"    => "langDedicateModuleCallbackAdd",
            "method_delete" => "langDedicateModuleCallbackDelete",
        ),
    );

    /**
     * Seo pages configuration
     *
     * @params
     */
    private $_seo_pages = array(
        'index',
    );

    private $moderation_types = array(
        array(
            "name"                 => "questions",
            "mtype"                => "-1",
            "module"               => "questions",
            "model"                => "Questions_model",
            "check_badwords"       => "1",
            "method_get_list"      => "",
            "method_set_status"    => "",
            "method_delete_object" => "",
            "allow_to_decline"     => "0",
            "template_list_row"    => "",
        ),
    );

    /**
     * Notifications configuration
     *
     * @params
     */
    protected $_notifications = array(
        'notifications' => array(
            array('gid' => 'questions_new_question', 'send_type' => 'simple'),
            array('gid' => 'questions_answer', 'send_type' => 'simple'),
        ),
        'templates' => array(
            array('gid' => 'questions_new_question', 'name' => 'Question', 'vars' => array('avatar', 'sender_name', 'recipient_name', 'question', 'link'), 'content_type' => 'text'),
            array('gid' => 'questions_answer', 'name' => 'Answer', 'vars' => array('sender_name', 'recipient_name', 'answer', 'question', 'link'), 'content_type' => 'text'),
        ),
    );

     /**
      * Moderators configuration
      *
      * @params
      */
     protected $moderators = array(
        array('module' => 'questions', 'method' => 'index', 'is_default' => 1),
        array('module' => 'questions', 'method' => 'settings', 'is_default' => 1),
    );

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->model('Install_model');
    }

    /**
     *  Menu install
     *
     *  @return void
     */
    public function install_menu()
    {
        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data['action'], isset($menu_data["name"]) ? $menu_data["name"] : '');
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]['items']);
        }

        if (!empty($this->menu_indicators)) {
            $this->CI->load->model('menu/models/Indicators_model');
            foreach ($this->menu_indicators as $data) {
                $this->CI->Indicators_model->save_type(null, $data);
            }
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('questions', 'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }

        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, 'update', $gid, 0, $this->menu[$gid]['items'], $gid, $langs_file);
        }

        // Indicators
        if (!empty($this->menu_indicators)) {
            $langs_file = $this->CI->Install_model->language_file_read('questions', 'indicators', $langs_ids);
            if (!$langs_file) {
                log_message('info', '(resumes) Empty indicators langs data');

                return false;
            } else {
                $this->CI->load->model('menu/models/Indicators_model');
                $this->CI->Indicators_model->update_langs($this->menu_indicators, $langs_file, $langs_ids);
            }
        }

        return true;
    }

    public function install_menu_lang_export($langs_ids)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->helper('menu');

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]['items'], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }
        if (!empty($this->menu_indicators)) {
            $this->CI->load->model('menu/models/Indicators_model');
            $indicators_langs = $this->CI->Indicators_model->export_langs($this->menu_indicators, $langs_ids);
        }

        return array('menu' => $return);
    }

    public function deinstall_menu()
    {
        $this->CI->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            if ($menu_data['action'] == 'create') {
                linked_install_set_menu($gid, 'delete');
            } else {
                linked_install_delete_menu_items($gid, $this->menu[$gid]['items']);
            }
        }
        if (!empty($this->menu_indicators)) {
            $this->CI->load->model('menu/models/Indicators_model');
            foreach ($this->menu_indicators as $data) {
                $this->CI->Indicators_model->delete_type($data['gid']);
            }
        }
    }

    public function install_notifications()
    {
        // add notification
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');

        foreach ($this->_notifications['templates'] as $tpl) {
            $template_data = array(
                'gid'          => $tpl['gid'],
                'name'         => $tpl['name'],
                'vars'         => serialize($tpl['vars']),
                'content_type' => $tpl['content_type'],
                'date_add'     => date('Y-m-d H:i:s'),
                'date_update'  => date('Y-m-d H:i:s'),
            );
            $tpl_ids[$tpl['gid']] = $this->CI->Templates_model->save_template(null, $template_data);
        }

        foreach ($this->_notifications['notifications'] as $notification) {
            $notification_data = array(
                'gid'                 => $notification['gid'],
                'send_type'           => $notification['send_type'],
                'id_template_default' => $tpl_ids[$notification['gid']],
                'date_add'            => date("Y-m-d H:i:s"),
                'date_update'         => date("Y-m-d H:i:s"),
            );
            $this->CI->Notifications_model->save_notification(null, $notification_data);
        }
    }

    public function install_notifications_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Notifications_model');

        $langs_file = $this->CI->Install_model->language_file_read('questions', 'notifications', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty notifications langs data');

            return false;
        }

        $this->CI->Notifications_model->update_langs($this->_notifications, $langs_file, $langs_ids);

        return true;
    }

    public function install_notifications_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Notifications_model');
        $langs = $this->CI->Notifications_model->export_langs($this->_notifications, $langs_ids);

        return array('notifications' => $langs);
    }

    public function deinstall_notifications()
    {
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');
        foreach ($this->_notifications['templates'] as $tpl) {
            $this->CI->Templates_model->delete_template_by_gid($tpl['gid']);
        }
        foreach ($this->_notifications['notifications'] as $notification) {
            $this->CI->Notifications_model->delete_notification_by_gid($notification['gid']);
        }
    }

    public function install_moderation()
    {
        $this->CI->load->model('moderation/models/Moderation_type_model');
        foreach ($this->moderation_types as $mtype) {
            $mtype['date_add'] = date("Y-m-d H:i:s");
            $this->CI->Moderation_type_model->save_type(null, $mtype);
        }
    }

    public function install_moderation_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('questions', 'moderation', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->CI->load->model('moderation/models/Moderation_type_model');
        $this->CI->Moderation_type_model->update_langs($this->moderation_types, $langs_file);
    }

    public function install_moderation_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('moderation/models/Moderation_type_model');

        return array('moderation' => $this->CI->Moderation_type_model->export_langs($this->moderation_types, $langs_ids));
    }

    public function deinstall_moderation()
    {
        $this->CI->load->model('moderation/models/Moderation_type_model');
        foreach ($this->moderation_types as $mtype) {
            $type = $this->CI->Moderation_type_model->get_type_by_name($mtype["name"]);
            $this->CI->Moderation_type_model->delete_type($type['id']);
        }
    }

    /**
     * Install banners links
     */
    public function install_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->set_module("questions", "Questions_model", "bannerAvailablePages");
        $this->add_banners();
    }

    /**
     * Import banners languages
     */
    public function install_banners_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }

        $banners_groups = array('banners_group_questions_groups');
        $langs_file = $this->CI->Install_model->language_file_read('questions', 'pages', $langs_ids);
        $this->CI->load->model('banners/models/Banner_group_model');
        $this->CI->Banner_group_model->update_langs($banners_groups, $langs_file, $langs_ids);
    }

    /**
     * Unistall banners links
     */
    public function deinstall_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->delete_module("questions");
        $this->remove_banners();
    }

    /**
     * Add default banners
     */
    public function add_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->load->model("banners/models/Banner_place_model");

        $group_attrs = array(
            'date_created'  => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s"),
            'price'         => 1,
            'gid'           => 'questions_groups',
            'name'          => 'Questions pages',
        );
        $group_id = $this->CI->Banner_group_model->create_unique_group($group_attrs);
        $all_places = $this->CI->Banner_place_model->get_all_places();
        if ($all_places) {
            foreach ($all_places  as $key => $value) {
                if ($value['keyword'] != 'bottom-banner' && $value['keyword'] != 'top-banner') {
                    continue;
                }
                $this->CI->Banner_place_model->save_place_group($value['id'], $group_id);
            }
        }

        ///add pages in group
        $this->CI->load->model("Questions_model");
        $pages = $this->CI->Questions_model->bannerAvailablePages();
        if ($pages) {
            foreach ($pages  as $key => $value) {
                $page_attrs = array(
                    "group_id" => $group_id,
                    "name"     => $value["name"],
                    "link"     => $value["link"],
                );
                $this->CI->Banner_group_model->add_page($page_attrs);
            }
        }
    }

    /**
     * Remove banners
     */
    public function remove_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $group_id = $this->CI->Banner_group_model->get_group_id_by_gid("questions_groups");
        $this->CI->Banner_group_model->delete($group_id);
    }

    /**
     * Install moderators links
     */
    public function install_moderators()
    {
        //install ausers permissions
        $this->CI->load->model("Moderators_model");
        foreach ((array) $this->moderators as $method_data) {
            $validate_data = array("errors" => array(), "data" => $method_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $this->CI->Moderators_model->save_method(null, $validate_data["data"]);
        }
    }

    /**
     * Import moderators languages
     *
     * @param array $langs_ids
     */
    public function install_moderators_lang_update($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read("questions", "moderators", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty moderators langs data");

            return false;
        }
        // install moderators permissions
        $this->CI->load->model("Moderators_model");
        $params["where"]["module"] = "questions";
        $methods = $this->CI->Moderators_model->get_methods_lang_export($params);

        foreach ($methods as $method) {
            if (!empty($langs_file[$method["method"]])) {
                $this->CI->Moderators_model->save_method($method["id"], array(), $langs_file[$method["method"]]);
            }
        }
    }

    /**
     * Export moderators languages
     *
     * @param array $langs_ids
     */
    public function install_moderators_lang_export($langs_ids)
    {
        $this->CI->load->model("Moderators_model");
        $params["where"]["module"] = "questions";
        $methods = $this->CI->Moderators_model->get_methods_lang_export($params, $langs_ids);
        foreach ($methods as $method) {
            $return[$method["method"]] = $method["langs"];
        }

        return array('moderators' => $return);
    }

    /**
     * Uninstall moderators links
     */
    public function deinstall_moderators()
    {
        $this->CI->load->model("Moderators_model");
        $params = array();
        $params["where"]["module"] = "questions";
        $this->CI->Moderators_model->delete_methods($params);
    }

    /**
     * Install fields
     */
    public function _prepare_installing()
    {
        $this->CI->load->model("Questions_model");
        foreach ($this->CI->pg_language->languages as $lang_id => $value) {
            $this->CI->Questions_model->langDedicateModuleCallbackAdd($lang_id);
        }
    }

    public function _arbitrary_installing()
    {
        ///// add entries for lang data updates
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $this->CI->pg_language->add_dedicate_modules_entry($lang_dm_data);
        }
        // SEO
        $this->add_demo_content();
        $this->CI->pg_module->set_module_config('questions', 'allow_own_question', "1");
        $this->CI->pg_module->set_module_config('questions', 'action_for_communication', "mailbox");
    }

    /**
     * Import module languages
     *
     * @param array $langs_ids array languages identifiers
     *
     * @return void
     */
    public function _arbitrary_lang_install($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read('questions', 'arbitrary', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty questions arbitrary langs data');

            return false;
        }
        foreach ($this->_seo_pages as $page) {
            $post_data = array(
                'title'          => $langs_file["seo_tags_{$page}_title"],
                'keyword'        => $langs_file["seo_tags_{$page}_keyword"],
                'description'    => $langs_file["seo_tags_{$page}_description"],
                'header'         => $langs_file["seo_tags_{$page}_header"],
                'og_title'       => $langs_file["seo_tags_{$page}_og_title"],
                'og_type'        => $langs_file["seo_tags_{$page}_og_type"],
                'og_description' => $langs_file["seo_tags_{$page}_og_description"],
            );
            $this->CI->pg_seo->set_settings('user', 'questions', $page, $post_data);
        }
    }

    /**
     * Export module languages
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function _arbitrary_lang_export($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $seo_settings = $this->pg_seo->get_all_settings_from_cache('user', 'questions');
        $lang_ids = array_keys($this->CI->pg_language->languages);
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

    public function _arbitrary_deinstalling()
    {
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $this->CI->pg_language->delete_dedicate_modules_entry(array('where' => $lang_dm_data));
        }
        $this->CI->pg_seo->delete_seo_module('questions');
    }

    /**
     * Install demo content
     *
     * @return void
     */
    public function add_demo_content()
    {
        if (!defined('TABLE_QUESTIONS')) {
            define('TABLE_QUESTIONS', DB_PREFIX . 'questions');
        }

        $this->CI->load->model('Questions_model');
        // Associating languages id with codes
        foreach ($this->CI->pg_language->languages as $l) {
            $lang[$l['code']] = $l['id'];
            if (!empty($l['is_default'])) {
                $default_lang = $l;
            }
        }
        $demo_content = include MODULEPATH . 'questions/install/demo_content.php';

        //add questions
        if (!empty($demo_content['questions'])) {
            foreach ($demo_content['questions'] as $question) {
                foreach ($lang as $key => $id) {
                    if (empty($question['name'][$key])) {
                        $question['name_' . $id] = $question['name']['en'];
                    } else {
                        $question['name_' . $id] = $question['name'][$key];
                    }
                }
                unset($question['name']);

                $this->CI->db->insert(TABLE_QUESTIONS, $question);
            }
        }

        if (!empty($demo_content['settings'])) {
            foreach ($lang as $key => $id) {
                if (empty($demo_content['settings'][$key])) {
                    $value = $demo_content['settings']['en'];
                } else {
                    $value = $demo_content['settings'][$key];
                }
                $this->CI->pg_module->set_module_config('questions', 'action_description_' . $id, $value);
            }
        }

        return true;
    }
}
