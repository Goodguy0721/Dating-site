<?php

/**
 * store helper
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	helpers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 **/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('store_categories_select')) {
    function store_categories_select($params)
    {
        $CI = &get_instance();
        $CI->load->model("store/models/Store_categories_model");

        $lang_id = $CI->pg_language->current_lang_id;

        if (!empty($params['id_product'])) {
            $category_ids = $CI->Store_categories_model->get_categories_by_product_id($params['id_product']);
            if (!empty($category_ids)) {
                $attrs['where_in']['id'] = $category_ids;
                $attrs['where']['status'] = 1;
                $data["categories"] = $CI->Store_categories_model->get_categories_list(null, null, null, $attrs, array(), true, false, $lang_id);
            }
        }
        $data['id_product'] = intval($params['id_product']);
        $data["rand"] = rand(100000, 999999);

        $CI->view->assign('category_helper_data', $data);

        return $CI->view->fetch('helper_categories_select', 'admin', 'store');
    }
}

if (!function_exists('store_categories_block')) {
    function store_categories_block($params)
    {
        $CI = &get_instance();
        $CI->load->model('store/models/Store_categories_model');
        $attrs['where']['status'] = 1;
        $categories = $CI->Store_categories_model->get_categories_list(null, null, null, $attrs);
        foreach ($categories as $key => $category) {
            if ($params['category_id'] == $category['id']) {
                $categories[$key]['active'] = true;
            }
        }
        $CI->view->assign("categories", $categories);
        $html = $CI->view->fetch("helper_categories", 'user', 'store');
        echo $html;
    }
}

if (!function_exists('store_bestsellers_block')) {
    function store_bestsellers_block($params)
    {
        $CI = &get_instance();
        $data = $product_ids = array();
        $lang_id = $CI->pg_language->current_lang_id;
        $params['where']['status'] = 1;
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $params['where']['id_category'] = intval($params["category_id"]);
        }
        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $params['where_sql'][] = ' id_product!="' . $params['product_id'] . '" ';
        }
        $items_on_page = $CI->pg_module->get_module_config('store', 'products_featured_items');
        $CI->load->model('store/models/Store_bestsellers_model');
        $bestsellers = $CI->Store_bestsellers_model->get_bestsellers_list(null, $items_on_page, $params, array('priority' => 'ASC'), $lang_id);
        if (!empty($bestsellers)) {
            $data['thumb_name'] = 'big';
            $data['media'] = $bestsellers;
            $count = count($bestsellers);
            if ($count > 7) {
                $data['carousel']['media_count'] = $count;
                $data['rand'] = $data['carousel']['rand'] = rand(1, 999999);
                $data['carousel']['visible'] = !empty($params['visible']) ? intval($params['visible']) : 3;
                $data['carousel']['scroll'] = (!empty($params['scroll']) && $params['scroll'] != 'auto') ? intval($params['scroll']) : 'auto';
                $data['carousel']['class'] = !empty($params['class']) ? $params['class'] : '';
                $data['carousel']['thumb_name'] = !empty($params['thumb_name']) ? $params['thumb_name'] : $data['thumb_name'];
                if (!$data['carousel']['scroll']) {
                    $data['carousel']['scroll'] = 1;
                }
            }
            $CI->view->assign('bestsellers', $data);

            return $CI->view->fetch('helper_bestsellers_block', 'user', 'store');
        }
    }
}

if (!function_exists('store_similar_block')) {
    function store_similar_block($params)
    {
        $CI = &get_instance();
        $CI->load->model('store/models/Store_products_model');
        $data = $product_ids = array();
        $params['where_sql'][] = ' id_product!="' . $params['product_id'] . '" ';
        $CI->load->model("store/models/Store_categories_model");
        $category_ids = $CI->Store_categories_model->get_categories_by_product_id(intval($params['product_id']));
        $items_on_page = $CI->pg_module->get_module_config('store', 'products_similar_items');
        $product_ids = $CI->Store_categories_model->get_products_by_category_ids($category_ids, $items_on_page, $params, true);
        if (!empty($product_ids)) {
            $attrs["where_in"]["id"] = $product_ids;
            $data['thumb_name'] = 'middle';
            $lang_id = $CI->pg_language->current_lang_id;
            $data['media'] = $CI->Store_products_model->get_products_list(null, null, null, $attrs, array(), true, false, $lang_id);
            $count = count($data['media']);
            if ($count > 4) {
                $data['carousel']['media_count'] = count($data['media']);
                $data['rand'] = $data['carousel']['rand'] = rand(1, 999999);
                $data['carousel']['visible'] = !empty($params['visible']) ? intval($params['visible']) : 3;
                $data['carousel']['scroll'] = (!empty($params['scroll']) && $params['scroll'] != 'auto') ? intval($params['scroll']) : 'auto';
                $data['carousel']['class'] = !empty($params['class']) ? $params['class'] : '';
                $data['carousel']['thumb_name'] = !empty($params['thumb_name']) ? $params['thumb_name'] : $data['thumb_name'];
                if (!$data['carousel']['scroll']) {
                    $data['carousel']['scroll'] = 1;
                }
            }
        }
        $CI->view->assign('similar', $data);

        return $CI->view->fetch('helper_similar_block', 'user', 'store');
    }
}

