<?php

namespace Pg\Modules\Media\Controllers;

/**
 * Media controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Mikhail Chernov <mchernov@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (РЎСЂ, 02 Р°РїСЂ 2010) $ $Author: mchernov $
 * */
class Media extends \Controller
{
    private $user_id = false;

    public function __construct()
    {
        parent::__construct();
        $this->load->model("Media_model");
        $this->user_id = intval($this->session->userdata('user_id'));
    }

    public function index($param = 'all', $index = true)
    {
        $param = trim(strip_tags($param));
        $order = trim(strip_tags($this->input->get_post('order', true)));
        $direction = trim(strip_tags($this->input->get_post('direction', true)));
        $media_sorter = array(
            "order"     => $order ? $order : 'date_add',
            "direction" => $direction ? $direction : 'DESC',
            "links"     => array(
                "date_add"       => l('field_date_add', 'media'),
                "views"          => l('field_views', 'media'),
                "comments_count" => l('field_comments_count', 'media'),
            ),
        );
        $order_by = array($media_sorter['order'] => $media_sorter['direction']);
        $is_guest = $this->session->userdata("auth_type") != "user";

        $this->load->model('Menu_model');
        $this->load->helper('seo');
        $this->Menu_model->breadcrumbs_set_active(l('header_gallery', 'media'), rewrite_link('media', ''));
        $this->Menu_model->breadcrumbs_set_active(l($param, 'media'));

        $seo_data = array();

        if ($index) {
            $this->router->set_method($param);
            $seo_data['canonical'] = rewrite_link('media', $param, $seo_data, false, null, true);
        }

        $media_filters = array(
            'all'       => array('link' => rewrite_link('media', 'all'), 'name' => l('all', 'media')),
            'photo'     => array('link' => rewrite_link('media', 'photo'), 'name' => l('photo', 'media')),
            'video'     => array('link' => rewrite_link('media', 'video'), 'name' => l('video', 'media')),
            'audio'     => array('link' => rewrite_link('media', 'audio'), 'name' => l('audio', 'media')),
            'albums'    => array('link' => rewrite_link('media', 'albums'), 'name' => l('albums', 'media')),
        );
        $audio_uploads = $this->pg_module->is_module_installed('audio_uploads');
        if(!$audio_uploads){
            unset($media_filters['audio']);
        }
        $this->view->assign('media_filters', $media_filters);

        $this->pg_seo->set_seo_data($seo_data);

        $this->view->assign('gallery_param', $param);
        $this->view->assign('media_sorter', $media_sorter);
        $this->view->assign('order_by', $order_by);
        $this->view->assign('albums', $this->Media_model->get_albums_select(0));
        $this->view->assign("is_guest", $is_guest);
        $this->view->render('gallery');
    }

    public function video()
    {
        $this->index('video', false);
    }

    public function photo()
    {
        $this->index('photo', false);
    }

    public function audio()
    {
        $this->index('audio', false);
    }

    public function albums()
    {
        $this->index('albums', false);
    }

    public function favorites()
    {
        $this->index('favorites', false);
    }

    public function all()
    {
        $this->index('all', false);
    }

    public function ajax_get_gallery_list($param = 'all', $album_id = 0)
    {
        $album_id = intval($album_id);
        $param = trim(strip_tags($param));
        $order = trim(strip_tags($this->input->post('order')));
        $direction = trim(strip_tags($this->input->post('direction')));
        if (!$order) {
            $order = 'date_add';
        }
        $order_by[$order] = ($direction == 'asc') ? 'ASC' : 'DESC';
        $page = intval($this->input->post('page', true));

        if ($param == 'albums' && !$album_id) {
            $gallery = $this->Media_model->get_albums(0, $page);
        } else {
            $icons = $this->input->post('icons', true);
            $loaded_count = intval($this->input->post('loaded_count', true));
            $this->Media_model->format_likes_count = true;
            $gallery = $this->Media_model->get_gallery_list(count($icons), $param, $loaded_count, $album_id, $order_by);
            $this->Media_model->format_likes_count = false;
        }
        if (!empty($gallery['media'])) {
            foreach ($gallery['media'] as $i => $photo) {
                $gallery['media'][$i]['alt'] = l('text_media_photo', 'media', null, 'button', $photo);
            }
        }
        $this->view->assign($gallery);
        $this->view->render();
    }

