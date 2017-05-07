<?php
/**
 * Clubs module
 *
 * @package     PG_Dating
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
 
use Pg\Modules\Clubs\Models;

/**
 * Clubs helper
 * 
 * @package     PG_Dating
 * @subpackage  Clubs
 * @category    helpers
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php datingDoc software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */

if (!function_exists('mediaBlock')) {
    function mediaBlock($params = [])
    {
        $ci = &get_instance();
        $ci->load->model('clubs/models/Clubs_media_model');

        $helper_mblock_data = [];
        $helper_mblock_data['club_id'] = intval($params['club_id']);
        $helper_mblock_data['upload_gid'] = isset($params['upload_gid']) ? $params['upload_gid'] : false;

        if (!empty($helper_mblock_data['club_id'])) {
            $filters = [
                'club_id'  => $helper_mblock_data['club_id'],
                'is_active' => 1,
            ];

            if (!empty($helper_mblock_data['upload_gid'])) {
                $filters['upload_gid'] = $helper_mblock_data['upload_gid'];
            }

            $helper_mblock_data['all_count']  = $ci->Clubs_media_model->getCount($filters);
            $helper_mblock_data['media_list'] = $ci->Clubs_media_model->formatArray(
                    $ci->Clubs_media_model->getList($filters, 1, $params['count'], ['date_add' => 'DESC']));
            if (!empty($helper_mblock_data['media_list'])) {
                if (empty($params['media_size'])) {
                    $media_count = 16 - count($helper_mblock_data['media_list']);
                    switch ($media_count) {
                        case 13: $recent_thumb['name'] = 'middle';
                            $recent_thumb['width'] = '82px';
                            break;
                        case 14: $recent_thumb['name'] = 'big';
                            $recent_thumb['width'] = '125px';
                            break;
                        case 15: $recent_thumb['name'] = 'great';
                            $recent_thumb['width'] = '255px';
                            break;
                        default: $recent_thumb['name'] = 'small';
                            $recent_thumb['width'] = '60px';
                    }
                } else {
                    switch ($params['media_size']) {
                        case 'middle': $recent_thumb['name'] = 'middle';
                            $recent_thumb['width'] = '82px';
                            break;
                        case 'big': $recent_thumb['name'] = 'big';
                            $recent_thumb['width'] = '125px';
                            break;
                        case 'great': $recent_thumb['name'] = 'great';
                            $recent_thumb['width'] = '255px';
                            break;
                        default: $recent_thumb['name'] = 'small';
                            $recent_thumb['width'] = '60px';
                    }
                }

                $helper_mblock_data['recent_thumb'] = $recent_thumb;
                $ci->view->assign('helper_mblock_data', $helper_mblock_data);

                $ci->view->assign('media_block', $ci->view->fetch('media_block', 'user', 'clubs'));

                return $ci->view->fetch('helper_media_block', 'user', 'clubs');
            }
        }

        return false;
    }
}

if (!function_exists('userClubsBlock')) {
    function userClubsBlock($params = [])
    {
        $ci = &get_instance();
        $ci->load->model('clubs/models/Clubs_users_model');

        if ($ci->session->userdata('auth_type') != 'user' || empty($params['user_id'])) {
            return '';
        }

        $tpl_vars = $params;
        $user_clubs = $ci->Clubs_users_model->getList(['user_id' => $params['user_id'], 'not_ended' => 1]);
        $clubs_ids_arr = [];
        if (!empty($user_clubs)) {
            foreach ($user_clubs as $value) {
                $clubs_ids_arr[] = $value['club_id'];
            }
        }

        if (!empty($clubs_ids_arr)) {
            $ci->load->model('Clubs_model');
            $tpl_vars['clubs'] = $ci->Clubs_model->getList(['id' => $clubs_ids_arr]);

            $ci->view->assign('helper_user_clubs', $tpl_vars);
            return $ci->view->fetch('helper_user_clubs_block', 'user', 'clubs');
        }

        return '';
    }
}