if (!function_exists('store_order_together')) {
    function store_order_together($params)
    {
        $CI = &get_instance();
        $CI->load->model('store/models/Store_products_model');
        $data = $product_ids = array();
        $CI->load->model("store/models/Store_statistics_model");
        
        $product_ids = $CI->Store_statistics_model->get_statistics_by_product($params['product_id']);
        if (!empty($product_ids)) {
            $items_on_page = $CI->pg_module->get_module_config('store', 'products_similar_items');
            
            $rand_ids = [];
    
            if (count($product_ids) > $items_on_page) {
                $keys = array_rand($product_ids, $items_on_page);
                foreach ($keys as $key) {
                    $rand_ids[] = $product_ids[$key];
                }
            }
            $attrs["where_in"]["id"] = !empty($rand_ids) ?: $product_ids;
            $data['thumb_name'] = 'middle';
            
            $lang_id = $CI->pg_language->current_lang_id;
            $data['media'] = $CI->Store_products_model->get_products_list(null, null, null, $attrs, array(), true, false, $lang_id);
            $count = count($data['media']);
            if ($count > 4) {
                $data['carousel']['media_count'] = count($data['media']);
                $data['rand'] = $data['carousel']['rand'] = rand(1, 999999);
                $data['carousel']['visible'] = !empty($params['visible']) ? intval($params['visible']) : 3;
                $data['carousel']['scroll'] = (!empty($params['scroll']) && $params['scroll'] != 'auto') ? intval($params['scroll']) : 'auto';
                $data['carousel']['class'] = !empty($params['class']) ? $params['class'] : '';
                $data['carousel']['thumb_name'] = !empty($params['thumb_name']) ? $params['thumb_name'] : $data['thumb_name'];
                if (!$data['carousel']['scroll']) {
                    $data['carousel']['scroll'] = 1;
                }
            }
        }
        $CI->view->assign('together', $data);

        return $CI->view->fetch('helper_together_block', 'user', 'store');
    }
}

if (!function_exists('store_product_media')) {
    function store_product_media($params)
    {
        $CI = &get_instance();
        $CI->load->model('store/models/Store_products_model');
        $data['photo'] = $CI->Store_products_model->get_photo_path($params['product_id'], $params['thumb_name']);
        $data['video'] = $CI->Store_products_model->get_video_path($params['product_id'], $params['thumb_name']);
        $data['carousel']['media_count'] = count($data['photo']);
        $data['rand'] = $data['carousel']['rand'] = rand(1, 999999);
        $data['carousel']['visible'] = !empty($params['visible']) ? intval($params['visible']) : 3;
        $data['carousel']['scroll'] = (!empty($params['scroll']) && $params['scroll'] != 'auto') ? intval($params['scroll']) : 'auto';
        $data['carousel']['class'] = !empty($params['class']) ? $params['class'] : '';
        $data['carousel']['thumb_name'] = !empty($params['thumb_name']) ? $params['thumb_name'] : 'middle';
        if (!$data['carousel']['scroll']) {
            $data['carousel']['scroll'] = 1;
        }
        $block_type = !empty($params['block_type']) ? $params['block_type'] : 'list';
        $CI->view->assign('product_media', $data);

        return $CI->view->fetch('helper_media_' . $block_type, 'user', 'store');
    }
}

