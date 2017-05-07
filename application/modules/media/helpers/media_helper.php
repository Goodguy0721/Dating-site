<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('media_block')) {
    function media_block($params)
    {
        $CI = &get_instance();

        $location_base_url = !empty($params['location_base_url']) ? $params['location_base_url'] : '';
        $param = $params['param'] ? $params['param'] : 'all';
        $page = $params['page'] ? $params['page'] : 1;
        if (!empty($params['user_id'])) {
            $user_id = $params['user_id'];
            $is_owner = false;
        } else {
            $user_id = $CI->session->userdata('user_id');
            $is_owner = $user_id ? true : false;
        }

        $CI->load->model('Media_model');

        $media_sorter = array(
            "order"     => !empty($params['order']) ? $params['order'] : 'date_add',
            "direction" => !empty($params['direction']) ? $params['direction'] : 'DESC',
            "links"     => array(
                "date_add"       => l('field_date_add', 'media'),
                "views"          => l('field_views', 'media'),
                "comments_count" => l('field_comments_count', 'media'),
            ),
        );
        $order_by = array($media_sorter['order'] => $media_sorter['direction']);

        $CI->view->assign('is_owner', $is_owner);
        $CI->view->assign('media_sorter', $media_sorter);
        $CI->view->assign('profile_section', 'gallery');
        $CI->view->assign('gallery_param', $param);
        $CI->view->assign('page', $page);
        if ($param == 'albums') {
            $albums = $CI->Media_model->get_albums($user_id, $page);
            $albums['albums_select'] = $CI->Media_model->get_albums_select($user_id);
            $CI->view->assign('content', $albums);
        } else {
            $CI->view->assign('content', $CI->Media_model->get_list($user_id, $param, $page, 0, true, $order_by));
        }
        $CI->view->assign('albums', $CI->Media_model->get_albums_select($user_id));
        $CI->view->assign('id_user', $user_id);

        if (is_callable($location_base_url)) {
            $media_filters = array(
                'all'       => array('link' => $location_base_url('all', l('all', 'media')), 'name' => l('all', 'media')),
                'photo'     => array('link' => $location_base_url('photo', l('photo', 'media')), 'name' => l('photo', 'media')),
                'video'     => array('link' => $location_base_url('video', l('video', 'media')), 'name' => l('video', 'media')),
                'audio'     => array('link' => $location_base_url('audio', l('audio', 'audio_uploads')), 'name' => l('audio', 'audio_uploads')),
                'albums'    => array('link' => $location_base_url('albums', l('albums', 'media')), 'name' => l('albums', 'media')),
                'favorites' => array('link' => $location_base_url('favorites', l('favorites', 'media')), 'name' => l('favorites', 'media')),
            );
        } else {
            $media_filters = array(
                'all'       => array('link' => $location_base_url . '/all', 'name' => l('all', 'media')),
                'photo'     => array('link' => $location_base_url . '/photo', 'name' => l('photo', 'media')),
                'video'     => array('link' => $location_base_url . '/video', 'name' => l('video', 'media')),
                'audio'     => array('link' => $location_base_url . '/audio', 'name' => l('audio', 'audio_uploads')),
                'albums'    => array('link' => $location_base_url . '/albums', 'name' => l('albums', 'media')),
                'favorites' => array('link' => $location_base_url . '/favorites', 'name' => l('favorites', 'media')),
            );
        }

        $audio_uploads = $CI->pg_module->is_module_installed('audio_uploads');
        if (!$audio_uploads) {
            unset($media_filters['audio']);
        }

        if (!isset($params['filters'])) {
            $params['filters'] = $media_filters;
        }

        $CI->view->assign('audio_uploads', $audio_uploads);
        $CI->view->assign('media_filters', $params['filters']);
        $CI->view->render('user_gallery', 'user', 'media');
    }
}

if (!function_exists('media_carousel')) {
    function media_carousel($params)
    {
        $CI = &get_instance();

        $data['header'] = !empty($params['header']) ? $params['header'] : '';
        $data['media'] = $params['media'];
        $data['carousel']['media_count'] = count($params['media']);
        $data['rand'] = $data['carousel']['rand'] = rand(1, 999999);
        $data['carousel']['visible'] = !empty($params['visible']) ? intval($params['visible']) : 3;
        $data['carousel']['scroll'] = (!empty($params['scroll']) && $params['scroll'] != 'auto') ? intval($params['scroll']) : 'auto';
        $data['carousel']['class'] = !empty($params['class']) ? $params['class'] : '';
        $data['carousel']['thumb_name'] = !empty($params['thumb_name']) ? $params['thumb_name'] : 'middle';
        if (!$data['carousel']['scroll']) {
            $data['carousel']['scroll'] = 1;
        }

        $CI->view->assign('media_carousel_data', $data);

        return $CI->view->fetch('helper_media_carousel', 'user', 'media');
    }
}

