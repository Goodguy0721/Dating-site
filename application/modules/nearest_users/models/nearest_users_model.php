<?php

namespace Pg\Modules\Nearest_users\Models;

/**
 * Nearest users module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2015 PG_Dating - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define('NEAREST_USERS_SETTINGS_TABLE', DB_PREFIX . 'nearest_users_settings');

/**
 * Nearest users main model
 *
 * @package 	PG_Dating
 * @subpackage 	Nearest users
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2015 PG_Dating - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Nearest_users_model extends \Model
{
    const MODULE_GID = 'nearest_users';

    const METERS_IN_KM = 1000;
    const METERS_IN_MILE = 1609.344;
    const KM_IN_RAD = 111.3;
    const MILES_IN_RAD = 69.1;
    const KM_TO_MILE_RATIO = 0.621371192;
    const DEFAULT_CIRCLE_RADIUS = 20;

    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $CI;

    /**
     * Link to database object
     *
     * @var object
     */
    protected $DB;

    /**
     * Settings table fields
     *
     * @var array
     */
    public $fields = array(
        'search_pattern',
        'search_radius',
        'search_radius_unit',
        'set_position_manual',
    );

    public $fields_all = array();

    /**
     * Constructor
     *
     * @return nearest_users_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->cache_dir = TEMPPATH . self::MODULE_GID . "/";
    }

    /**
     * Method validate data
     *
     * @var array
     *            return error or success
     */
    public function validate($data)
    {
        if (isset($data['search_pattern'])) {
            $return['data']['search_pattern'] = strip_tags($data['search_pattern']);
        } else {
            $return['data']['search_pattern'] = 'Circle';
        }

        if (isset($data['search_radius'])) {
            $return['data']['search_radius'] = intval($data['search_radius']);
        }

        if (isset($data['search_radius_unit'])) {
            $return['data']['search_radius_unit'] = strip_tags($data['search_radius_unit']);
        }

        if (isset($data['set_position_manual'])) {
            $return['data']['set_position_manual'] = intval($data['set_position_manual']);
        }

        return $return;
    }

    /**
     * Method save settings
     *
     * @var array
     *            return void
     */
    public function saveSettings($data)
    {
        foreach ($data as $setting => $value) {
            $this->CI->pg_module->set_module_config('nearest_users', $setting, $value);
        }
    }

    /**
     * Method get module settings
     *
     * @var string
     *             return array $result
     */
    public function getSettings()
    {
        $data = array(
            'search_pattern'      => $this->CI->pg_module->get_module_config('nearest_users', 'search_pattern'),
            'search_radius'       => $this->CI->pg_module->get_module_config('nearest_users', 'search_radius'),
            'search_radius_unit'  => $this->CI->pg_module->get_module_config('nearest_users', 'search_radius_unit'),
            'set_position_manual' => $this->CI->pg_module->get_module_config('nearest_users', 'set_position_manual'),
        );

        return $data;
    }

    /**
     * Method get circle settings
     *
     * return array $settings
     */
    public function getCircleSettings()
    {
        $this->load->model('Users_model');

        $user = $this->Users_model->get_user_by_id($this->session->userdata('user_id'), true);

        $settings = $this->getSettings();

        $settings['use_search_by_area'] = true;

        $circle_params = $this->session->userdata('circle_params');

        if ($settings['search_radius_unit'] == 'km') {
            $settings['default_search_radius'] = $settings['search_radius'] * self::METERS_IN_KM;
            if (!empty($circle_params['search_radius'])) {
                $settings['search_radius'] = $circle_params['search_radius'] * self::METERS_IN_KM;
            } else {
                $settings['search_radius'] *= self::METERS_IN_KM;
            }
        } else {
            $settings['default_search_radius'] = $settings['search_radius'] * self::METERS_IN_MILE;
            if (!empty($circle_params['search_radius'])) {
                $settings['search_radius'] = $circle_params['search_radius'] * self::METERS_IN_MILE;
            } else {
                $settings['search_radius'] *= self::METERS_IN_MILE;
            }
        }

        $settings['center_lon'] = !empty($circle_params['center_lon']) ? $circle_params['center_lon'] : $user['lon'];
        $settings['center_lat'] = !empty($circle_params['center_lat']) ? $circle_params['center_lat'] : $user['lat'];

        return $settings;
    }

    /**
     * Method get search criteria
     *
     * @var array
     *            return array $criteria
     */
    public function getSearchCriteria($data)
    {
        $criteria['where']['approved'] = '1';
        $criteria['where']['confirm'] = '1';
        $criteria['where']['activity'] = '1';

        if (!empty($data['id'])) {
            $criteria['where']['id <>'] = $data['id'];
        }
        if (!empty($data['min_lat'])) {
            $criteria['where']['lat >='] = (float) $data['min_lat'];
        }
        if (!empty($data['max_lat'])) {
            $criteria['where']['lat <='] = (float) $data['max_lat'];
        }
        if (!empty($data['min_lon'])) {
            $criteria['where']['lon >='] = (float) $data['min_lon'];
        }
        if (!empty($data['max_lon'])) {
            $criteria['where']['lon <='] = (float) $data['max_lon'];
        }
        if (!empty($data['distance']) && !empty($data['lat']) && !empty($data['lon'])) {
            $criteria['where']["(POW((69.1*(lon - " . $data['lon'] . ")*cos(" . $data['lat'] . "/57.3)),\"2\")+POW((69.1*(lat - " . $data['lat'] . ")),\"2\")) <="] =  $data['distance'];
        }

        return $criteria;
    }

    /**
     * Method get search data
     *
     * @var array
     *            return array $data
     */
    public function getSearchData($circle = null)
    {
        $settings = $this->getSettings();

        if ($settings['search_radius_unit'] == 'km') {
            $unit_ratio = self::METERS_IN_KM;
            $units_in_rad = self::KM_IN_RAD;
        } elseif ($settings['search_radius_unit'] == 'mile') {
            $unit_ratio = self::METERS_IN_MILE;
            $units_in_rad = self::MILES_IN_RAD;
        }

        if (empty($circle)) {
            $user = $this->Users_model->get_user_by_id($this->session->userdata('user_id'), true);
            $circle['center_lat'] = $user['lat'];
            $circle['center_lon'] = $user['lon'];

            if (!empty($settings['search_radius'])) {
                $circle['search_radius'] = self::DEFAULT_CIRCLE_RADIUS * $unit_ratio;
            }
        }

        $data['distance'] = $circle['search_radius'] / self::METERS_IN_MILE;
        $data['distance'] = intval($data['distance'] * $data['distance']);

        $circle['search_radius'] = $circle['search_radius'] / $unit_ratio;
        $circle['search_radius_unit'] = $settings['search_radius_unit'];
        $this->session->set_userdata("circle_params", $circle);

        $data['min_lon'] = $circle['center_lon'] - $circle['search_radius'] / abs(cos(deg2rad($circle['center_lat'])) * $units_in_rad);
        $data['max_lon'] = $circle['center_lon'] + $circle['search_radius'] / abs(cos(deg2rad($circle['center_lat'])) * $units_in_rad);
        $data['min_lat'] = $circle['center_lat'] - ($circle['search_radius'] / $units_in_rad);
        $data['max_lat'] = $circle['center_lat'] + ($circle['search_radius'] / $units_in_rad);

        $data['lat'] = $circle['center_lat'];
        $data['lon'] = $circle['center_lon'];

        $data['id'] = $this->CI->session->userdata('user_id');

        return $data;
    }

    /**
     * Get users markers
     *
     * @var array
     *
     * @return array $markers
     */
    public function getUsersMarkers($users = array())
    {
        $markers = array();
        if (!empty($users)) {
            foreach ($users as $user) {
                $markers[] = array(
                    'gid'     => $user['id'],
                    'country' => $user['country'],
                    'region'  => $user['region'],
                    'city'    => $user['city'],
                    'address' => $user['address'],
                    'lat'     => (float) $user['lat'],
                    'lon'     => (float) $user['lon'],
                    'info'    => $user['output_name'] . ", " . $user['age'],
                );
            }
        }

        return $markers;
    }

    /**
     * Return banner pages
     *
     * @return array
     */
    public function _bannerAvailablePages()
    {
        return array(
            array("link" => self::MODULE_GID . "/index", "name" => l(self::MODULE_GID, self::MODULE_GID)),
        );
    }

    /**
     * Return settings for seo
     *
     * @param string  $method  method name
     * @param integer $lang_id language identifier
     *
     * @return array
     */
    public function getSeoSettings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->_getSeoSettings($method, $lang_id);
        } else {
            $actions = array('index');
            $return = array();
            foreach ($actions as $action) {
                $return[$action] = $this->_getSeoSettings($action, $lang_id);
            }

            return $return;
        }
    }

    /**
     * Return settings for seo (internal)
     *
     * @param string  $method  method name
     * @param integer $lang_id language identifier
     *
     * @return array
     */
    public function _getSeoSettings($method, $lang_id = '')
    {
        switch ($method) {
            case 'index': {
                    return array(
                        'templates'   => array('nickname', 'fname', 'sname'),
                        'url_vars'    => array(),
                        'url_postfix' => array(),
                        'optional'    => array(),
                    );
                    break;
                }
        }
    }

    /**
     * Transform values of request data
     *
     * @param string $var_name_from variable name from request
     * @param string $var_name_to   variable name from method
     * @param string $value         variable value from request
     *
     * @return mixed
     */
    public function requestSeoRewrite($var_name_from, $var_name_to, $value)
    {
        if ($var_name_from == $var_name_to) {
            return $value;
        }

        show_404();
    }

    /**
     * Return data for generating xml sitemap
     *
     * @return array
     */
    public function getSitemapXmlUrls()
    {
        $this->CI->load->helper('seo');
        $return = array();

        return $return;
    }

    /**
     * Return data for generating sitemap page
     *
     * @return array
     */
    public function getSitemapUrls()
    {
        $this->CI->load->helper('seo');

        $block[] = array(
            'name'      => l('header_nearest_users_index', self::MODULE_GID),
            'link'      => rewrite_link(self::MODULE_GID, 'index'),
            'clickable' => true,
        );

        return $block;
    }
}
