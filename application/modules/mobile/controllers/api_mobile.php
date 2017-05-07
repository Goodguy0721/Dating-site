<?php

namespace Pg\Modules\Mobile\Controllers;

/**
 * Mobile version API controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Api_Mobile extends \Controller
{
    
    /**
     * Constructor
     *
     * @return Api_Mobile
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Pg_theme');
        $this->load->model('mobile/models/Mobile_model');
    }
    
    private function saveLang($lang_id = null)
    {
        if (!$lang_id) {
            $sess_lang_id = (int) $this->session->userdata('lang_id');
            if (!empty($sess_lang_id)) {
                $lang_id = $sess_lang_id;
            } else {
                $lang_id = $this->pg_language->get_default_lang_id();
            }
        }
        $this->session->set_userdata('lang_id', $lang_id);
        $this->session->sess_update();

        return $lang_id;
    }

    private function getAppUrls()
    {
        $this->load->model('Mobile_model');
        $settings = $this->Mobile_model->getSettings();

        return array(
            'android_url' => $settings['android_url'],
            'ios_url'     => $settings['ios_url'],
        );
    }

    private function getCssUrl()
    {
        $this->load->library('pg_theme');
        $theme_settings = $this->pg_theme->return_active_settings($this->pg_theme->get_current_theme_type());
        return $this->pg_theme->theme_default_url . $theme_settings['theme'] . '/sets/' . $theme_settings['scheme'] . '/css/';
    }

    private function getLogoPath()
    {
        $theme_data = $this->pg_theme->format_theme_settings($this->router->class);

        return $theme_data['mini_logo']['path'];
    }

    public function init($lang_id = null)
    {
        $this->load->model('Users_model');
        $this->load->model('Properties_model');
        $this->load->model('payments/models/Payment_currency_model');

        $saved_lang_id = $this->saveLang($lang_id);
        $user_id = intval($this->session->userdata('user_id'));

        $data = array(
            'data' => array(
                'cssUrl'             => $this->getCssUrl(),
                'siteUrl'            => site_url(),
                'appUrls'            => $this->getAppUrls(),
                'logo'               => $this->getLogoPath(),
                'services_add_money' => site_url() . 'users/account/update/',
            ),
            'modules' => array(
                'im'    => $this->pg_module->is_module_installed('im'),
                'likes' => $this->pg_module->is_module_installed('likes'),
            ),
            'l'          => $this->pg_language->pages->return_module('mobile', $saved_lang_id),
            'language'   => $this->pg_language->get_lang_by_id($saved_lang_id),
            'languages'  => $this->pg_language->languages,
            'userData'   => $this->Users_model->get_user_by_id($user_id, true, false),
            'properties' => array(
                'userTypes' => $this->Properties_model->get_property('user_type', $saved_lang_id),
                'age'       => array(
                    'min' => $this->pg_module->get_module_config('users', 'age_min'),
                    'max' => $this->pg_module->get_module_config('users', 'age_max'),
                ),
                'currency' => $this->Payment_currency_model->default_currency,
            ),
        );

        $this->load->model('Mobile_model');
        $data['l'] = $this->Mobile_model->langsReplace($data['l']);
        
        $data['not_editable_fields'] = $this->Users_model->fields_not_editable;
        if (!$this->pg_module->is_module_installed('perfect_match')) {
            $data['not_editable_fields'][] = 'looking_user_type';
            $data['not_editable_fields'][] = 'partner_age';
        }

        if (filter_input(INPUT_POST, 'is_app')) {
            $data['app_l'] = $this->pg_language->pages->return_module('mobile_app', $saved_lang_id);
            if (empty($data['app_l'])) {
                log_message('error', 'empty_app_langs');
            }
        }

        $data['data']['is_demo'] = DEMO_MODE;

        $this->set_api_content('data', $data);
    }

    public function changeLang()
    {
        $lang_id = filter_input(INPUT_POST, 'lang_id');
        if (!$lang_id) {
            log_message('error', 'languages API: Empty lang id');
            $this->set_api_content('error', l('api_error_empty_lang_id', 'languages'));

            return false;
        }
        $this->load->model('Properties_model');
        $this->load->model('Users_model');

        $save_data['lang_id'] = $lang_id;
        $this->Users_model->save_user(intval($this->session->userdata('user_id')), $save_data);

        $this->session->set_userdata('lang_id', $lang_id);
        $this->session->sess_update();
        $this->set_api_content('data', array(
            'language'   => $this->pg_language->get_lang_by_id($lang_id),
            'l'          => $this->pg_language->pages->return_module('mobile', $lang_id),
            'properties' => array(
                'userTypes' => $this->Properties_model->get_property('user_type', $lang_id),
            ),
        ));
    }

    public function getConfig()
    {
        // TODO: $this->pg_module->get_module_config($module, $gid);
    }
}