if (!function_exists('user_media_block')) {
    function user_media_block($params)
    {
        $CI = &get_instance();
        $CI->load->model('Media_model');

        $user_id = intval($params['user_id']);

        if (!empty($user_id)) {
            $where = array();
            $where['where'] = array('id_user' => $user_id);
            $where['where_in']['upload_gid'][] = $CI->Media_model->file_config_gid;
            $where['where_in']['upload_gid'][] = $CI->Media_model->video_config_gid;
            if ($CI->pg_module->is_module_installed("audio_uploads")) {
                $where['where_in']['upload_gid'][] = $CI->Media_model->audio_config_gid;
            }

            $data = $CI->Media_model->get_recent_media($params['count'], null, $where);
            $data['id_user'] = $user_id;
            if (!empty($data['media'])) {
                if (empty($params['media_size'])) {
                    $media_count = 16 - count($data['media']);
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

                $current_user = $CI->session->userdata('user_id');
                $gallery_link = site_url() . "users/view/{$user_id}/gallery";
                if ($current_user == $user_id) {
                    $gallery_link = site_url() . 'users/profile/gallery';
                }

                $CI->view->assign('gallery_link', $gallery_link);
                $CI->view->assign('user_media_count', $data['media_count']);
                $CI->view->assign('recent_photos_data', $data);
                $CI->view->assign('recent_thumb', $recent_thumb);
                $CI->view->assign('id_user', $user_id);
                $CI->view->assign('user_media_block', $CI->view->fetch('user_media_block', 'user', 'media'));

                return $CI->view->fetch('helper_user_media_block', 'user', 'media');
            }
        }

        return false;
    }
}

if (!function_exists('events_media_block')) {
    function events_media_block($params)
    {
        $CI = &get_instance();
        $CI->load->model('Media_model');
        $CI->load->model('Events_model');
        $CI->Media_model->initialize($CI->Events_model->album_type);

        $album_id = intval($params['album_id']);

        if (!empty($album_id)) {
            $where = array();
            $where['where_in']['upload_gid'][] = $CI->Media_model->file_config_gid;
            $where['where_in']['upload_gid'][] = $CI->Media_model->video_config_gid;
            
            $CI->load->model('media/models/Media_album_model');
            $media_count = $CI->Media_album_model->get_album_media_count($album_id);

            $data = $CI->Media_model->get_gallery_list($params['count'], 'events', null, $album_id, array('date_add' => 'DESC'), $where);
            if (!empty($data['media'])) {
                if (empty($params['media_size'])) {
                    $media_count = 16 - count($data['media']);
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
                $CI->view->assign('recent_thumb', $recent_thumb);
            }

            $CI->view->assign('album_id', $album_id);
            $CI->view->assign('recent_photos_data', $data);
            $CI->view->assign('event_media_count', $media_count);
            $CI->view->assign('event_media_block', $CI->view->fetch('event_media_block', 'user', 'media'));
        }

        return $CI->view->fetch('helper_event_media_block', 'user', 'media');
    }
}

if (!function_exists('recent_media_block')) {
    function recent_media_block($params)
    {
        $CI = &get_instance();
        $CI->load->model('Media_model');
        $data = $CI->Media_model->get_recent_media($params['count'], $params['upload_gid']);
        if (!empty($data['media'])) {
            $media_count = 16 - count($data['media']);
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
            $CI->view->assign('recent_photos_data', $data);
            $CI->view->assign('recent_thumb', $recent_thumb);

            return $CI->view->fetch('helper_recent_media', 'user', 'media');
        }

        return false;
    }
}

if (!function_exists('get_albums_for_media')) {
    function get_albums_for_media($params)
    {
        $CI = &get_instance();
        $CI->load->model('Media_model');
        $CI->load->model('media/models/Media_album_model');
        $CI->load->model('media/models/Albums_model');

        $media_id = $params['id'];
        $user_id = $params['user_id'];
        $section = trim($params['section']);
        $media = $CI->Media_model->get_media_by_id($media_id);
        $is_access_permitted = $CI->Media_model->is_access_permitted($media_id, $media);
        $is_user_media_user = ($media['id_user'] == $user_id);
        $is_user_media_owner = ($media['id_owner'] == $user_id);
        $CI->view->assign('is_user_media_owner', $is_user_media_owner);
        $CI->view->assign('is_user_media_user', $is_user_media_user);
        $CI->view->assign('is_access_permitted', $is_access_permitted);
        $CI->view->assign('media', $media);
        $albums = array();

        if ($is_user_media_user) {
            $media_in_album = $CI->Media_album_model->get_albums_by_media_id($media_id);
        } elseif ($is_access_permitted) {
            $media_parent_id = $media['id_parent'] ? $media['id_parent'] : $media_id;
            $parent_ids = $CI->Media_model->get_parent_media_list_ids($media_parent_id);
            $media_in_album = $CI->Media_album_model->get_albums_by_media_id(array_merge($parent_ids, (array) $media_id, (array) $media_parent_id));
        }

        $param['where']['id_user'] = $user_id;
        $param["where"]["id_album_type"] = $CI->Media_model->album_type_id;
        $albums['user'] = ($user_id) ? $CI->Albums_model->get_albums_list($param) : array();
        $param['where']['id_user'] = '0';
        $albums['common'] = $CI->Albums_model->get_albums_list($param, null, null, null, null, true, $CI->pg_language->current_lang_id);

        $CI->view->assign('media_albums', $albums);
        $CI->view->assign('media_in_album', $media_in_album);

        return $CI->view->fetch('section_albums', 'user', 'media');
    }
}

if (!function_exists('media_add_photo')) {
    function media_add_photo(array $params = array())
    {
        $ci = &get_instance();

        $ci->view->assign('user', array('id' => $ci->session->userdata('user_id')));

        return $ci->view->fetch('helper_add_photo', 'user', 'media');
    }
}