    public function ajax_get_gallery_render_list($param = 'all', $album_id = 0)
    {
        $return = array('content' => '');

        $album_id = intval($album_id);
        $param = trim(strip_tags($param));
        $order = trim(strip_tags($this->input->post('order')));
        $direction = trim(strip_tags($this->input->post('direction')));
        if (!$order) {
            $order = 'date_add';
        }
        $order_by[$order] = ($direction == 'asc') ? 'ASC' : 'DESC';
        $page = intval($this->input->post('page', true));

        if ($param == 'albums' && !$album_id) {
            $gallery = $this->Media_model->get_albums(0, $page);
        } else {
            $icons = $this->input->post('icons', true);
            $loaded_count = intval($this->input->post('loaded_count', true));
            $this->Media_model->format_likes_count = true;
            $gallery = $this->Media_model->get_gallery_list(count($icons), $param, $loaded_count, $album_id, $order_by);
            $this->Media_model->format_likes_count = false;
        }
        if (!empty($gallery['media'])) {
            foreach ($gallery['media'] as $i => $photo) {
                $gallery['media'][$i]['alt'] = l('text_media_photo', 'media', null, 'button', $photo);
            }
        }
        $return['gallery'] = $gallery;

        $line_data = $this->input->post('line_data', true);
        if (!empty($line_data)) {
            $num = 0;
            $new_gallery = array();
            foreach ($line_data as $key => $line) {
                $media_item = isset($gallery['media'][0]) ? $gallery['media'][0] : '';
                if ($media_item) {
                    if (!isset($line['end_block'])) {
                        $new_gallery[$key]['width'] = $line['width'];
                        for ($i = 0; $i < $line['count']; ++$i) {
                            if ($media_item) {
                                $new_gallery[$key]['items'][$num] = array_shift($gallery['media']);
                                ++$num;
                            } else {
                                break 2;
                            }
                        }
                    } else {
                        $new_gallery[$key]['end_block'] = 1;
                    }
                }
            }
            $this->view->assign('media', $new_gallery);
            $this->view->assign('icons', $icons);
            $return['content'] = $this->view->fetchFinal('media_all');
        }

        $this->view->assign($return);
        $this->view->render();
    }

    private function _add_media_tpl($type)
    {
        $id_album = intval($this->input->post('id_album'));
        $this->load->model('media/models/Albums_model');
        $params['where']['id_user'] = $this->user_id;
        $params["where"]["id_album_type"] = $this->Media_model->album_type_id;
        $user_albums = $this->Albums_model->get_albums_list($params);
        $this->view->assign('id_album', $id_album);
        $this->view->assign('user_albums', $user_albums);

        switch ($type) {
            case 'video':
                $this->load->model('Video_uploads_model');
                $media_config = $this->Video_uploads_model->get_config($this->Media_model->video_config_gid);
                $this->view->assign('media_config', $media_config);
                $tpl = $this->view->fetch('add_video');
                break;
            case 'audio':
                if ($this->pg_module->is_module_installed('audio_uploads')) {
                    $this->load->model('Audio_uploads_model');
                    $media_config = $this->Audio_uploads_model->get_config($this->Media_model->audio_config_gid);
                    $this->view->assign('media_config', $media_config);
                    $tpl = $this->view->fetch('add_audio');
                } else {
                    $tpl = '';
                }
                break;
            case 'image':
            default:
                $this->load->model('Uploads_model');
                $media_config = $this->Uploads_model->get_config($this->Media_model->file_config_gid);
                $this->view->assign('media_config', $media_config);
                $tpl = $this->view->fetch('add_photos');
                break;
        }

        return $tpl;
    }

    public function ajax_add_images()
    {
        $this->view->output($this->_add_media_tpl('image'));
        $this->view->render();
    }

    public function ajax_add_video()
    {
        $this->view->output($this->_add_media_tpl('video'));
        $this->view->render();
    }

    public function ajax_add_audio()
    {
        $this->view->output($this->_add_media_tpl('audio'));
        $this->view->render();
    }

    public function ajax_view_media($id, $user_id, $param = 'all', $album_id = 0)
    {
        $id = intval($id);
        $user_id = intval($user_id);
        $album_id = intval($album_id);
        $param = trim(strip_tags($param));
        $order = trim(strip_tags($this->input->post('order')));
        $direction = trim(strip_tags($this->input->post('direction')));
        if (!$order) {
            $order = 'date_add';
        }
        $order_by[$order] = ($direction == 'asc') ? 'ASC' : 'DESC';
        $filter_duplicate = intval($this->input->post('filter_duplicate', true));

        $media = $this->Media_model->get_media_by_id($id);

        $gallery_name = $this->input->post('gallery_name', true) ? trim(strip_tags($this->input->post('gallery_name', true))) : 'mediagallery';
        $is_access_permitted = $this->Media_model->is_access_permitted($id, $media);
        $selections = array();
        $this->load->model('Uploads_model');
        $upload_config = $this->Uploads_model->get_config($this->Media_model->file_config_gid);
        foreach ($upload_config['thumbs'] as $thumb_config) {
            $selections[$thumb_config['prefix']] = array(
                'width'  => $thumb_config['width'],
                'height' => $thumb_config['height'],
            );
        }
        $this->view->assign('selections', $selections);
        $this->view->assign('gallery_name', $gallery_name);
        $this->view->assign('media_id', $id);
        $this->view->assign('param', $param);
        $this->view->assign('album_id', $album_id);
        $this->view->assign('rand', rand(0, 999999));
        $this->view->output($this->view->fetchFinal('view_media'));
        $this->view->render();
    }

