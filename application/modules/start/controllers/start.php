<?php

namespace Pg\Modules\Start\Controllers;

use Pg\Libraries\View;

/**
 * Start user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Start extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
    }

    public function index($text_content_theme = 'index')
    {
        if ($this->session->userdata("auth_type") == "user" && $this->session->userdata('user_id')) {
            if (SOCIAL_MODE) {
                redirect(site_url() . 'start/homepage');
            } else {
                $this->load->model('Users_model');
                $this->Menu_model->breadcrumbs_set_parent('user-main-home-item');
                $this->view->assign('user_id', $this->session->userdata('user_id'));
                $this->view->render('homepage');
            }
        } else {
            $this->session->set_userdata('demo_user_type', 'user');

            $fixed_index_page = $this->pg_module->get_module_config('start', 'fixed_index_page');
            if ($fixed_index_page) {
                header('X-PJAX-Version: index-fixed');
                if (SOCIAL_MODE) {
                    $this->view->render('index_social');
                } else {
                    $template_name = 'index_pleasure';
                    
                    $colorset = $this->pg_theme->return_active_settings();
                    if(!empty($colorset)) {
                        $file_name = 'index_' . $colorset["user"]['scheme'];
                        $path = MODULEPATH . "start/views/" . $colorset['user']['theme'] . "/" . $file_name . '.' . $colorset['user']['template_engine'];
                        if(file_exists($path)) {
                            $template_name = $file_name;
                        }                        
                    }
                    
                    if ($text_content_theme != 'index') {
                        $seo_data_raw = $this->pg_seo->get_settings('user', 'start', $text_content_theme);
                        $seo_data['title'] = $seo_data_raw['meta_' . $this->pg_language->current_lang_id]['title'];
                        $seo_data['description'] = $seo_data_raw['meta_' . $this->pg_language->current_lang_id]['description'];
                        $seo_data['keyword'] = $seo_data_raw['meta_' . $this->pg_language->current_lang_id]['keyword'];
                        $seo_data['header'] = $seo_data_raw['meta_' . $this->pg_language->current_lang_id]['header'];
                        $seo_data['og_title'] = $seo_data_raw['og_' . $this->pg_language->current_lang_id]['og_title'];
                        $seo_data['og_type'] = $seo_data_raw['og_' . $this->pg_language->current_lang_id]['og_type'];
                        $seo_data['og_description'] = $seo_data_raw['og_' . $this->pg_language->current_lang_id]['og_description'];
                        $seo_data['noindex'] = $seo_data_raw['noindex'];
                        $this->pg_seo->set_seo_tags($seo_data);
                    }

                    $this->view->assign('header_features', l('header_features_' . $text_content_theme, 'start'));
                    
                    $this->view->assign('header_slogan', l('header_slogan_' . $text_content_theme, 'start'));
                    $this->view->assign('text_slogan', l('text_slogan_' . $text_content_theme, 'start'));
                    
                    $this->view->assign('header_subslogan_1', l('header_subslogan_1_' . $text_content_theme, 'start'));
                    $this->view->assign('text_subslogan_1', l('text_subslogan_1_' . $text_content_theme, 'start'));                    
                    $this->view->assign('header_subslogan_2', l('header_subslogan_2_' . $text_content_theme, 'start'));
                    $this->view->assign('text_subslogan_2', l('text_subslogan_2_' . $text_content_theme, 'start'));
                    $this->view->assign('header_madeby', l('header_madeby_' . $text_content_theme, 'start'));
                    $this->view->assign('text_madeby', l('text_madeby_' . $text_content_theme, 'start'));
                    
                    $this->view->assign('header_highlighted_1', l('header_highlighted_1_' . $text_content_theme, 'start'));
                    $this->view->assign('header_highlighted_2', l('header_highlighted_2_' . $text_content_theme, 'start'));
                    $this->view->assign('text_highlighted_1', l('text_highlighted_1_' . $text_content_theme, 'start'));
                    $this->view->assign('text_highlighted_2', l('text_highlighted_2_' . $text_content_theme, 'start'));
                    
                    $this->view->assign('header_community', l('header_community_' . $text_content_theme, 'start'));
                    $this->view->assign('text_community', l('text_community_' . $text_content_theme, 'start'));
                    $this->view->assign('header_ads', l('header_ads_' . $text_content_theme, 'start'));
                    $this->view->assign('text_ads', l('text_ads_' . $text_content_theme, 'start'));                         
                    
                    $this->view->assign('text_content_theme', $text_content_theme);
                                        
                    $this->view->assign('mobile_url', $this->config->site_url() . 'm');
                    $this->view->render($template_name);
                }
            } else {
                $this->view->render('index');
            }
        }
    }

    public function homepage()
    {
        $this->load->model('Users_model');
        $this->Menu_model->breadcrumbs_set_parent('user-main-home-item');
        $this->view->assign('user_id', $this->session->userdata('user_id'));
        $this->view->render('homepage');
    }

    public function error()
    {
        header("HTTP/1.0 404 Not Found");
        $this->Menu_model->breadcrumbs_set_active(l('header_error', 'start'));
        $this->view->assign('header_type', 'error_page');
        $this->view->render('error');
    }

    public function print_version()
    {
        echo $this->pg_module->get_module_config('start', 'product_version');
    }

    // test methods
    public function test_file_upload()
    {
        $this->load->model("file_uploads/models/File_uploads_config_model");

        $configs = $this->File_uploads_config_model->get_config_list();
        $this->view->assign('configs', $configs);

        if ($this->input->post('btn_save') && $this->input->post('config')) {
            $config = $this->input->post('config');
            $file_name = 'file';

            if (isset($_FILES[$file_name]) && is_array($_FILES[$file_name]) && is_uploaded_file($_FILES[$file_name]["tmp_name"])) {
                $this->load->model("File_uploads_model");
                $return = $this->File_uploads_model->upload($config, '', $file_name);

                if (!empty($return["errors"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $return["errors"]);
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, $return["file"]);
                }
            }
        }

        $this->view->render('test_file_upload');
    }

    public function demo($type = 'user')
    {
        $this->session->set_userdata('demo_user_type', $type);
        redirect();
    }

    public function ajax_backend()
    {
        $data = (array) $this->input->post('data');
        $user_session_id = ($this->session->userdata('auth_type') == 'user') ? intval($this->session->userdata('user_id')) : 0;
        $return_arr['user_session_id'] = $user_session_id;
        foreach ($data as $gid => $params) {
            $return_arr[$gid] = array();
            if (
                !(empty($params['module']) && empty($params['model']) && empty($params['method'])) && $this->pg_module->is_module_installed($params['module']) && $this->load->model($params['module'] . '/models/' . $params['model'], $gid . '_backend_model', false, true, true) && method_exists($this->{$gid . '_backend_model'}, 'backend_' . $params['method'])
            ) {
                $return_arr[$gid] = $this->{$gid . '_backend_model'}->{'backend_' . $params['method']}($params);
                $return_arr[$gid]['user_session_id'] = $user_session_id;
            }
        }

        $this->view->assign($return_arr);

        return;
    }

    public function multi_request_script()
    {
        $js = file_get_contents(APPPATH . 'modules/friendlist/js/friendlist_multi_request.js');
        echo $js;
    }

    public function aclCheck()
    {
        $url_data = explode('/', filter_input(INPUT_POST, 'url_data'));
        $module = $url_data[0];
        $module = $url_data[1];
        $errors = [];
        if (empty($module)) {
            $errors[] = 'Empty module';
        }
        if (empty($action)) {
            $errors[] = 'Empty action';
        }
        if (empty($errors)) {
            $allowed = $this->acl->check(new \Pg\Libraries\Acl\Action\ViewPage(
                new \Pg\Libraries\Acl\Resource\Page(
                    ['module' => $module, 'controller' => $module, 'action' => $action,]
                )
            ), false);
        } else {
            $allowed = false;
        }
        $this->view->assign(View::MSG_ERROR, $errors);
        $this->view->assign('is_allowed', $allowed);
        $this->view->render();
    }
    
    public function sendAnalytics()
    {
        $category = $this->input->post('category');
        $gid = $this->input->post('gid');

        $this->load->library('Analytics');
        $this->analytics->ajaxTrack($category, $gid);
    }

}