if (!function_exists('store_button_block')) {
    function store_button_block($params)
    {
        $CI = &get_instance();
        $CI->load->model('store/models/Store_cart_model');
        $user_id = $CI->session->userdata("user_id");
        if (!isset($params['method'])) {
            $params['method'] = '';
        }
        if ($params['method'] == 'add' || $params['method'] == 'view') {
            $data['class'] = 'add_product ' . $params['class'];
            $data['value'] = l('shopping_cart', 'store');
            $data['hidden']['idrecipient'] = !empty($params['id_recipient']) ? intval($params['id_recipient']) : $user_id;
            $data['hidden']['idproduct'] = intval($params['product']['id']);
            $data['hidden']['pjax'] = 0;
            $data['hidden']['quick_view'] = 0;
            $params['type'] = 'button';
        } elseif ($params['method'] == 'checkout') {
            $data['value'] = l('button_checkout', 'store');
            $data['class'] = $params['class'];
            $params['view_type'] = 'button';
        } else {
            $data['cart'] = $CI->Store_cart_model->get_cart();
            $params['view_type'] = 'link';
        }
        $CI->view->assign('cart_button', $data);

        if (empty($params['view_type'])) {
            $params['view_type'] = 'button';
        }

        return $CI->view->fetch('helper_store_' . $params['view_type'], 'user', 'store');
    }
}

if (!function_exists('store_present_receivers')) {
    function store_present_receivers($params)
    {
        $CI = &get_instance();
        $CI->load->model('users/models/Users_views_model');
        $order_by['view_date'] = 'DESC';
        $user_id = $CI->session->userdata("user_id");
        $items_on_page = 1;
        $receivers = intval($params['user']) ?: null;
        if (isset($receivers)) {
            $CI->load->model('Users_model');
            $data = $CI->Users_model->get_users_list_by_key(null, $items_on_page, $order_by, array(), array($receivers));
            $CI->view->assign('user_data', $data[$receivers]);
            $CI->view->assign('receivers_sel', 1);
        } else {
            $last_viewers = $CI->Users_views_model->get_views_daily_unique($user_id, 1, $items_on_page, $order_by);
            $users_count = $last_viewers ? count($last_viewers) : 0;
            if (isset($users_count)) {
                foreach ($last_viewers as $viewer) {
                    $need_ids[] = $viewer['id_user'];
                }
                $CI->load->model('Users_model');
                if (!empty($need_ids)) {
                    $data = $CI->Users_model->get_users_list_by_key(1, $items_on_page, $order_by, array(), $need_ids);
                    $CI->view->assign('user_data', $data[$need_ids[0]]);
                }
                $CI->view->assign('receivers_sel', 0);
            }
        }

        return $CI->view->fetch('helper_present_receivers', 'user', 'store');
    }
}

if (!function_exists('store_address_block')) {
    function store_address_block()
    {
        $CI = &get_instance();
        $CI->load->model('store/models/Store_users_shippings_model');
        $user_id = $CI->session->userdata("user_id");
        $recipient = $CI->Store_users_shippings_model->get_address_by_user_id($user_id);
        $CI->view->assign('recipient', $recipient);

        return $CI->view->fetch('helper_address', 'user', 'store');
    }
}

if (!function_exists('store_cart')) {
    function store_cart()
    {
        $CI = &get_instance();
        $CI->load->model('store/models/Store_cart_model');
        $data['cart'] = $CI->Store_cart_model->get_cart();
        $data['items'] = $CI->Store_cart_model->get_products_by_cart();
        $CI->view->assign('data_cart', $data);

        return $CI->view->fetch('ajax_cart', 'user', 'store');
    }
}

if (!function_exists('store_media')) {
    function store_media($params)
    {
        $CI = &get_instance();
        $CI->load->model("store/models/Store_products_model");
        
        $data = [];
        
        if (!empty($params['id_product'])) {
            if ($params['type'] == 'photo') {
                $media_id = !empty($params['id_media']) ? $params['id_media'] : 0;
                $media = $CI->Store_products_model->get_images_product($params['id_product'], $params['amount'], $media_id);
            } else {
                $media = array($CI->Store_products_model->get_product_by_id($params['id_product']));
            }
            $data = $CI->Store_products_model->format_media($params['id_product'], $media, $params['type'], $params['size']);
        }
        $source = $CI->session->userdata('auth_type') == 'admin' ? 'admin' : 'user';
        $CI->view->assign('media', $data);

        return $CI->view->fetch('helper_product_media', $source, 'store');
    }
}

if (!function_exists('store_add_photos_form')) {
    function store_add_photos_form($params)
    {
        $CI = &get_instance();
        $CI->load->model("store/models/Store_products_model");
        $CI->load->model('Uploads_model');
        $media_config = $CI->Uploads_model->get_config('store');
        $media_config['max_count'] = $CI->Store_products_model->max_photo_count;
        $CI->view->assign('photo_config', $media_config);

        return $CI->view->fetch('helper_product_form_photos', 'admin', 'store');
    }
}