    public function ajax_edit_album($id)
    {
        $this->load->model('media/models/Albums_model');
        if (!$this->Albums_model->is_user_album_owner($id)) {
            $this->view->assign('error');

            return;
        }
        $album = $this->Albums_model->get_album_by_id($id);
        $this->view->assign('album', $album);
        $this->view->render('edit_album');
    }

    public function ajax_get_section_content()
    {
        $return = array('content' => '');
        $media_id = intval($this->input->post('id'));
        $section = trim($this->input->post('section'));
        $media = $this->Media_model->get_media_by_id($media_id);
        $is_access_permitted = $this->Media_model->is_access_permitted($media_id, $media);
        $is_user_media_user = ($media['id_user'] == $this->user_id);
        $is_user_media_owner = ($media['id_owner'] == $this->user_id);
        $this->view->assign('is_user_media_owner', $is_user_media_owner);
        $this->view->assign('is_user_media_user', $is_user_media_user);
        $this->view->assign('is_access_permitted', $is_access_permitted);
        $this->view->assign('media', $media);
        $this->_to_favorites($media);
        switch ($section) {
            case 'albums': {
                    $this->load->model('media/models/Media_album_model');
                    $this->load->model('media/models/Albums_model');
                    $albums = array();
                    if ($is_user_media_user) {
                        $media_in_album = $this->Media_album_model->get_albums_by_media_id($media_id);
                    } elseif ($is_access_permitted) {
                        $media_parent_id = $media['id_parent'] ? $media['id_parent'] : $media_id;
                        $parent_ids = $this->Media_model->get_parent_media_list_ids($media_parent_id);
                        $media_in_album = $this->Media_album_model->get_albums_by_media_id(array_merge($parent_ids, (array) $media_id, (array) $media_parent_id));
                    }
                    $param['where']['id_user'] = $this->user_id;
                    $param["where"]["id_album_type"] = $this->Media_model->album_type_id;
                    $albums['user'] = ($this->user_id) ? $this->Albums_model->get_albums_list($param) : array();
                    $param['where']['id_user'] = '0';
                    $albums['common'] = $this->Albums_model->get_albums_list($param, null, null, null, null, true, $this->pg_language->current_lang_id);

                    $this->view->assign('media_albums', $albums);
                    $this->view->assign('media_in_album', $media_in_album);

                    $return['content'] = $this->view->fetch('section_albums');
                    break;
                }
        }
        $this->view->assign($return);
        $this->view->render();
    }

    private function _to_favorites($media)
    {
        $this->load->model('media/models/Albums_model');
        $this->load->model('media/models/Media_album_model');
        $media_parent_id = $media['id_parent'] ? $media['id_parent'] : $media['id'];
        $parent_ids = $this->Media_model->get_parent_media_list_ids($media_parent_id);
        $albums = $this->Media_album_model->get_albums_by_media_id($parent_ids);
        $default_album = $this->Albums_model->get_default($this->user_id);
        $this->view->assign('default_album', $default_album);
        $this->view->assign('in_favorites', in_array($default_album['id'], $albums));
    }

