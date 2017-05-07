<?php

namespace Pg\Modules\Themes\Models;

/**
 * Themes main model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Themes_model extends \Model
{
    private $ci;

    public $fields_all = array(
        "id",
        "theme",
        "theme_type",
        "scheme",
        "active",
        "theme_name",
        "theme_description",
        'setable',
        'logo_default',
        'logo_width',
        'logo_height',
        'mini_logo_default',
        'mini_logo_width',
        'mini_logo_height',
        'template_engine',
    );

    public $bg_upload_config = array(
        'allowed_types' => 'gif|jpg|png|jpeg',
        'max_size'      => '10000',
        'max_width'     => '5000',
        'max_height'    => '5000',
        'overwrite'     => true,
    );

    public $ftp = false;

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();

        $this->ci->load->model('install/models/Ftp_model');

        $this->ci->config->load('install', true);
        $ftp_host = $this->ci->config->item('ftp_host', 'install');

        $ftp_user = $this->ci->config->item('ftp_user', 'install');
        $ftp_password = $this->ci->config->item('ftp_password', 'install');

        if ($this->ci->Ftp_model->ftp() && !empty($ftp_host) && !empty($ftp_user)) {
            $this->ci->Ftp_model->ftp_login($ftp_host, $ftp_user, $ftp_password);
            $this->ftp = true;
        }
    }

    public function get_installed_themes_list($params = array(), $filter_object_ids = null)
    {
        $this->ci->db->select(implode(", ", $this->fields_all));
        $this->ci->db->from(THEMES_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->ci->db->where($value);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->ci->db->where_in("id", $filter_object_ids);
        }

        $this->ci->db->order_by("id ASC");

        $results = $this->ci->db->get()->result_array();

        if (!empty($results) && is_array($results)) {
            foreach ($results as $r) {
                $r = $this->_format_theme($r);
                $data[] = $r;
            }

            return $data;
        }

        return false;
    }

    public function get_installed_themes_by_key($params = array(), $filter_object_ids = null)
    {
        $return = array();
        $themes = $this->get_installed_themes_list($params, $filter_object_ids);
        if (empty($themes)) {
            return false;
        }
        foreach ($themes as $theme) {
            $return[$theme["theme"]] = $theme;
        }

        return $return;
    }

    public function get_installed_themes_count($params = array(), $filter_object_ids = null)
    {
        $this->ci->db->select("COUNT(*) AS cnt");
        $this->ci->db->from(THEMES_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->ci->db->where($value);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->ci->db->where_in("id", $filter_object_ids);
        }

        $result = $this->ci->db->get()->result();
        if (!empty($result)) {
            return intval($result[0]->cnt);
        } else {
            return 0;
        }
    }

    public function get_uninstalled_themes_list($theme_type = '')
    {
        $return = array();
        if (!empty($theme_type)) {
            $params["where"]['theme_type'] = $theme_type;
        } else {
            $params = array();
        }
        $installed_themes = $this->get_installed_themes_by_key($params);

        $dir_path = $this->ci->pg_theme->theme_default_full_path;
        $d = dir($dir_path);
        while (false !== ($entry = $d->read())) {
            if (substr($entry, 0, 1) == '.') {
                continue;
            }
            if (isset($installed_themes[$entry]) && !empty($installed_themes[$entry])) {
                continue;
            }
            $theme_data = $this->ci->pg_theme->get_theme_data($entry);
            if (empty($theme_data)) {
                continue;
            }

            if (!empty($theme_type) && $theme_data["type"] != $theme_type) {
                continue;
            }

            $return[$entry] = $this->_format_theme($theme_data);
        }
        $d->close();

        return $return;
    }

    public function install_theme($theme)
    {
        $theme_data = $this->ci->pg_theme->get_theme_data($theme);
        if (empty($theme_data)) {
            return false;
        }

        $data = array(
            "theme"               => $theme,
            "theme_type"          => $theme_data["type"],
            "scheme"              => $theme_data["default_scheme"],
            "theme_name"          => $theme_data["name"],
            "theme_description"   => $theme_data["description"],
            "template_engine"     => $theme_data["template_engine"],
            'setable'             => intval($theme_data["setable"]) ? 1 : 0,
            "logo_default"        => $theme_data["logo_default"] ? $theme_data["logo_default"] : '',
            "logo_width"          => intval($theme_data["logo_width"]) ? intval($theme_data["logo_width"]) : 50,
            "logo_height"         => intval($theme_data["logo_height"]) ? intval($theme_data["logo_height"]) : 50,
            "mini_logo_default"   => $theme_data["mini_logo_default"] ? $theme_data["mini_logo_default"] : '',
            "mini_logo_width"     => intval($theme_data["mini_logo_width"]) ? intval($theme_data["mini_logo_width"]) : 30,
            "mini_logo_height"    => intval($theme_data["mini_logo_height"]) ? intval($theme_data["mini_logo_height"]) : 30,
            "mobile_logo_default" => $theme_data["mobile_logo_default"] ? $theme_data["mobile_logo_default"] : '',
            "mobile_logo_width"   => intval($theme_data["mini_logo_width"]) ? intval($theme_data["mini_logo_width"]) : 30,
            "mobile_logo_height"  => intval($theme_data["mini_logo_height"]) ? intval($theme_data["mini_logo_height"]) : 30,
        );
        $this->ci->db->insert(THEMES_TABLE, $data);
        $id_theme = $this->ci->db->insert_id();
        
        $bild_prefix = (SOCIAL_MODE === true) ? 'social_' : '';
        $this->install_sets($id_theme, $theme_data[$bild_prefix . "schemes"]);

        return true;
    }

    public function uninstall_theme($id)
    {
        $theme_data = $this->get_theme($id);
        if ($theme_data["active"] || $theme_data["default"]) {
            return false;
        }

        $this->ci->db->where('id', $id);
        $this->ci->db->delete(THEMES_TABLE);

        $this->uninstall_sets($id);

        return true;
    }

    public function get_theme($id = null, $theme = null, $lang_id = 0)
    {
        if (!empty($id)) {
            if (!$lang_id) {
                $lang_id = $this->ci->pg_language->current_lang_id;
            }
            $this->fields_all[] = 'logo_' . $lang_id;
            $this->fields_all[] = 'mini_logo_' . $lang_id;
            $result = $this->ci->db->select(implode(", ", $this->fields_all))
                    ->from(THEMES_TABLE)
                    ->where("id", $id)
                    ->get()->result_array();
            if (!empty($result)) {
                $theme_data = $result[0];
                $theme = $theme_data["theme"];
                $theme_data["logo"] = $theme_data['logo_' . $lang_id];
                $theme_data["mini_logo"] = $theme_data['mini_logo_' . $lang_id];
            }
        }

        $theme_data = array_merge($this->ci->pg_theme->get_theme_data($theme), $theme_data);
        $theme_data = $this->_format_theme($theme_data);

        return $theme_data;
    }

    public function set_active($id)
    {
        $theme_data = $this->get_theme($id);

        $attrs["active"] = 0;
        $this->ci->db->where('theme_type', $theme_data["theme_type"]);
        $this->ci->db->update(THEMES_TABLE, $attrs);

        $attrs["active"] = 1;
        $this->ci->db->where('id', $id);
        $this->ci->db->update(THEMES_TABLE, $attrs);

        return;
    }

    public function set_scheme($id, $scheme)
    {
        $attrs["scheme"] = $scheme;
        $this->ci->db->where('id', $id);
        $this->ci->db->update(THEMES_TABLE, $attrs);

        return;
    }

    public function _format_theme($theme)
    {
        if (empty($theme)) {
            return $theme;
        }
        if (empty($theme["theme"])) {
            $theme["theme"] = $theme["gid"];
        }
        if (empty($theme["theme_name"])) {
            $theme["theme_name"] = $theme["name"];
        }
        if (empty($theme["theme_description"])) {
            $theme["theme_description"] = $theme["description"];
        }
        if (empty($theme["setable"])) {
            $theme["setable"] = 0;
        }
        if (empty($theme["theme_type"])) {
            $theme["theme_type"] = $theme["type"];
        }
        if (empty($theme["scheme"])) {
            $theme["scheme"] = $theme["default_scheme"];
        }
        $theme["path"] = $this->ci->pg_theme->theme_default_full_path . $theme["theme"] . '/';
        $theme["url"] = $this->ci->pg_theme->theme_default_url . $theme["theme"] . '/';
        $theme["logo_url"] = $this->ci->pg_theme->theme_default_url . $theme["theme"] . '/logo/' . (!empty($theme["logo"]) ? $theme["logo"] : $theme["logo_default"]);
        $theme["mini_logo_url"] = $this->ci->pg_theme->theme_default_url . $theme["theme"] . '/logo/' . (!empty($theme["mini_logo"]) ? $theme["mini_logo"] : $theme["mini_logo_default"]);
        $img = $theme["path"] . "theme.png";
        if (file_exists($img)) {
            $theme["img"] = $theme["url"] . "theme.png";
        } else {
            $theme["img"] = "";
        }
        $default_theme = $this->ci->pg_theme->get_default_settings($theme["theme_type"]);
        $theme["default"] = ($default_theme["theme"] == $theme["theme"]) ? true : false;

        return $theme;
    }

    public function get_sets_list($id_theme)
    {
        $this->ci->db->select('id, set_name, set_gid, id_theme, color_settings, active, scheme_type, preset');
        $this->ci->db->from(THEMES_COLORSETS_TABLE)->where('id_theme', $id_theme);

        $this->ci->db->order_by("id ASC");

        $results = $this->ci->db->get()->result_array();

        if (!empty($results) && is_array($results)) {
            foreach ($results as $r) {
                $data[] = $this->_format_set($r);
            }

            return $data;
        }

        return false;
    }

    public function get_set_by_id($id)
    {
        $this->ci->db->select('id, set_name, set_gid, id_theme, color_settings, active, scheme_type, preset, is_generated')
                     ->from(THEMES_COLORSETS_TABLE)
                     ->where('id', $id);
        $results = $this->ci->db->get()->result_array();

        if (!empty($results) && is_array($results)) {
            $data = $this->_format_set($results[0]);

            return $data;
        }

        return array();
    }
    
    /**
     * Set is_generated param for colorset
     *
     * @param integer $id colorset idnetifier
     * @param integer $is_generated generated idnetifier
     *
     * @return void
     */
    public function setIsGenerated($set_id, $is_generated = 1)
    {
        $this->ci->db->where('id', $set_id);
        $this->ci->db->set('is_generated', $is_generated);
        $this->ci->db->update(THEMES_COLORSETS_TABLE);
    }
    
    /**
     * Regenerate colorset
     *
     * @param integer $id_set colorset identifier
     *
     * @return void
     */
    public function regenerateColorset($id_set) 
    {
        $set = $this->get_set_by_id($id_set);
        if(!$set['is_generated']) {
            $this->save_set($id_set, array('color_settings' => serialize($set['color_settings'])));          
            $this->setIsGenerated($id_set);          
        }
    }

    public function _format_set($set_data)
    {
        $set_data["color_settings"] = unserialize($set_data["color_settings"]);

        return $set_data;
    }

    public function get_sets_count($params)
    {
        $this->ci->db->select("COUNT(*) AS cnt");
        $this->ci->db->from(THEMES_COLORSETS_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->ci->db->where($value);
            }
        }

        $result = $this->ci->db->get()->result();
        if (!empty($result)) {
            return intval($result[0]->cnt);
        } else {
            return 0;
        }
    }

    public function validate_set($id, $data, $files = array(), $theme_data = array())
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($data["set_name"])) {
            $return["data"]["set_name"] = strip_tags($data["set_name"]);

            if (empty($return["data"]["set_name"])) {
                $return["errors"][] = l('error_set_name_incorrect', 'themes');
            }
        }

        if (isset($data["scheme_type"])) {
            $return["data"]["scheme_type"] = strip_tags($data["scheme_type"]);
        }

        if (isset($data["preset"])) {
            $return["data"]["preset"] = strval($data["preset"]);
        }

        if ($files) {
            $set = $this->get_set_by_id($id);
            if (!$theme_data && !empty($data['id_theme'])) {
                $theme_data = $this->get_theme($set['id_theme']);
            }
            if ($theme_data) {
                $theme_settings = $this->ci->pg_theme->format_theme_settings('', $theme_data['type'], $theme_data['gid'], $set['set_gid']);
                foreach ($files as $name => $bg_file) {
                    if ($bg_file === false) {
                        $data["color_settings"][$name] = '';
                    } else {
                        if (is_uploaded_file($_FILES[$name]['tmp_name'])) {
                            $upload = $this->upload_bg_image($name, SITE_PHYSICAL_PATH . $theme_settings['img_set_path'], SITE_VIRTUAL_PATH . $theme_settings['img_set_path']);
                            if ($upload['error']) {
                                $return["errors"][] = trim(strip_tags($upload['error']));
                            } elseif ($upload['is_uploaded']) {
                                $data["color_settings"][$name] = $upload['img_full_name'];
                                if (!empty($data["color_settings"][$name . '_ver'])) {
                                    ++$data["color_settings"][$name . '_ver'];
                                } else {
                                    $data["color_settings"][$name . '_ver'] = 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($data["color_settings"])) {
            $return["data"]["color_settings"] = serialize($data["color_settings"]);
        }

        if (isset($data["id_theme"])) {
            $return["data"]["id_theme"] = intval($data["id_theme"]);
        }

        if (isset($data["active"])) {
            $return["data"]["active"] = intval($data["active"]);
        }

        if (isset($data["set_gid"])) {
            $temp_gid = $return["data"]["set_gid"] = strtolower(trim(strip_tags($data["set_gid"])));
            $return["data"]["set_gid"] = preg_replace("/[^a-z0-9_\-]+/i", '-', $return["data"]["set_gid"]);
            $return["data"]["set_gid"] = preg_replace("/[\-]{2,}/i", '-', $return["data"]["set_gid"]);

            if ($return["data"]["set_gid"] == '-') {
                $return["data"]["set_gid"] = substr(md5($temp_gid), 0, 6);
            }

            if (empty($return["data"]["set_gid"])) {
                $return["errors"][] = l('error_set_gid_incorrect', 'themes');
            }

            $params["where"]["id_theme"] = $return["data"]["id_theme"];
            $params["where"]["set_gid"] = $return["data"]["set_gid"];
            if ($id) {
                $params["where"]["id <>"] = $id;
            }
            $count = $this->get_sets_count($params);
            if ($count > 0) {
                $return["errors"][] = l('error_set_gid_already_exists', 'themes');
            }
        }

        return $return;
    }

    public function save_set($id, $data)
    {
        if (empty($id)) {
            $this->ci->db->insert(THEMES_COLORSETS_TABLE, $data);
            $id = $this->ci->db->insert_id();
        } else {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(THEMES_COLORSETS_TABLE, $data);
        }

        $set = $this->get_set_by_id($id);
        $theme_data = $this->get_theme($set["id_theme"]);
        
        $bild_prefix = (SOCIAL_MODE === true) ? 'social_' : '';
        include $theme_data["path"] . "config/" . $bild_prefix . "colors.config.php";
        $theme_settings = $this->ci->pg_theme->format_theme_settings('', $theme_data['type'], $theme_data['gid'], $set["set_gid"]);
        $theme_data["css_path"] = SITE_PHYSICAL_PATH . $theme_settings["css_path"];
        $theme_data["img_path"] = SITE_PHYSICAL_PATH . $theme_settings["img_path"];
        $theme_data["img_set_path"] = SITE_PHYSICAL_PATH . $theme_settings["img_set_path"];
        $theme_data["set_path"] = $theme_data["path"] . "sets/" . $set["set_gid"] . "/";
        $this->generate_css($theme_data, $set["color_settings"], $scheme);

        return $id;
    }

    public function delete_set($id)
    {
        if (empty($id)) {
            log_message('error', '(themes) Set id is empty');

            return false;
        }
        $set = $this->get_set_by_id($id);
        if ($set["active"]) {
            return;
        }
        $theme_data = $this->get_theme($set["id_theme"]);

        $this->ci->db->where('id', $id);
        $this->ci->db->delete(THEMES_COLORSETS_TABLE);

        $theme_settings = $this->ci->pg_theme->format_theme_settings('', $theme_data['type'], $theme_data['gid'], $set["set_gid"]);
        $theme_data["css_path"] = SITE_PHYSICAL_PATH . $theme_settings["css_path"];
        $theme_data["set_path"] = $theme_data["path"] . "sets/" . $set["set_gid"] . "/";
        $this->delete_css($theme_data);

        return;
    }

    /**
     *  Clear all color sets
     *
     *  @return void
     */
    public function clearAllSet()
    {
        $this->ci->db->where('active', 0);
        $this->ci->db->delete(THEMES_COLORSETS_TABLE);
    }

    public function activate_set($id)
    {
        $set = $this->get_set_by_id($id);

        $data["active"] = 0;
        $this->ci->db->where('id_theme', $set["id_theme"]);
        $this->ci->db->update(THEMES_COLORSETS_TABLE, $data);

        $data["active"] = 1;
        $this->ci->db->where('id', $id);
        $this->ci->db->update(THEMES_COLORSETS_TABLE, $data);

        if (!empty($set['preset']) && $this->ci->pg_module->is_module_installed('dynamic_blocks')) {
            $this->ci->load->model('Dynamic_blocks_model');
            $preset = $this->ci->Dynamic_blocks_model->get_preset_by_gid($set['preset']);
            if (!empty($preset)) {
                $areas = $this->ci->Dynamic_blocks_model->get_areas_list();
                foreach ($areas as $area) {
                    $this->ci->Dynamic_blocks_model->set_area_preset($area['id'], $preset['id']);
                }
            }
        }

        return;
    }

    public function install_sets($id_theme, $sets)
    {
        foreach ($sets as $set_gid => $set) {
            $data = array(
                "id_theme"       => $id_theme,
                "set_name"       => $set["name"],
                "set_gid"        => $set_gid,
                "active"         => $set["active"],
                "color_settings" => $set["color_settings"],
                "scheme_type"    => $set["scheme_type"],
                "preset"         => !empty($set["preset"]) ? $set["preset"] : "",
            );
            $this->save_set(0, $data);
        }

        return;
    }

    public function uninstall_sets($id_theme)
    {
        $sets = $this->get_sets_list($id_theme);
        foreach ($sets as $set) {
            $this->delete_set($set["id"]);
        }

        return;
    }

    // tools
    public function regenerate_color_sets($id_theme)
    {
        $theme_data = $this->get_theme($id_theme);
        $sets = $this->get_sets_list($id_theme);

        $bild_prefix = (SOCIAL_MODE === true) ? 'social_' : '';
        include $theme_data["path"] . "config/" . $bild_prefix . "colors.config.php";
        foreach ($sets as $set) {
            $theme_settings = $this->ci->pg_theme->format_theme_settings('', $theme_data['type'], $theme_data['gid'], $set["set_gid"]);
            $theme_data["css_path"] = SITE_PHYSICAL_PATH . $theme_settings["css_path"];
            $theme_data["img_path"] = SITE_PHYSICAL_PATH . $theme_settings["img_path"];
            $theme_data["set_path"] = $theme_data["path"] . "sets/" . $set["set_gid"] . "/";
            $this->generate_css($theme_data, $set["color_settings"], $scheme);
        }

        return;
    }

    private function _generate_bg_propertie($theme_data, $color_settings, $prefix = '')
    {
        if ($color_settings[$prefix . '_bg_image']) {
            $background['background-image'] = "url('../img/{$color_settings[$prefix . '_bg_image']}?ver={$color_settings[$prefix . '_bg_image_ver']}')";
            if ($color_settings[$prefix . '_bg_image_repeat_x'] && $color_settings[$prefix . '_bg_image_repeat_x']) {
                $background['background-repeat'] = 'repeat';
            } elseif ($color_settings[$prefix . '_bg_image_repeat_x']) {
                $background['background-repeat'] = 'repeat-x';
            } elseif ($color_settings[$prefix . '_bg_image_repeat_y']) {
                $background['background-repeat'] = 'repeat-y';
            } else {
                $background['background-repeat'] = 'no-repeat';
            }
            $background['background-attachment'] = $color_settings[$prefix . '_bg_image_scroll'] ? 'scroll' : 'fixed';
            $background['background-position'] = $color_settings[$prefix . '_bg_image_repeat_x'] ? '0 0' : 'center 0';
            $background['background-color'] = '#' . str_replace('#', '', $color_settings[$prefix . '_bg_image_bg']);

            $result['index_bg'] = implode(' ', $background);
            $result['index_bg_size'] = ($color_settings[$prefix . '_bg_image_adjust_width'] ? '100%' : 'cover') . ' ' . ($color_settings[$prefix . '_bg_image_adjust_height'] ? '100%' : 'auto');
        } else {
            if (isset($color_settings[$prefix . '_bg_image_bg'])) {
                $result['index_bg'] = '#' . str_replace('#', '', $color_settings[$prefix . '_bg_image_bg']);
            } else {
                $result['index_bg'] = '';
            }
            $result['index_bg_size'] = 'auto auto';
        }

        return $result;
    }

    public function generate_css($theme_data, $color_settings, $default_color_settings)
    {
        $return = array("errors" => array());

        $path = $theme_data["set_path"];
        if (!is_dir($path)) {
            
            if($this->ftp) {
                // support fix begin
                $remove_string = '/home/lombardipil';
                $path_for_ftp = str_replace('/home/lombardipil', '', $path);
                $this->ci->Ftp_model->ftp_mkdir_rec($path_for_ftp);
                $this->ci->Ftp_model->ftp_chmod(0777, $path_for_ftp);
                // support fix end
            } elseif (!mkdir($path, 0777, true)) {
                $return["errors"][] = "Unable to create '" . $path . "'";
            }

        } elseif($this->ftp) {
            $this->ci->Ftp_model->ftp_chmod(0777, $path);
        } else {
            chmod($path, 0777);
        }

		$color_settings = array_merge($color_settings, $this->_generate_bg_propertie($theme_data, $color_settings, 'index'));
        $css_path = $theme_data["css_path"];
        if (!is_dir($css_path)) {

            if($this->ftp) {
                // support fix begin
                $remove_string = '/home/lombardipil';
                $css_path_for_ftp = str_replace('/home/lombardipil', '', $css_path);
                $this->ci->Ftp_model->ftp_mkdir_rec($css_path_for_ftp);
                $this->ci->Ftp_model->ftp_chmod(0777, $css_path_for_ftp);
                // support fix end
            } elseif (!mkdir($css_path, 0777, true)) {
                $return["errors"][] = "Unable to create '" . $css_path . "'";
            }

        } elseif($this->ftp) {
            $this->ci->Ftp_model->ftp_chmod(0777, $css_path);
        } else {
            chmod($css_path, 0777);
        }

        if (!empty($theme_data['scss'])) {
            foreach($default_color_settings as $name => $value){
				if($value["type"] == "color" && !empty($color_settings[$name])){
					$color_settings[$name] = '#'.$color_settings[$name];
				}elseif($value["type"] == "font" && !empty($color_settings[$name])){
					$color_settings[$name] = $color_settings[$name]."px";
				} elseif ($value["type"] == "file") {
                    $color_settings[$name] = "'" . $color_settings[$name] . "'";
                } elseif ($value["type"] == "text") {
                    $color_settings[$name] = "'" . $color_settings[$name] . "'";
                } elseif ($value["type"] == "checkbox") {
                    $color_settings[$name] = $color_settings[$name] ? '1' : '0';
                }
			}

			if(!is_dir(TEMPPATH . 'scss/')){
				
                if($this->ftp) {
                    // support fix begin
                    $remove_string = '/home/lombardipil';
                    $scss_path_for_ftp = str_replace('/home/lombardipil', '', TEMPPATH . 'scss/');
                    $this->ci->Ftp_model->ftp_mkdir_rec($scss_path_for_ftp);
                    $this->ci->Ftp_model->ftp_chmod(0777, $scss_path_for_ftp);
                    // support fix end
                } elseif(!mkdir(TEMPPATH . 'scss/', 0777, true)){
					$return["errors"][] = "Unable to create '".TEMPPATH . "scss/'";
				}

			} else {
                
                if($this->ftp) {
                    $this->ci->Ftp_model->ftp_chmod(0777, TEMPPATH . 'scss/');
                } else {
                    chmod(TEMPPATH . 'scss/', 0777);
                }
                
                $d = dir(TEMPPATH . 'scss/');

                while (false !== ($entry = $d->read())) {
                    if (substr($entry, 0, 1) == '.' || $entry == 'index.html') {
                        continue;
                    }

                    unlink(TEMPPATH . 'scss/' . $entry);
                }

                $d->close();
            }

			$h = fopen(TEMPPATH . 'scss/config.scss', "w");
			if($h){
				foreach($color_settings as $name => $value){
                    if (strlen($value) > 0) {
                        fwrite($h, "$".$name.": ".$value.";\n");
                    }
				}
				fclose($h);
			}else{
				$return["errors"][] = "Permission denied: '".TEMPPATH . "scss/config.scss'";
			}

            $this->ci->load->library('scssc');

            $scss_folder = $theme_data["path"] . 'scss';

            $import_paths = array($scss_folder, TEMPPATH . 'scss/');

            $h = fopen(TEMPPATH . 'scss/modules.scss', "w");
            if ($h) {
                $modules = $this->ci->pg_module->get_modules();
                foreach ($modules as $module) {
                    $filelist = glob(MODULEPATH . $module['module_gid'] . '/views/' . $theme_data['gid'] . '/scss/*.scss');
                    if (!empty($filelist)) {
                        foreach ($filelist as $file) {
                            $filename = $module['module_gid'] . '_' . basename($file);
                            copy($file, TEMPPATH . 'scss/' . $filename);
                            fwrite($h, "@import \"" . $filename . "\";\n");
                        }
                    }
                }
                fclose($h);
            } else {
                $return["errors"][] = "Permission denied: '" . TEMPPATH . "scss/modules.scss'";
            }

            // set the path to your to-be-imported mixins
            $this->ci->scssc->setImportPaths($import_paths);

            // set css formatting (normal, nested or minimized)
            $this->ci->scssc->setFormatter('scss_formatter_compressed');

            // step through all .scss files in that folder
            foreach ($theme_data['css'] as $css_gid => $css_data) {
                // get .scss's content, put it into $string_sass
                $string_sass = file_get_contents($scss_folder . '/' . $css_gid . ".scss");

                // try/catch block to prevent script stopping when scss compiler throws an error
                try {
                    $this->ci->scssc->unregisterFunction('get_language_dir');
                    $this->ci->scssc->registerFunction('get_language_dir', function () {
                        return 'ltr';
                    });
                    $string_css = $this->ci->scssc->compile($string_sass);
                    
                    $css_path_full = $css_path . str_replace('[dir]', 'ltr', $css_data['file']);
                    
                    if($this->ftp) {
                        $this->ci->Ftp_model->ftp_chmod(0777, $css_path_full);
                    } else {
                        chmod($css_path_full, 0777);
                    }

                    file_put_contents($css_path_full, $string_css);

                } catch (Exception $e) {
                    $return["errors"][] = "Permission denied: '" . $css_path_full . "'";
                }

                try {
                    $this->ci->scssc->unregisterFunction('get_language_dir');
                    $this->ci->scssc->registerFunction('get_language_dir', function ($args) {
                        return 'rtl';
                    });
                    $string_css = $this->ci->scssc->compile($string_sass);
                    

                    $css_path_full = $css_path . str_replace('[dir]', 'rtl', $css_data['file']);
                    
                    if($this->ftp) {
                        $this->ci->Ftp_model->ftp_chmod(0777, $css_path_full);
                    } else {
                        chmod($css_path_full, 0777);
                    }
                    
                    file_put_contents($css_path_full, $string_css);

                } catch (Exception $e) {
                    $return["errors"][] = "Permission denied: '" . $css_path_full . "'";
                }
            }
        } else {
            foreach ($default_color_settings as $name => $value) {
                if ($value["type"] == "color") {
                    $color_settings[$name . '_rgb'] = !empty($color_settings[$name]) ?
                        hexdec(substr($color_settings[$name], 0, 2)) . ','
                        . hexdec(substr($color_settings[$name], 2, 2)) . ','
                        . hexdec(substr($color_settings[$name], 4, 2)) : '';
                    $color_settings[$name] = !empty($color_settings[$name]) ? '#' . $color_settings[$name] : '';
                } elseif ($value["type"] == "font") {
                    $color_settings[$name] = !empty($color_settings[$name]) ? $color_settings[$name] . "px" : "";
                }
            }
            foreach ($theme_data['css'] as $css_gid => $css_data) {
                $css_left_file = str_replace('[rtl]', 'ltr', $css_data['file']);
                $file_left_content = file_get_contents($theme_data["path"] . 'config/' . $css_left_file);
                foreach ($color_settings as $param => $value) {
                    $file_left_content = str_replace('[' . $param . ']', $value, $file_left_content);
                }
                $h = fopen($css_path . $css_left_file, "w");
                if ($h) {
                    fwrite($h, $file_left_content);
                    fclose($h);
                } else {
                    $return["errors"][] = "Permission denied: '" . $css_path . $css_left_file . "'";
                }

                if ($css_left_file !== $css_data['file']) {
                    $css_right_file = str_replace('[rtl]', 'rtl', $css_data['file']);
                    $file_right_content = file_get_contents($theme_data["path"] . 'config/' . $css_right_file);
                    foreach ($color_settings as $param => $value) {
                        $file_right_content = str_replace('[' . $param . ']', $value, $file_right_content);
                    }
                    $h = fopen($css_path . $css_right_file, "w");
                    if ($h) {
                        fwrite($h, $file_right_content);
                        fclose($h);
                    } else {
                        $return["errors"][] = "Permission denied: '" . $css_path . $css_right_file . "'";
                    }
                }
            }
        }

        return $return;
    }

    public function delete_css($theme_data)
    {
        $path = $theme_data["set_path"];
        $css_path = $theme_data["css_path"];

        foreach ($theme_data['css'] as $css_data) {
            $css_left_file = str_replace('[rtl]', 'ltr', $css_data['file']);

            if (file_exists($css_path . $css_left_file)) {
                unlink($css_path . $css_left_file);
            }

            if ($css_left_file !== $css_data['file']) {
                $css_right_file = str_replace('[rtl]', 'rtl', $css_data['file']);

                if (file_exists($css_path . $css_right_file)) {
                    unlink($css_path . $css_right_file);
                }
            }
        }

        if (is_dir($css_path)) {
            rmdir($css_path);
        }
        if (is_dir($path)) {
            rmdir($path);
        }

        return;
    }

    public function generate_sprite($theme_data, $color_settings, $default_color_settings, $sprites)
    {
        $return = array();
        $path = $theme_data["set_path"];
        $img_path = $theme_data["img_set_path"];

        if ($path && !is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                $return["errors"][] = "Unable to create '" . $path . "'";
            }
        }
        if ($img_path && !is_dir($img_path)) {
            if (!mkdir($img_path, 0777, true)) {
                $return["errors"][] = "Unable to create '" . $img_path . "'";
            }
        }
        if (!$path || !$img_path) {
            return $return;
        }

        $start_color = $default_color_settings["main_bg"]["light_default"];
        $draw_settings['start_array'] = $start_array = array(
            'red'   => hexdec(substr($start_color, 0, 2)),
            'green' => hexdec(substr($start_color, 2, 2)),
            'blue'  => hexdec(substr($start_color, 4, 2)),
        );

        $end_color = $color_settings["main_bg"];
        $draw_settings['end_array'] = $end_array = array(
            'red'   => hexdec(substr($end_color, 0, 2)),
            'green' => hexdec(substr($end_color, 2, 2)),
            'blue'  => hexdec(substr($end_color, 4, 2)),
        );

        $start_error_color = $default_color_settings["status_color"]["light_default"];
        $draw_settings['start_error_array'] = $start_error_array = array(
            'red'   => hexdec(substr($start_error_color, 0, 2)),
            'green' => hexdec(substr($start_error_color, 2, 2)),
            'blue'  => hexdec(substr($start_error_color, 4, 2)),
        );

        $end_error_color = $color_settings["status_color"];
        $draw_settings['end_error_array'] = $end_error_array = array(
            'red'   => hexdec(substr($end_error_color, 0, 2)),
            'green' => hexdec(substr($end_error_color, 2, 2)),
            'blue'  => hexdec(substr($end_error_color, 4, 2)),
        );
        foreach ($sprites as $sprite) {
            if (!empty($sprite['file'])) {
                $img_left_file = str_replace('[rtl]', 'ltr', $sprite['file']);
                if (is_file($theme_data["path"] . 'config/' . $img_left_file)) {
                    $this->drawSprite($theme_data["path"] . 'config/' . $img_left_file, $img_path . $img_left_file, $sprite["width"], $sprite["height"], $draw_settings);
                }

                if ($img_left_file != $sprite['file']) {
                    $img_right_file = str_replace('[rtl]', 'rtl', $sprite['file']);
                    if (is_file($theme_data["path"] . 'config/' . $img_right_file)) {
                        $this->drawSprite($theme_data["path"] . 'config/' . $img_right_file, $img_path . $img_right_file, $sprite["width"], $sprite["height"], $draw_settings);
                    }
                }
            }
        }

        return $return;
    }

    private function drawSprite($file_from, $file_to, $width, $height, $color_settings)
    {
        $src = ImageCreateFromPng($file_from);

        imageSaveAlpha($src, true);
        imageAlphaBlending($src, false);

        $start_array = $color_settings['start_array'];
        $end_array = $color_settings['end_array'];
        $start_error_array = $color_settings['start_error_array'];
        $end_error_array = $color_settings['end_error_array'];

        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $rgb = imagecolorat($src, $x, $y);
                $colors = imagecolorsforindex($src, $rgb);

                if ($colors["red"] == $start_array["red"] && $colors["green"] == $start_array["green"] && $colors["blue"] == $start_array["blue"]) {
                    $alpha[$colors["alpha"]] = 1;
                    $c = imagecolorallocatealpha($src, $end_array["red"], $end_array["green"], $end_array["blue"], $colors["alpha"]);
                    imagesetpixel($src, $x, $y, $c);
                }

                if ($colors["red"] == $start_error_array["red"] && $colors["green"] == $start_error_array["green"] && $colors["blue"] == $start_error_array["blue"]) {
                    $alpha[$colors["alpha"]] = 1;
                    $c = imagecolorallocatealpha($src, $end_error_array["red"], $end_error_array["green"], $end_error_array["blue"], $colors["alpha"]);
                    imagesetpixel($src, $x, $y, $c);
                }
            }
        }

        imagepng($src, $file_to);
        imagedestroy($src);

        return;
    }

    public function delete_sprite($theme_data, $sprites)
    {
        $path = $theme_data["set_path"];
        $img_path = $theme_data["img_set_path"];

        foreach ($sprites as $sprite) {
            $img_left_file = str_replace('[rtl]', 'ltr', $sprite['file']);

            if (file_exists($img_path . $img_left_file)) {
                unlink($img_path . $img_left_file);
            }

            if ($img_left_file !== $sprite['file']) {
                $img_right_file = str_replace('[rtl]', 'rtl', $sprite['file']);

                if (file_exists($img_path . $img_right_file)) {
                    unlink($img_path . $img_right_file);
                }
            }
        }

        if (is_dir($img_path)) {
            rmdir($img_path);
        }
        if (is_dir($path)) {
            rmdir($path);
        }

        return;
    }

    public function parse_current_rtl($css_path)
    {
        $file_content = file_get_contents($css_path);

        // replace float|text-align|background-position: left
        $file_content = preg_replace('/:\s*left/i', ': [left]', $file_content);
        $file_content = preg_replace('/:\s*right/i', ': left', $file_content);
        $file_content = str_replace(': [left]', ': right', $file_content);

        $file_content = str_replace('-ltr.png', '-rtl.png', $file_content);

        // replace (border-|margin-|padding-|)left
        $file_content = str_replace('left:', '[left]:', $file_content);
        $file_content = str_replace('right:', 'left:', $file_content);
        $file_content = str_replace('[left]:', 'right:', $file_content);

        preg_match_all('/(margin|padding|border):\s+([0-9px]+)\s+([0-9px]+)\s+([0-9px]+)\s+([0-9px]+)/i', $file_content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $replace = $match[0];
            $with = $match[1] . "[changed]: " . $match[2] . " " . $match[5] . " " . $match[4] . " " . $match[3];
            $file_content = str_replace($replace, $with, $file_content);
        }
        $file_content = preg_replace('/(margin|padding|border)\[changed\]/', "$1", $file_content);

        return $file_content;
    }

    // logo methods
    public function upload_logo($upload_gid, $id_theme, $width, $height, $lang_id = 0)
    {
        $return = array('error' => '', 'success' => false);
        if (!$lang_id) {
            $lang_id = $this->ci->pg_language->current_lang_id;
        }

        $theme_data = $this->get_theme($id_theme);
        $theme = $theme_data["theme"];
        $this->ci->load->helper('upload');
        $path = $this->ci->pg_theme->theme_default_full_path . $theme . '/logo/';

        // upload src file
        $upload_config = array(
            'allowed_types' => 'jpg|gif|png',
            'overwrite'     => true,
        );
        $image_return = upload_file($upload_gid, $path, $upload_config);
        if (!empty($image_return["error"])) {
            $return["error"] = implode('<br>', $image_return["error"]);
        } else {
            $lang_data = $this->ci->pg_language->get_lang_by_id($lang_id);
            $new_name = $upload_gid . "_" . $lang_data["code"] . $image_return["data"]["file_ext"];
            if ($new_name != $image_return["data"]["orig_name"]) {
                copy($image_return["data"]["full_path"], $path . $new_name);
                @unlink($image_return["data"]["full_path"]);

                @ini_set("memory_limit", "512M");
                $this->ci->load->library('image_lib');

                $resize_config['source_image'] = $path . $new_name;
                $resize_config['create_thumb'] = false;
                $resize_config['width'] = $width;
                $resize_config['height'] = $height;
                $resize_config['maintain_ratio'] = true;

                $this->ci->image_lib->initialize($resize_config);
                $this->ci->image_lib->resize();
                if (!empty($this->ci->image_lib->error_msg)) {
                    $return["error"] = implode('<br>', $this->ci->image_lib->error_msg);
                } else {
                    $return['success'] = true;
                    $data[$upload_gid . "_" . $lang_id] = $new_name;
                    $this->ci->db->where('id', $id_theme);
                    $this->ci->db->update(THEMES_TABLE, $data);
                }
            }
        }

        return $return;
    }

    public function delete_logo($id_theme, $lang_id, $type = 'logo')
    {
        $theme_data = $this->get_theme($id_theme, '', $lang_id);
        if ($theme_data[$type]) {
            $path = $this->ci->pg_theme->theme_default_full_path . $theme_data["theme"] . '/logo/' . $theme_data[$type];
            @unlink($path);
            $data[$type . "_" . $lang_id] = '';
            $this->ci->db->where('id', $id_theme);
            $this->ci->db->update(THEMES_TABLE, $data);
        }

        return;
    }

    public function validate_logo_params($data)
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($data["logo_width"])) {
            $return["data"]["logo_width"] = intval($data["logo_width"]);
        }

        if (isset($data["logo_height"])) {
            $return["data"]["logo_height"] = intval($data["logo_height"]);
        }

        if (isset($data["mini_logo_width"])) {
            $return["data"]["mini_logo_width"] = intval($data["mini_logo_width"]);
        }

        if (isset($data["mini_logo_height"])) {
            $return["data"]["mini_logo_height"] = intval($data["mini_logo_height"]);
        }

        if ((isset($return["data"]["logo_width"]) && $return["data"]["logo_width"] <= 0) || (isset($return["data"]["mini_logo_width"]) && $return["data"]["mini_logo_width"] <= 0)) {
            $return["errors"][] = l('error_logo_width_error', 'themes');
        }
        if ((isset($return["data"]["logo_height"]) && $return["data"]["logo_height"] <= 0) || (isset($return["data"]["mini_logo_height"]) && $return["data"]["mini_logo_height"] <= 0)) {
            $return["errors"][] = l('error_logo_height_error', 'themes');
        }

        return $return;
    }

    public function save_logo_params($id_theme, $data)
    {
        if (!empty($data)) {
            $this->ci->db->where('id', $id_theme);
            $this->ci->db->update(THEMES_TABLE, $data);
        }

        return;
    }

    public function check_theme_permissions($theme)
    {
        $path_logo = $this->ci->pg_theme->theme_default_full_path . $theme . '/logo/';
        $path_sets = $this->ci->pg_theme->theme_default_full_path . $theme . '/sets/';
        $perm = array(
            'logo'      => is_writable($path_logo),
            'logo_path' => $this->ci->pg_theme->theme_default_path . $theme . '/logo/',
            'sets'      => is_writable($path_sets),
            'sets_path' => $this->ci->pg_theme->theme_default_path . $theme . '/sets/',
        );

        return $perm;
    }

    public function _dynamic_block_get_logo_block()
    {
        return $this->ci->view->fetch('dynamic_block_logo', 'user', 'themes');
    }

    public function upload_bg_image($name, $path, $url)
    {
        $result['error'] = false;
        $result['is_uploaded'] = false;
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }

        $bg_upload_config = $this->bg_upload_config;
        $bg_upload_config['upload_path'] = $path;
        $bg_upload_config['file_name'] = $name;
        $this->ci->load->helper('upload');
        $upload = upload_file($name, $path, $bg_upload_config);
        if (empty($upload['error']) && !empty($upload['data'])) {
            $result['is_uploaded'] = true;
            $result['img_name'] = $upload['data']['raw_name'];
            $result['img_ext'] = $upload['data']['file_ext'];
            $result['img_full_name'] = $upload['data']['file_name'];
            $result['upload_url'] = $url . $upload['data']['file_name'];
        } elseif (!empty($upload['error'])) {
            $result['error'] = $upload['error'];
        }

        return $result;
    }

    public function generate_css_for_current_themes() {
        $params = array();
        $params['where']['theme_type'] = 'user';
        $params['where']['active'] = 1;
        $themes = $this->get_installed_themes_list($params);
        foreach($themes as $theme) {
            $sets_list = $this->get_sets_list($theme['id']);
            foreach($sets_list as $list) {
                if ($list['active'] == 1) {
                    $this->save_set($list['id'], array('color_settings' => serialize($list['color_settings'])));
                }
            }
        }
    }

    public function getSections($color_settings = [], $set = [], $img_path = '') {
        $return = [];
        foreach ($color_settings as $key => $setting) {
            if(!empty($setting['active']) && $setting['active']) {
                if (empty($set['id'])) {
                    $setting['value'] = $setting["light_default"];
                } elseif(!empty($set["color_settings"][$key])) {
                    $setting['value'] = $set["color_settings"][$key];
                }
                if ($setting['type'] == 'file') {
                    $return['files'][$key] = $key;
                    if (!empty($set['color_settings'][$key])) {
                        $setting['value'] = site_url() . $img_path . $set["color_settings"][$key] . '?ver=1';
                    }
                }
                $return['section'][$setting['section']][$key] = $setting;
            }
        }
        unset($set["color_settings"]);
        $return['set'] = $set;

        return $return;
    }
}