if (!function_exists('store_add_video_form')) {
    function store_add_video_form($params)
    {
        $CI = &get_instance();
        $CI->load->model('Video_uploads_model');
        $media_config = $CI->Video_uploads_model->get_config('store');
        $CI->view->assign('video_config', $media_config);

        return $CI->view->fetch('helper_product_form_video', 'admin', 'store');
    }
}

if (!function_exists('store_options_values')) {
    function store_options_values($params)
    {
        $CI = &get_instance();
        $CI->load->model("store/models/Store_products_model");
        $CI->load->model("store/models/Store_options_model");
        $option = array('id' => $params['id'], 'type' => isset($params['type']) ? $params['type'] : '');
        $item_values = array_merge($option, $CI->pg_language->ds->get_reference('store', 'store_optoins_' . $params['id']));
        $options_sel = !empty($params['options']) ? unserialize($params['options']) : array();
        $options_values = $CI->Store_products_model->get_options_selected($options_sel, $item_values);
        $lang_id = $CI->pg_language->current_lang_id;
        $option_data = $CI->Store_options_model->get_option_by_id($params['id'], $lang_id);
        $options_values['header'] = $option_data['name'];
        $options_values['description'] = $option_data['description'];
        $action = !empty($params['action']) ? trim(strip_tags($params['action'])) : 'view';
        $page_type = !empty($params['page_type']) ? trim(strip_tags($params['page_type'])) : 'quick';
        $source = !empty($params['source']) ? trim(strip_tags($params['source'])) : 'admin';
        $CI->view->assign('opt', $option);
        $CI->view->assign('options_values', $options_values);
        $CI->view->assign('page_type', $page_type);

        return $CI->view->fetch('helper_option_' . $action, $source, 'store');
    }
}

if (!function_exists('store_countries_select')) {
    function store_countries_select($params)
    {
        $CI = &get_instance();
        $CI->load->model("store/models/Store_shippings_model");
        $lang_id = $CI->pg_language->current_lang_id;
        if (!empty($params['id_shipping'])) {
            $data['locations'] = $CI->Store_shippings_model->get_locations_by_shipping_id($params['id_shipping']);
            $data['id_shipping'] = intval($params['id_shipping']);
        }
        $data["rand"] = rand(100000, 999999);
        $CI->view->assign('country_helper_data', $data);

        return $CI->view->fetch('helper_countries_select', 'admin', 'store');
    }
}

if (!function_exists('store_item_locations')) {
    function store_item_locations($params)
    {
        $CI = &get_instance();
        $CI->load->model("Countries_model");
        $lang_id = $CI->pg_language->current_lang_id;
        $id_shipping = isset($params['id_shipping']) ? $params['id_shipping'] : 0;
        if (isset($params['location'][$id_shipping]['id_country'])) {
            $id_country = $params['location'][$id_shipping]['id_country'];
        }
        if (isset($params['location'][$id_shipping]['id_region'])) {
            $id_region = $params['location'][$id_shipping]['id_region'];
        }
        if (isset($params['location'][$id_shipping]['id_city'])) {
            $id_city = $params['location'][$id_shipping]['id_city'];
        }
        if (isset($id_country)) {
            $data["country"] = $CI->Countries_model->get_country($id_country, $lang_id);
        }
        if (isset($id_region)) {
            $data["region"] = $CI->Countries_model->get_region($id_region, $lang_id);
        }
        if (isset($id_city)) {
            $data["city"] = $CI->Countries_model->get_city($id_city, $lang_id);
        }

        $data["var_country_name"] = "country_id";
        $data["var_region_name"] = "region_id";
        $data["var_city_name"] = "city_id";
        $data["select_type"] = "city";
        $data["rand"] = rand(100000, 999999);
        $data["var_js_name"] = 'location_' . $data["rand"];

        $CI->view->assign('item_locations', $data);

        return $CI->view->fetch('helper_item_locations', 'admin', 'store');
    }
}

if (!function_exists('btn_store')) {
    function btn_store()
    {
        $CI = &get_instance();

        if (empty($params['view_type'])) {
            $params['view_type'] = 'button';
        }

        return $CI->view->fetch('helper_send_gift_' . $params['view_type'], 'user', 'store');
    }
}