    public function ajax_get_media_content($media_id, $gallery_param = 'all', $album_id = 0)
    {
        $return = array('content' => '', 'position_info' => '', 'media_type' => '', 'views_num' => '');
        $media_id = intval($media_id);
        $album_id = intval($album_id);
        $place = trim(strip_tags($this->input->post('place', true)));
        $gallery_param = trim(strip_tags($gallery_param));
        $without_position = intval($this->input->post('without_position'));

        $order = trim(strip_tags($this->input->post('order')));
        $direction = trim(strip_tags($this->input->post('direction')));
        if (!$order) {
            $order = 'date_add';
        }
        $order_by[$order] = ($direction == 'asc') ? 'ASC' : 'DESC';
        $filter_duplicate = ($place == 'site_gallery') ? 1 : intval($this->input->post('filter_duplicate', true));

        $media = $this->Media_model->get_media_by_id($media_id, true, true);
        $user_id = ($place == 'site_gallery') ? 0 : $media['id_user'];
        $is_user_media_owner = ($media['id_owner'] == $this->user_id);
        $is_user_media_user = ($media['id_user'] == $this->user_id);
        $is_access_permitted = $this->Media_model->is_access_permitted($media_id, $media);
        $date_formats['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        $date_formats['date_time_format'] = $this->pg_date->get_format('date_time_literal', 'st');

        if ($is_access_permitted) {
            $this->Media_model->increment_media_views($media_id);
            $this->view->assign('media', $media);
            $return['views_num'] = $media['views'] + 1;
        }

        $this->_to_favorites($media);

        $this->view->assign('is_user_media_owner', $is_user_media_owner);
        $this->view->assign('is_user_media_user', $is_user_media_user);
        $this->view->assign('is_access_permitted', $is_access_permitted);
        $this->view->assign('date_formats', $date_formats);

        $aviary_post_data = array(
            'type'    => $media['type_id'],
            'id'      => $media_id,
            'user_id' => $user_id,
        );
        $this->view->assign('aviary_post_data', $aviary_post_data);

        $this->view->assign('responder_id', $media['id_user']);

        $this->view->assign('user_id', $this->user_id);

        $this->view->assign('responder_id', $media['id_user']);

        $rand_param = intval($this->input->post('rand_param'));
        if ($rand_param) {
            $this->view->assign('vers', '?' . $rand_param);
        }

        $return['content'] = $this->view->fetchFinal('media_content');

        if (!$without_position) {
            $return['position_info'] = $this->Media_model->get_media_position_info($media_id, $gallery_param, $album_id, $user_id, true, $order_by, $filter_duplicate);
        }

        $return['media_type'] = $media['upload_gid'];

        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_add_media_in_album($media_id, $album_id)
    {
        $return = array('errors' => '', 'status' => 0);
        $media_id = intval($media_id);
        $album_id = intval($album_id);

        $media = $this->Media_model->get_media_by_id($media_id);
        if (!$this->Media_model->is_access_permitted($media_id, $media)) {
            $this->view->assign($return);

            return;
        }
        $this->load->model('media/models/Media_album_model');
        $this->load->model('media/models/Albums_model');

        $album = $this->Albums_model->get_album_by_id($album_id);
        if (!$album) {
            $this->view->assign($return);

            return;
        }
        $is_album_owner = ($album['id_user'] == $this->user_id);
        $is_common_album = ($album['id_user'] == 0);

        if ($media['id_owner'] == $this->user_id && $media['id_parent'] == 0) { //user is media owner && user
            if ($is_album_owner || $is_common_album) {
                $add_status = $this->Media_album_model->add_media_in_album($media_id, $album_id);
            }
        } elseif ($media['id_owner'] == $this->user_id && $media['id_parent'] != 0) { //user is media owner && !user
            if ($is_album_owner || $is_common_album) {
                $add_status = $this->Media_album_model->add_media_in_album($media['id_parent'], $album_id); //original media
            }
        } elseif ($media['id_owner'] != $this->user_id && $media['id_user'] == $this->user_id) { //user is media user && !owner
            if ($is_album_owner) {
                $add_status = $this->Media_album_model->add_media_in_album($media_id, $album_id);
            }
        } elseif ($media['id_owner'] != $this->user_id && $media['id_user'] != $this->user_id && $media['id_parent'] != 0) { //foreign media on foreign gallery
            if ($is_album_owner) {
                $param['where']['id_user'] = $this->user_id;
                $param['where']['id_parent'] = $media['id_parent'];
                $m = $this->Media_model->get_media(null, null, null, $param);
                if (count($m)) {
                    $add_status = $this->Media_album_model->add_media_in_album($m[0]['id'], $album_id);
                } else {
                    $new_media_id = $this->Media_model->copy_media($media_id);
                    $add_status = $this->Media_album_model->add_media_in_album($new_media_id, $album_id);
                }
            }
        } else {
            if ($is_album_owner) {
                $param['where']['id_user'] = $this->user_id;
                $param['where']['id_parent'] = $media_id;
                $m = $this->Media_model->get_media(null, null, null, $param);
                if (count($m)) {
                    $add_status = $this->Media_album_model->add_media_in_album($m[0]['id'], $album_id);
                } else {
                    $new_media_id = $this->Media_model->copy_media($media_id);
                    $add_status = $this->Media_album_model->add_media_in_album($new_media_id, $album_id);
                }
            }
        }
        if (!empty($add_status['status'])) {
            $return['status'] = 1;
        } else {
            $return['status'] = 0;
            $return['errors'] = !empty($add_status['error']) ? $add_status['error'] : l('error_add_in_ablum', 'media');
        }
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_delete_media_from_album($media_id, $album_id)
    {
        $return = array('errors' => '', 'status' => 0);
        $this->load->model('media/models/Albums_model');
        $album = $this->Albums_model->get_album_by_id($album_id);
        if (!$album) {
            $this->view->assign($return);

            return;
        }
        $is_album_owner = ($album['id_user'] == $this->user_id);
        $is_common_album = ($album['id_user'] == 0);

        if ($is_album_owner || $is_common_album) {
            $this->load->model('media/models/Media_album_model');
            $this->load->model('Media_model');
            $is_user_media_user = $this->Media_model->is_user_media_user($media_id);
            if ($is_user_media_user) {
                $this->Media_album_model->delete_media_from_album($media_id, $album_id);
            } else {
                $media = $this->Media_model->get_media_by_id($media_id);
                if (!$media) {
                    $this->view->assign($return);

                    return;
                }
                if ($media['id_parent']) {
                    $media_id = $media['id_parent'];
                }
                $params['where']['id_parent'] = $media_id;
                $params['where']['id_user'] = $this->user_id;
                $media_list = $this->Media_model->get_media(null, null, null, $params);
                if (isset($media_list[0]['id'])) {
                    $this->Media_album_model->delete_media_from_album($media_list[0]['id'], $album_id);
                } else {
                    unset($params['where']['id_parent']);
                    $params['where']['id'] = $media_id;
                    $media_list = $this->Media_model->get_media(null, null, null, $params);
                    if (isset($media_list[0]['id'])) {
                        $this->Media_album_model->delete_media_from_album($media_list[0]['id'], $album_id);
                    }
                }
            }
        }
        $return['status'] = 1;
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_save_description($photo_id)
    {
        $return = array('errors' => '', 'status' => 0, 'message' => '');

        $photo_id = intval($photo_id);
        if (!$this->Media_model->is_user_media_user($photo_id)) {
            $this->view->assign($return);

            return;
        }

        if ($_POST) {
            $post_data = array();
            if (isset($_POST["fname"])) {
                $post_data["fname"] = $this->input->post("fname", true);
            }
            if (isset($_POST["description"])) {
                $post_data["description"] = $this->input->post("description", true);
            }
            $validate_data = $this->Media_model->validate_image($post_data);

            if (empty($validate_data["errors"])) {
                $save_data = $this->Media_model->save_image($photo_id, $validate_data["data"]);
                $return["media"] = $save_data;

                $media_type = $this->input->post('media_type');
                if ($media_type) {
                    $return["message"] = l($media_type . '_update_success', 'media');
                }
                $return["status"] = 1;
            } else {
                $return["errors"] = $validate_data["errors"];
                $return["status"] = 0;
            }
            if (!empty($save_data['errors'])) {
                $return["errors"] += $save_data['errors'];
                $return["status"] = 0;
            }
        }
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_save_audio_title($audio_id)
    {
        $return = array('errors' => '', 'status' => 0);

        $audio_id = intval($audio_id);
        if (!$this->Media_model->is_user_media_user($audio_id)) {
            exit(json_encode($return));
        }

        if ($_POST) {
            $post_data = array(
                "fname"       => $this->input->post("fname", true),
                "description" => $this->input->post("description", true),
            );
            $validate_data = $this->Media_model->validate_audio($post_data);
            if (empty($validate_data["errors"])) {
                $save_data = $this->Media_model->save_audio($audio_id, $validate_data["data"]);
                $return["status"] = 1;
            } else {
                $return["errors"] = $validate_data["errors"];
                $return["status"] = 0;
            }
            if (!empty($save_data['errors'])) {
                $return["errors"] += $save_data['errors'];
                $return["status"] = 0;
            }
        }
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_save_album($mode = 'small', $album_id = null)
    {
        $return = array('errors' => '', 'status' => 0, 'data' => '');
        $this->load->model('media/models/Albums_model');
        if ($_POST) {
            if ($mode == 'small') {
                $post_data = array(
                    "name"        => $this->input->post("name", true),
                    "permissions" => 1,
                );
            } else {
                $post_data = array(
                    "name"        => $this->input->post("name", true),
                    "permissions" => $this->input->post("permissions", true),
                    "description" => $this->input->post("description", true),
                );
            }
            $validate_data = $this->Albums_model->validate_album($post_data);
            $validate_data['data']["id_album_type"] = $this->Media_model->album_type_id;
            $langs = array();
            foreach ($this->pg_language->languages as $lang_id => $lang_data) {
                $langs[$lang_id] = $validate_data['data']['name'];
            }
            if (empty($validate_data["errors"])) {
                $new_album_id = $this->Albums_model->save($album_id, $validate_data['data'], $langs);
                if ($new_album_id) {
                    $return['status'] = 1;
                    $return['data']['album_id'] = $new_album_id;
                    $return['data']['albums_select'] = $this->Media_model->get_albums_select($this->user_id);
                    $return['data']['id_user'] = $this->user_id;
                }
            } else {
                $return['errors'] = $validate_data["errors"];
            }
        } else {
            $return['errors'] = l('no_data_sended', 'media');
        }
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_save_permissions($photo_id)
    {
        $return = array('errors' => '', 'status' => 0);

        $photo_id = intval($photo_id);
        if (!$this->Media_model->is_user_media_owner($photo_id)) {
            $this->view->assign($return);

            return;
        }

        if ($_POST) {
            $post_data = array(
                "permissions" => $this->input->post("permissions", true),
            );
            $validate_data = $this->Media_model->validate_image($post_data);

            if (empty($validate_data["errors"])) {
                $save_data = $this->Media_model->save_image($photo_id, $validate_data["data"]);
                $this->Media_model->update_children_permissions($photo_id, $validate_data["data"]["permissions"]);
                $return["status"] = 1;
            } else {
                $return["errors"] = $validate_data["errors"];
                $return["status"] = 0;
            }
            if (!empty($save_data['errors'])) {
                $return["errors"] += $save_data['errors'];
                $return["status"] = 0;
            }
        }
        $this->view->assign($return);
        $this->view->render();
    }

    public function save_image()
    {
        $return = array('errors' => array(), 'warnings' => array(), 'name' => '');

        if ($_POST) {
            $post_data = array(
                "permissions" => $this->input->post("permissions", true),
                "description" => $this->input->post("description", true),
            );
        }
        $validate_data = $this->Media_model->validate_image($post_data, 'multiUpload');
        $validate_data['data']["id_user"] = $this->user_id;
        $validate_data['data']["id_owner"] = $this->user_id;
        $validate_data['data']["type_id"] = $this->Media_model->album_type_id;
        $validate_data['data']["upload_gid"] = $this->Media_model->file_config_gid;

        if (empty($validate_data["errors"])) {
            $this->load->model('Moderation_model');
            $validate_data['data']['status'] = intval($this->Moderation_model->get_moderation_type_status($this->Media_model->moderation_type));
            $mtype = $this->Moderation_model->get_moderation_type($this->Media_model->moderation_type);
            $save_data = $this->Media_model->save_image(null, $validate_data["data"], 'multiUpload', intval($mtype['mtype']));
            $is_moderated = $this->Moderation_model->add_moderation_item($this->Media_model->moderation_type, $save_data['id']);
            if ($is_moderated && $mtype['mtype'] == 2) {
                $return['warnings'][] = l('file_uploaded_and_moderated', 'media');
            }

            $id_album = intval($this->input->post('album_id'));
            if ($id_album) {
                $this->load->model('media/models/Albums_model');
                $is_user_can_add = $this->Albums_model->is_user_can_add_to_album($id_album);
                if ($is_user_can_add['status']) {
                    $this->load->model('media/models/Media_album_model');
                    $add_status = $this->Media_album_model->add_media_in_album($save_data['id'], $id_album);
                }
            }
        }

        $return["form_error"] = $validate_data['form_error'];

        if (!empty($validate_data['errors'])) {
            $return['errors'] = $validate_data['errors'];
        }
        if (empty($save_data['errors'])) {
            if (!empty($data['file'])) {
                $return['name'] = $save_data['file'];
            }
        } else {
            $return['errors'][] = $save_data['errors'];
        }
        if (!empty($is_user_can_add['error'])) {
            $return['warnings'][] = $is_user_can_add['error'];
        }

        $this->view->assign($return);

        $this->view->render();
    }

    public function save_video()
    {
        $return = array('errors' => array(), 'warnings' => array(), 'name' => '');
        if ($_POST) {
            $post_data = array(
                "permissions" => $this->input->post("permissions", true),
                "description" => $this->input->post("description", true),
                "embed_code"  => $this->input->post("embed_code", true),
            );
        }
        $validate_data = $this->Media_model->validate_video($post_data, 'videofile');
        $validate_data['data']["id_user"] = $this->user_id;
        $validate_data['data']["id_owner"] = $this->user_id;
        $validate_data['data']["type_id"] = $this->Media_model->album_type_id;
        $validate_data['data']["upload_gid"] = $this->Media_model->video_config_gid;
        if (empty($validate_data["errors"])) {
            $this->load->model('Moderation_model');
            $videofile = (!empty($validate_data['data']['media_video']) && $validate_data['data']['media_video'] === 'embed') ? '' : 'videofile';
            $validate_data['data']['status'] = intval($this->Moderation_model->get_moderation_type_status($this->Media_model->moderation_type));
            $mtype = $this->Moderation_model->get_moderation_type($this->Media_model->moderation_type);
            $create_event = !intval($mtype['mtype']);
            $save_data = $this->Media_model->save_video(null, $validate_data["data"], $videofile, $create_event);
            $is_moderated = $this->Moderation_model->add_moderation_item($this->Media_model->moderation_type, $save_data['id']);
            if ($is_moderated) {
                $return['warnings'][] = l('file_uploaded_and_moderated', 'media');
            }

            $id_album = intval($this->input->post('id_album'));
            if ($id_album) {
                $this->load->model('media/models/Albums_model');
                $is_user_can_add = $this->Albums_model->is_user_can_add_to_album($id_album);
                if ($is_user_can_add['status']) {
                    $this->load->model('media/models/Media_album_model');
                    $this->Media_album_model->add_media_in_album($save_data['id'], $id_album);
                }
            }
        }
        $return['form_error'] = $validate_data['form_error'];

        if (!empty($validate_data['errors'])) {
            $return['errors'] = $validate_data['errors'];
        }
        if (empty($save_data['errors'])) {
            if (!empty($save_data['file'])) {
                $return['name'] = $save_data['file'];
            }
        } else {
            $return['errors'][] = $save_data['errors'];
        }
        if (!empty($is_user_can_add['error'])) {
            $return['warnings'][] = $is_user_can_add['error'];
        }

        $this->view->assign($return);
        $this->view->render();
    }

    public function save_audio($theme_type = 'default')
    {
        $return = array('errors' => array(), 'warnings' => array(), 'name' => '');

        $audio_name = $this->input->post("name", true);

        //temporary check (different functionality for flatty)
        $upload_gid = 'audiofile';
        if ($theme_type == 'flatty') {
            $upload_gid = 'multiUpload';
            if (!empty($_FILES[$upload_gid]['name'])) {
                $audio_name = trim(strip_tags(
                        str_replace(array('.mp3', '.wav'), '', $_FILES[$upload_gid]['name'])
                        ));
            }
        }

        $post_data = array();

        //temporary check
        if ($_POST) {
            $post_data = array(
                "permissions" => $this->input->post("permissions", true),
                "fname"       => $audio_name,
            );
        }
        $validate_data = $this->Media_model->validate_audio($post_data, $upload_gid);

        $validate_data['data']["id_user"] = $this->user_id;
        $validate_data['data']["id_owner"] = $this->user_id;
        $validate_data['data']["type_id"] = $this->Media_model->album_type_id;
        $validate_data['data']["upload_gid"] = $this->Media_model->audio_config_gid;
        if (empty($validate_data["errors"])) {
            $this->load->model('Moderation_model');
            $validate_data['data']['status'] = intval($this->Moderation_model->get_moderation_type_status($this->Media_model->moderation_type));
            $mtype = $this->Moderation_model->get_moderation_type($this->Media_model->moderation_type);
            $save_data = $this->Media_model->save_audio(null, $validate_data["data"], $upload_gid, intval($mtype['mtype']));

            $is_moderated = $this->Moderation_model->add_moderation_item($this->Media_model->moderation_type, $save_data['id']);
            if ($is_moderated) {
                $return['warnings'][] = l('file_uploaded_and_moderated', 'media');
            }

            $id_album = intval($this->input->post('id_album'));
            if ($id_album) {
                $this->load->model('media/models/Albums_model');
                $is_user_can_add = $this->Albums_model->is_user_can_add_to_album($id_album);
                if ($is_user_can_add['status']) {
                    $this->load->model('media/models/Media_album_model');
                    $add_status = $this->Media_album_model->add_media_in_album($save_data['id'], $id_album);
                }
            }
        }
        $return["form_error"] = $validate_data['form_error'];

        if (!empty($validate_data['errors'])) {
            $return['errors'] = $validate_data['errors'];
        }
        if (empty($save_data['errors'])) {
            if (!empty($save_data['file'])) {
                $return['name'] = $save_data['file'];
            }
        } else {
            $return['errors'][] = $save_data['errors'];
        }
        if (!empty($is_user_can_add['error'])) {
            $return['warnings'][] = $is_user_can_add['error'];
        }

        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_get_list($user_id, $param = 'all', $page = 1, $album_id = 0)
    {
        $order = trim(strip_tags($this->input->post('order')));
        $direction = trim(strip_tags($this->input->post('direction')));
        if (!$order) {
            $order = 'date_add';
        }
        $order_by[$order] = ($direction == 'asc') ? 'ASC' : 'DESC';

        if ($param == 'albums' && !$album_id) {
            $albums = $this->Media_model->get_albums($user_id, $page);
            $albums['albums_select'] = $this->Media_model->get_albums_select($user_id);
            $this->view->assign($albums);
        } else {
            $this->view->assign($this->Media_model->get_list($user_id, $param, $page, $album_id, true, $order_by));
        }
        $this->view->render();
    }

    public function ajax_delete_media($media_id)
    {
        $return = array('status' => 0, 'message' => '', 'errors' => array());
        $media_id = intval($media_id);
        if ($this->Media_model->is_user_media_user($media_id)) {
            $result = $this->Media_model->delete_media($media_id);
            if (!empty($result['errors'])) {
                $return['status'] = 0;
                $return['message'] = $result['errors'];
            } else {
                $return['status'] = 1;
                $return['message'] = l('success_delete_media', 'media');
            }
        }
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_delete_album($id_album = 0)
    {
        $return = array('status' => 0, 'message' => '');
        $id_album = intval($id_album);
        $this->load->model('media/models/Albums_model');
        if ($id_album && $this->Albums_model->is_user_album_owner($id_album)) {
            $this->Albums_model->delete_album($id_album);
            $return['status'] = 1;
            $return['message'] = l('success_delete_media', 'media');
            $return['data']['albums_select'] = $this->Media_model->get_albums_select($this->user_id);
            $return['data']['id_user'] = $this->user_id;
        }
        $this->view->assign($return);
        $this->view->render();
    }

    /**
     * Recrop upload
     *
     * @param integer $upload_id    upload identifier
     * @param string  $thumb_prefix thumb prefix
     */
    public function ajax_recrop($upload_id, $thumb_prefix = '')
    {
        $result = array('status' => 1, 'errors' => array(), 'msg' => array(), 'data' => array());
        $recrop_data['x1'] = intval($this->input->post('x1', true));
        $recrop_data['y1'] = intval($this->input->post('y1', true));
        $recrop_data['width'] = intval($this->input->post('width', true));
        $recrop_data['height'] = intval($this->input->post('height', true));
        if (!$thumb_prefix) {
            $thumb_prefix = trim(strip_tags($this->input->post('prefix', true)));
        }

        $media = $this->Media_model->get_media_by_id($upload_id);
        if ($media && $thumb_prefix && $media['id_owner'] == $this->user_id) {
            $this->load->model('Uploads_model');
            $this->Uploads_model->recrop_upload($this->Media_model->file_config_gid, $media['id_owner'], $media['mediafile'], $recrop_data, $thumb_prefix);
            $result['data']['img_url'] = $media['media']['mediafile']['thumbs'][$thumb_prefix];
            $result['data']['rand'] = rand(0, 999999);
            $result['msg'][] = l('image_update_success', 'media');
        } else {
            $result['status'] = 0;
            $result['errors'][] = 'access denied';
        }

        $this->view->assign($result);
        $this->view->render();
    }

    public function ajax_get_recent_media()
    {
        $params['count'] = intval($this->input->post('count', true));
        $params['upload_gid'] = trim(strip_tags($this->input->post('upload_gid', true)));

        $data = $this->Media_model->get_recent_media($params['count'], $params['upload_gid']);
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
            $this->view->assign('recent_photos_data', $data);
            $this->view->assign('recent_thumb', $recent_thumb);
            $this->view->output($this->view->fetch('helper_recent_media', 'user', 'media'));
        }

        $this->view->render();
    }

    public function ajax_get_user_recent_media()
    {
        $user_id = intval($this->input->post('id_user', true));
        $params['count'] = intval($this->input->post('count', true));

        if ($user_id) {
            $data = $this->Media_model->get_recent_media($params['count'], null, array('where' => array('id_user' => $user_id)));
            if (!empty($data['media'])) {
                if (!isset($params['media_size'])) {
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

                $current_user = $this->session->userdata('user_id');
                $gallery_link = site_url() . "users/view/{$user_id}/gallery";
                if ($current_user == $user_id) {
                    $gallery_link = site_url() . 'users/profile/gallery';
                }

                $this->view->assign('gallery_link', $gallery_link);
                $this->view->assign('user_media_count', $data['media_count']);
                $this->view->assign('recent_photos_data', $data);
                $this->view->assign('recent_thumb', $recent_thumb);
                $this->view->assign('id_user', $user_id);

                $result = array('html' => $this->view->fetch('user_media_block', 'user', 'media'));
                $this->view->assign($result);
            }
        }

        $this->view->render();
    }

    public function ajax_get_event_recent_media($album_id = 0)
    {
        if (!empty($album_id)) {
            $this->load->model('Events_model');
            $this->Media_model->initialize($this->Events_model->album_type);

            $params['count'] = intval($this->input->post('count', true));

            $where = array();
            $where['where_in']['upload_gid'][] = $this->Media_model->file_config_gid;
            $where['where_in']['upload_gid'][] = $this->Media_model->video_config_gid;

            $data = $this->Media_model->get_gallery_list($params['count'], 'events', null, $album_id, array('date_add' => 'DESC'), $where);

            if (!empty($data['media'])) {
                $media_count = 16 - count($data['media']);
                switch ($media_count) {
//                    case 13: $recent_thumb['name'] = 'middle';
//                        $recent_thumb['width'] = '82px';
//                        break;
//                    case 14: $recent_thumb['name'] = 'big';
//                        $recent_thumb['width'] = '125px';
//                        break;
//                    case 15: $recent_thumb['name'] = 'great';
//                        $recent_thumb['width'] = '255px';
//                        break;
                    default: $recent_thumb['name'] = 'small';
                        $recent_thumb['width'] = '60px';
                }
                $this->view->assign('album_id', $album_id);
                $this->view->assign('event_media_count', $data['media_count']);
                $this->view->assign('recent_photos_data', $data);
                $this->view->assign('recent_thumb', $recent_thumb);

                $result = $this->view->fetch('event_media_block', 'user', 'media');
                $this->view->assign($result);
            }
        }

        $this->view->render();
    }

    /**
     * Rotate upload
     *
     * @param integer        $upload_id upload identifier
     * @param integer/string $angle     rotate angle
     *
     * @return void
     */
    public function ajax_rotate($upload_id, $angle = 90)
    {
        $result = array('status' => 1, 'errors' => array(), 'msg' => array(), 'data' => array());

        $media = $this->Media_model->get_media_by_id($upload_id);
        if ($angle < 0) {
            $angle += 360;
        }

        if ($media && $media['id_owner'] == $this->user_id) {
            $this->load->model('Uploads_model');
            $this->Uploads_model->rotate_upload($this->Media_model->file_config_gid, $media['id_owner'], $media['mediafile'], $angle);
            $result['data']['img_url'] = $media['media']['mediafile']['file_url'];
            $result['data']['thumbs'] = $media['media']['mediafile']['thumbs'];
            $result['data']['rand'] = rand(0, 999999);
            $result['msg'][] = l('image_update_success', 'media');
        } else {
            $result['status'] = 0;
            $result['errors'][] = 'access denied';
        }

        $this->view->assign($result);
        $this->view->render();
    }
}
