<?php

namespace Pg\Modules\Store\Controllers;

use Pg\Libraries\View;

/**
 * Store controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 **/
class Store extends \Controller
{
    /**
     * Constructor
     *
     * @return Store
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('store/models/Store_model');
        $this->load->model('store/models/Store_products_model');
        $this->load->model('store/models/Store_categories_model');
        $this->load->model('Menu_model');
    }

    public function index()
    {
        $params = array('where' => array('status' => 1));
        $priority = $this->Store_categories_model->get_priority($params);
        $data = $this->Store_categories_model->get_category_id_by_priority($priority);
        $this->category($data['id']);
    }

    /**
     * Return category products
     *
     * @param integer $id
     * @param string  $order
     * @param string  $order_direction
     * @param integer $page
     *
     * @return void
     */
    public function category($id = null, $order = "priority", $order_direction = "ASC",  $page = 1)
    {
        if (isset($id)) {
            $lang_id = $this->pg_language->current_lang_id;
            $category = $this->Store_categories_model->get_category_by_id($id, $lang_id);
            if (!$category['status']) {
                show_404();
            }
            $this->view->assign('category', $category);
            $data = array();
            if ($this->input->post('search_product')) {
                $data['search'] = $this->input->post('search_product', true);
            }
            $ids = $this->Store_categories_model->get_products_by_category_id(intval($id));
            $data['ids'] = $ids ?: array(0);
            $search_params = $this->Store_products_model->get_products_fulltext_search($data);
            $products_count = $this->Store_products_model->get_products_count($search_params);

            $order = trim(strip_tags($order));
            switch ($order) {
                case 'name':
                    $order = 'name_' . $lang_id;
                    $sort_order = 'name';
                    break;
                case 'date_created':
                    $order = 'date_created';
                    $sort_order = 'date_created';
                    break;
                default:
                    $order = 'priority';
                    $sort_order = 'priority';
                    break;
            }
            $order_array = array($order => $order_direction);
            $this->view->assign('order', $order);

            $order_direction = strtoupper(trim(strip_tags($order_direction)));
            if ($order_direction != 'ASC') {
                $order_direction = "DESC";
            }
            $this->view->assign('order_direction', $order_direction);

            if (!$page) {
                $page = 1;
            }
            $items_on_page = $this->pg_module->get_module_config('store', 'products_per_page');

            $this->load->helper('seo');

            $seo_data = array(
                'id'   => $category['id'],
                'gid'  => $category['gid'],
                'name' => $category['name'],
            );

            $lang_canonical = true;

            if ($this->pg_module->is_module_installed('seo')) {
                $lang_canonical = $this->pg_module->get_module_config('seo', 'lang_canonical');
            }

            $seo_settings = $category;
            $seo_settings['canonical'] = rewrite_link('content', 'view', $seo_data, false, null, $lang_canonical);
            $this->pg_seo->set_seo_data($seo_settings);

            $this->load->helper('sort_order');
            $page = get_exists_page_number($page, $products_count, $items_on_page);

            $sort_data = array(
                "url"       => site_url() . "store/category/" . $id . "/",
                "order"     => $sort_order,
                "direction" => $order_direction,
                "links"     => array(
                    "priority"     => l('field_default_sorter', 'store'),
                    "name"         => l('field_name', 'store'),
                    "date_created" => l('field_date_created', 'store'),
                ),
            );
            $this->view->assign('sort_data', $sort_data);

            if ($products_count > 0) {
                $products = $this->Store_products_model->get_products_list($page, $items_on_page, $order_array, $search_params, array(), true, false, $lang_id);
                $this->view->assign("products", $products);
            }
            $this->load->helper("navigation");
            $url = site_url() . "store/category/" . $id . "/" . $order . "/" . $order_direction . "/";
            $page_data = get_user_pages_data($url, $products_count, $items_on_page, $page, 'briefPage');
            $page_data["date_format"] = $this->pg_date->get_format('date_literal', 'st');
            $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $page_data["view_type"] = 'gallery';
            $this->pg_seo->set_seo_data($category);

            $this->view->assign('page_data', $page_data);
            $form_settings = array('action' => site_url() . "store/category/" . $id);
            $this->Menu_model->breadcrumbs_set_active(l('header_main_sections', 'store'), rewrite_link('store', ''));
            $this->Menu_model->breadcrumbs_set_active($category['name']);
            $this->view->assign('lang_id', $lang_id);
            $this->view->assign('form_settings', $form_settings);
            $this->view->render('category_view');
        } else {
            show_404();
        }
    }

    /**
     * Return product
     *
     * @param integer $product_id
     *
     * @return void
     */
    public function product($product_id = null)
    {
        if (!isset($product_id)) {
            show_404();
        }
        $lang_id = $this->pg_language->current_lang_id;
        $product = $this->Store_products_model->get_product_by_id($product_id, $lang_id);
        $this->view->assign('product', $product);
        $this->load->helper('seo');
        $seo_data = array(
            'id'   => $product['id'],
            'gid'  => $product['gid'],
            'name' => $product['name'],
        );
        $lang_canonical = true;
        if ($this->pg_module->is_module_installed('seo')) {
            $lang_canonical = $this->pg_module->get_module_config('seo', 'lang_canonical');
        }
        if ($lang_canonical) {
            $lang_id = $this->pg_language->get_default_lang_id();
            if ($lang_id != $this->pg_language->current_lang_id) {
            }
        }
        /*if ($product['id_seo_settings']) {
            $this->load->model('Seo_advanced_model');
            $seo_settings = $this->Seo_advanced_model->parse_seo_tags($product['id_seo_settings']);
            $seo_settings['canonical'] = rewrite_link('store', 'product', $seo_data, false, null, $lang_canonical);
            $this->pg_seo->set_seo_tags($seo_settings);
        } else {*/
            $seo_settings = $product;
        $seo_settings['canonical'] = rewrite_link('store', 'product', $seo_data, false, null, $lang_canonical);
        $this->pg_seo->set_seo_data($seo_settings);
        //}

        $form_settings = array('action' => site_url() . "store/search/");
        $this->Menu_model->breadcrumbs_set_active(l('header_main_sections', 'store'), rewrite_link('store', ''));
        $this->Menu_model->breadcrumbs_set_active(l('header_view_product', 'store'), "store");
        $this->view->assign('form_settings', $form_settings);
        $this->view->assign('location_base_url', rewrite_link('store', 'product') . "{$product_id}/");
        $this->view->render('view');
    }

    /**
     * Search products
     *
     * @param integer $page
     *
     * @return void
     */
    public function search($page = 1)
    {
        $lang_id = $this->pg_language->current_lang_id;
        $data = array('search' => '');
        if ($this->input->post('search_product')) {
            $data['search'] = $this->input->post('search_product', true);
        }
        $search_params = $this->Store_products_model->get_products_fulltext_search($data);
        $products_count = $this->Store_products_model->get_products_count($search_params);
        if (!$page) {
            $page = 1;
        }
        $items_on_page = $this->pg_module->get_module_config('store', 'products_per_page');
        $this->load->helper('seo');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $products_count, $items_on_page);
        if ($products_count > 0) {
            $products = $this->Store_products_model->get_products_list($page, $items_on_page, null, $search_params, array(), true, false, $lang_id);
            $this->view->assign("products", $products);
        }
        $this->load->helper("navigation");
        $url = site_url() . "store/search/";
        $page_data = get_user_pages_data($url, $products_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);
        $form_settings = array('action' => site_url() . "store/search/", 'search_text' => $data['search']);
        $this->Menu_model->breadcrumbs_set_active(l('header_main_sections', 'store'), rewrite_link('store', ''));
        $this->view->assign('lang_id', $lang_id);
        $this->view->assign('form_settings', $form_settings);
        $this->view->render('category_view');
    }

    /**
     * View cart
     *
     * @param integer $page
     *
     * @return void
     */
    public function cart($page = 1)
    {
        $this->load->model('store/models/Store_cart_model');
        $cart = array();
        $cart['data'] = $this->Store_cart_model->get_cart();
        $products_count = $this->Store_cart_model->unique_products_count();
        $this->load->helper('seo');
        if ($products_count > 0) {
            $cart['items'] = $this->Store_cart_model->get_products_by_cart();
        }
        $this->view->assign('data_cart', $cart);
        $this->Menu_model->breadcrumbs_set_active(l('header_main_sections', 'store'), rewrite_link('store', ''));
        $this->Menu_model->breadcrumbs_set_active(l('cart', 'store'));
        $form_settings = array('action' => site_url() . "store/preorder/");
        $this->view->assign('form_settings', $form_settings);
        $this->view->render('cart');
    }

    /**
     * View preorders
     *
     * @return void
     */
    public function preorders()
    {
        $this->load->model('store/models/Store_orders_model');
        $orders = $this->Store_orders_model->get_orders_list();
        $this->view->assign('orders', $orders);
        $this->view->render('preorders_block');
    }

    /**
     * Return preorder
     *
     * @param integer $order_id
     *
     * @return void
     */
    public function preorder($order_id = null)
    {
        $this->load->model('store/models/Store_orders_model');
        $user_id = $this->session->userdata('user_id');
        if (!empty($order_id)) {
            $order = $this->Store_orders_model->get_order_by_id($order_id);
            if (!$order || $order['id_customer'] != $user_id) {
                redirect(site_url() . "store/cart/");
            } elseif (in_array($order['status'], $this->Store_orders_model->order_status4editing)) {
                redirect(site_url() . "store/order/" . $order_id);
            }
        }
        if ($this->input->post('cart_item')) {
            $data = array(
                'items'   => $this->input->post('cart_item', true),
                'count'   => $this->input->post('count', true),
                'options' => $this->input->post('options', true),
            );
            $this->load->model('store/models/Store_cart_model');
            $cart_data = $this->Store_cart_model->get_items_cart($data['items']);
            if (!$cart_data) {
                redirect(site_url() . "store/cart/");
            }
            $validate_data = array();
            $validate_order = $this->Store_orders_model->validate_preorder($cart_data, $data);
            if (!empty($validate_data["errors"])) {
                $order = array_merge($order, $validate_order["data"]);
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_order["errors"]);
            } else {
                $save_order = $validate_order["data"];
                $order_id = $this->Store_orders_model->save_order($save_order);
                $data['order_id'] = $order_id;
                $this->load->model('store/models/Store_orders_log_model');
                $validate_log = $this->Store_orders_log_model->validate_order_log($order_id, $save_order);
                if (!empty($validate_log["errors"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $validate_order["errors"]);
                } else {
                    $this->Store_orders_log_model->save_order_log($validate_log['data']);
                }
                $validate_product = $this->Store_orders_model->validate_order_product($cart_data, $data);
                if (!empty($validate_product["errors"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $validate_product["errors"]);
                } else {
                    $save_product = $validate_product["data"];
                    $this->Store_orders_model->save_order_product($order_id, $save_product);
                    $this->load->model('store/models/Store_statistics_model');
                    $count = array_sum($data['count']);
                    $this->Store_statistics_model->save_product($save_product, $count);
                    $this->Store_cart_model->delete_cart_products($data['items']);
                    redirect(site_url() . "store/preorder/" . $order_id);
                }
            }
        } elseif (empty($order_id)) {
            redirect(site_url() . "store/cart/");
        }
        $this->load->helper('seo');
        $this->Menu_model->breadcrumbs_set_active(l('header_main_sections', 'store'), rewrite_link('store', ''));
        $this->Menu_model->breadcrumbs_set_active(l('header_preorder', 'store'));
        $this->view->assign('preorders', $order);
        $data_order = $this->session->userdata('store_order_' . $order_id);
        $this->view->assign('data_order', json_encode($data_order));
        $form_settings = array('action' => site_url() . "store/save_order/" . $order_id);
        $this->view->assign('form_settings', $form_settings);
        $this->view->render('preorders');
    }

    /**
     * Return order
     *
     * @param integer $order_id
     *
     * @return void
     */
    public function order($order_id)
    {
        $this->load->model('store/models/Store_orders_model');
        $user_id = $this->session->userdata('user_id');
        if (isset($order_id)) {
            $order = $this->Store_orders_model->get_order_by_id($order_id);
            if (!$order || $order['id_customer'] != $user_id) {
                redirect(site_url() . "store/cart/");
            } elseif (!in_array($order['status'], $this->Store_orders_model->order_status4editing)) {
                redirect(site_url() . "store/preorder/" . $order_id);
            }
        } else {
            redirect(site_url() . "store/cart/");
        }
        $this->load->helper('seo');
        $this->Menu_model->breadcrumbs_set_active(l('header_main_sections', 'store'), rewrite_link('store', ''));
        $this->Menu_model->breadcrumbs_set_active(l('header_order', 'store'));
        $this->view->assign('orders', $order);
        $this->view->render('orders');
    }

    /**
     * View order list
     *
     * @param string  $action
     * @param integer $page
     *
     * @return void
     */
    public function order_list($action = 'preorders', $page = 1)
    {
        $page = intval($page);

        $this->load->helper('seo');
        $this->Menu_model->breadcrumbs_set_active(l('header_main_sections', 'store'), rewrite_link('store', ''));
        $base_url = rewrite_link('store', 'order_list') . "/{$action}/";
        $order_data = $this->_set_order_list_menu($action, $page);

        $this->view->assign('base_url', $base_url);
        $this->view->assign('page', $page);
        $this->view->assign('action', $action);
        $this->view->assign('order_data', $order_data);
        $this->view->assign('page_header', l('header_' . $action, 'store'));
        $this->view->render('order_list');
    }

    /**
     * Save order
     *
     * @param integer $order_id
     *
     * @return void
     */
    public function save_order($order_id)
    {
        $post_data = array(
            'for_friend'           => $this->input->post('for_friend', true),
            'for_myself'           => $this->input->post('for_myself', true),
            'user_id'              => $this->input->post('user_id', true),
            'address_id'           => $this->input->post('address_id', true),
            'shipping_id'          => $this->input->post('shipping_id', true),
            'agree_terms_delivery' => $this->input->post('agree_terms_delivery', true),
            'comment'              => $this->input->post('comment', true),
            'comment_log'          => $this->input->post('comment_log', true),
            'canceled_sender'      => $this->input->post('canceled_sender', true),
        );
        $lang_id = $this->pg_language->current_lang_id;
        $this->load->model('store/models/Store_orders_model');
        $validate_order = $this->Store_orders_model->validate_order($order_id, $post_data, $lang_id);
        if (!empty($validate_order["errors"])) {
            $this->system_messages->addMessage(View::MSG_ERROR, $validate_order["errors"]);
            $this->session->set_userdata('store_order_' . $order_id, $post_data);
            redirect(site_url() . "store/preorder/" . $order_id);
        } else {
            $order = $validate_order["data"];
            $id = $this->Store_orders_model->save_order($order);
            if (isset($id)) {
                $this->load->model('store/models/Store_orders_log_model');
                $validate_log = $this->Store_orders_log_model->validate_order_log($id, $order);
                if (!empty($validate_log["errors"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $validate_order["errors"]);
                } else {
                    $this->Store_orders_log_model->save_order_log($validate_log['data']);
                }
            }
        }
        if ($order['is_formed'] == 1) {
            if ($order["total"] <= 0) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_when_buying', 'store'));
            } else {
                $this->session->set_userdata('service_redirect', site_url() . "store/order/" . $order_id);
                $this->load->helper('payments');
                post_location_request(site_url() . "store/preorder/" . $order_id, array('price' => $order["total"], 'id_order_payment' => $id, 'id_shipping' => $order["id_shipping"]));
            }
        }
        $redirect_url = site_url() . "store/preorder/" . $order_id;
        switch ($order['status']) {
            case $this->Store_orders_model->order_status[5]:
                $redirect_url = site_url() . "store/order/" . $order_id;
                $this->session->unset_userdata('store_order_' . $order_id);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('note_send_notification', 'store'));
                break;
            case $this->Store_orders_model->order_status[4]:
                $this->system_messages->addMessage(View::MSG_ERROR, l($order['status'], 'store'));
                break;
            case $this->Store_orders_model->order_status[1]:
                $message_data = $this->format_message($order_id, $order);
                $this->load->model('Mailbox_model');
                $this->Mailbox_model->save_message(null, $message_data);
                $this->load->model('store/models/Store_statistics_model');
                $data = $this->Store_orders_model->get_order_by_id($order_id);
                $this->Store_statistics_model->save_recipient($data);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('note_send_notification', 'store'));
                break;
        }
        redirect($redirect_url);
    }

    /**
     * Return message
     *
     * @param integer $order_id
     * @param integer $order
     *
     * @return array
     */
    private function format_message($order_id, $order)
    {
        $message = str_replace("[sender_nickname]", $this->session->userdata('output_name'), l('field_message', 'store'));
        $message = str_replace("[url]", site_url() . "store/shipping_address/" . $order_id, $message);
        $message = str_replace("[rejection]", site_url() . "store/rejection/" . $order_id, $message);
        $data = array(
            'id_from_user' => $this->session->userdata('user_id'),
            'id_to_user'   => $order['id_user'],
            'id_user'      => $order['id_user'],
            'subject'      => l('header_for_you', 'store'),
            'message'      => $message,
            'folder'       => 'inbox',
            'is_new'       => 1,
        );

        return $data;
    }

    /**
     * Return address
     *
     * @param integer $order_id
     *
     * @return array
     */
    public function shipping_address($order_id)
    {
        $this->load->model('store/models/Store_orders_model');
        $is_formed = $this->Store_orders_model->get_formed_by_order_id($order_id);
        if ($is_formed == 1) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_address_saved', 'store'));
            redirect(site_url() . "mailbox/");
        }
        $user_id = $this->session->userdata('user_id');
        $this->load->model('store/models/Store_users_shippings_model');
        $recipient = $this->Store_users_shippings_model->get_address_by_user_id($user_id);
        $this->view->assign('order_id', $order_id);
        $this->view->assign('recipient', $recipient);
        $this->view->render('address_list');
    }

    /**
     * Rejection order
     *
     * @param integer $order_id
     *
     * @return void
     */
    public function rejection($order_id)
    {
        $this->load->model('store/models/Store_orders_model');
        $return = $this->Store_orders_model->order_rejection($order_id);
        if ($return) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('status_rejected_order', 'store'));
        } else {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_address_delete', 'store'));
        }
        redirect(site_url() . "mailbox/");
    }

    /**
     * Return orders menu
     *
     * @param string  $action
     * @param integer $page
     *
     * @return array
     */
    private function _set_order_list_menu($action, $page = 1)
    {
        $this->load->model('store/models/Store_orders_model');
        $items_on_page = 10;
        $order_by['date_updated'] = 'DESC';
        switch ($action) {
            case 'preorders':
                $this->Menu_model->breadcrumbs_set_active(l('header_preorders', 'store'));
                $params['where']['is_archive'] = 0;
                $params['where']['id_customer'] = $this->session->userdata('user_id');
                $params['where_in']['status'] = array($this->Store_orders_model->order_status[0], $this->Store_orders_model->order_status[1], $this->Store_orders_model->order_status[3]);
                $data = $this->Store_orders_model->get_orders_list($page, $items_on_page, $order_by, $params);
                break;
            case 'orders':
                $this->Menu_model->breadcrumbs_set_active(l('header_orders', 'store'));
                $params['where']['is_formed'] = 1;
                $params['where']['is_archive'] = 0;
                $params['where']['id_customer'] = $this->session->userdata('user_id');
                $params['where_in']['status'] = array($this->Store_orders_model->order_status[5], $this->Store_orders_model->order_status[7]);
                $data = $this->Store_orders_model->get_orders_list($page, $items_on_page, $order_by, $params);
                break;
            case 'history':
                $this->Menu_model->breadcrumbs_set_active(l('header_history', 'store'));
                $this->load->model('store/models/Store_orders_log_model');
                $params['where']['is_archive'] = 1;
                $params['where']['id_customer'] = $this->session->userdata('user_id');
                $data = $this->Store_orders_model->get_orders_list($page, $items_on_page, $order_by, $params);
                break;
        }

        return $data;
    }

    /**
     * Present receivers
     *
     * @param integer $receiver_id
     *
     * @return string
     */
    private function _get_helper_links_html($receiver_id)
    {
        $this->load->model('Users_model');
        $data = $this->Users_model->get_users_list_by_key(null, 1, array(), array(), array($receiver_id));
        $this->view->assign('user_data', $data[$receiver_id]);

        return $this->view->fetch('helper_present_receivers', 'user', 'store');
    }

    /**
     * Search users
     *
     * @param integer $page
     * @param string  $search
     *
     * @return string
     */
    private function _get_search_users($page = 1, $search = '')
    {
        $search = trim(strip_tags($search));
        $this->load->model('Users_model');
        $this->load->model('Field_editor_model');
        $this->Field_editor_model->initialize($this->Users_model->form_editor_type);
        $temp_criteria = $this->Field_editor_model->return_fulltext_criteria($search);
        $criteria['fields'][] = $temp_criteria['user']['field'];
        $criteria['where_sql'][] = $temp_criteria['user']['where_sql'];
        $user_list = $this->Users_model->get_users_list_by_key(null, null, null, $criteria);
        foreach ($user_list as $key => $user) {
            $search_list['items'][$key]['user'] = $user;
        }
        $search_list['title'] = l('search_results', 'users');
        $this->view->assign('user_list', $search_list);

        return $this->view->fetch('receivers_list');
    }

    /**
     * Return friend list
     *
     * @param integer $page
     * @param integer $items_on_page
     *
     * @return string
     */
    private function _get_friendlist($page = 1, $items_on_page = 5)
    {
        $this->load->model('friendlist/models/Friendlist_model');
        $friendlist = array();
        $user_id = $this->session->userdata('user_id');
        $order_by['date_update'] = 'DESC';
        $friens_count = $this->Friendlist_model->get_list_count($user_id);
        $this->load->helper('sort_order');
        $friendlist['title'] = l('friendlist', 'friendlist');
        if ($friens_count) {
            $friendlist['items'] = $this->Friendlist_model->get_list($user_id, 'accept', $page, $items_on_page, $order_by);
        }
        if (!empty($friendlist['items'])) {
            $this->view->assign('user_list', $friendlist);

            return $this->view->fetch('receivers_list');
        }

        return false;
    }

    /**
     * Return sent list
     *
     * @param integer $page
     * @param integer $items_on_page
     *
     * @return string
     */
    private function _get_sent_list($page = 1, $items_on_page = 5)
    {
        $user_id = $this->session->userdata('user_id');
        $this->load->model('store/models/Store_statistics_model');
        $params['where']['id_user'] = $user_id;
        $order_by['id'] = 'DESC';
        $recipient = $this->Store_statistics_model->get_list_statistics($page, $items_on_page, $order_by, $params);
        foreach ($recipient as $value) {
            $ids[] = $value['id_recipient'];
        }
        if (!empty($ids)) {
            $this->load->model('Users_model');
            $user_list = $this->Users_model->get_users_list_by_key(null, null, null, array(), $ids);
            foreach ($user_list as $key => $user) {
                $sent_list['items'][$key]['user'] = $user;
            }
        }
        $sent_list['title'] = l('field_sent_products', 'store');
        if (!empty($sent_list['items'])) {
            $this->view->assign('user_list', $sent_list);

            return $this->view->fetch('receivers_list');
        }

        return false;
    }

    /**
     * Return user address
     *
     * @param integer $user_id
     *
     * @return string
     */
    private function _get_user_address($user_id)
    {
        $this->load->model('store/models/Store_users_shippings_model');
        $recipient = $this->Store_users_shippings_model->get_address_by_user_id($user_id);
        $this->view->assign('recipient', $recipient);

        return $this->view->fetch('helper_address');
    }

    /**
     * Return address
     *
     * @param integer $address_id
     *
     * @return string
     */
    private function _get_address($address_id)
    {
        $this->load->model('store/models/Store_users_shippings_model');
        $user_id = $this->session->userdata('user_id');
        $address = $this->Store_users_shippings_model->get_address_by_id($address_id, $user_id);
        $this->view->assign('address', $address);

        return $this->view->fetch('order_edit_shipping');
    }

    /**
     * Return rergion, city
     *
     * @param array $data
     *
     * @return array
     */
    private function _get_load_region_city($data)
    {
        $this->load->model("Countries_model");
        $lang_id = $this->pg_language->current_lang_id;
        $search_text_escape = $this->db->escape("%" . $data['location'] . "%");
        $params["where"]["country_code"] = $data['country'];
        $params["where_sql"][] = "lang_" . $lang_id . " LIKE " . $search_text_escape;
        if (!empty($data['region'])) {
            $params["where"]["id_region"] = $data['region'];
        } else {
            $result['regions'] = $this->Countries_model->get_regions($data['country'], array(), $params, array(), $lang_id);
        }
        $result['cities'] = $this->Countries_model->get_cities(null, null, array(), $params, array(), $lang_id);

        return $result;
    }

    /**
     * Return order log
     *
     * @param integer $order_id
     *
     * @return array
     */
    private function _get_show_order_log($order_id)
    {
        $result = array();
        if (isset($order_id)) {
            $this->load->model('store/models/Store_orders_model');
            $owner = $this->Store_orders_model->get_owner_order($order_id);
            if ($owner) {
                $this->load->model('store/models/Store_orders_log_model');
                $order_log = $this->Store_orders_log_model->get_log_by_id_order($order_id);
                $this->view->assign('order_log', $order_log);
                $page_data["date_format"] = $this->pg_date->get_format('date_literal', 'st');
                $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
                $this->view->assign('page_data', $page_data);
                $result['html'] = $this->view->fetch('order_log');
            }
        }

        return $result;
    }

    /**
     * Close preorder
     *
     * @param integer $order_id
     * @param array   $data
     *
     * @return void
     */
    private function _set_close_preorder($order_id = 0, $data = array())
    {
        if (isset($order_id)) {
            $this->load->model('store/models/Store_orders_model');
            $this->load->model('store/models/Store_orders_log_model');
            $validate_order = $this->Store_orders_model->validate_order($order_id, $data);
            if (!empty($validate_order["errors"])) {
                return $validate_order;
            } else {
                $order = $validate_order["data"];
                $id = $this->Store_orders_model->save_order($order);
                if (isset($id)) {
                    $this->load->model('store/models/Store_orders_log_model');
                    $validate_log = $this->Store_orders_log_model->validate_order_log($id, $order);
                    if (!empty($validate_log["errors"])) {
                        return $validate_order;
                    } else {
                        $this->Store_orders_log_model->save_order_log($validate_log['data']);

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Delete history
     *
     * @param integer $order_id
     *
     * @return void
     */
    private function _delete_history($order_id)
    {
        if (isset($order_id)) {
            $this->load->model('store/models/Store_orders_model');
            $this->load->model('store/models/Store_orders_log_model');
            $return = $this->Store_orders_model->delete_order($order_id);
            if ($return) {
                $this->Store_orders_log_model->delete_order_log($order_id);
            }
        }

        return false;
    }

    /**
     * Add to cart product(s)
     *
     * @param integer $id_product
     * @param integer $id_recipient
     *
     * @return array
     */
    private function _add_to_cart($id_product, $id_recipient)
    {
        $result = array();
        if (isset($id_product)) {
            $this->load->model('store/models/Store_cart_model');
            $this->load->model('store/models/Store_orders_model');
            $id_user = $this->session->userdata('user_id');
            $data = array(
                'id_recipient' => isset($id_recipient) ? intval($id_recipient) : $id_user,
                'id_product'   => intval($id_product),
                'option'       => $this->input->post('option', true),
                'count'        => $this->input->post('count', true),
            );
            $product_data = $this->Store_products_model->get_product_by_id(intval($id_product));
            $validate_cart = $this->Store_cart_model->validate($product_data, $data);
            $result = $this->Store_cart_model->add_to_cart($validate_cart['data']);
        }

        return $result;
    }

    /**
     * Quick view
     *
     * @param integer $product_id
     *
     * @return array
     */
    private function _quick_view($product_id)
    {
        $result = array();
        if ($product_id) {
            $user_id = $this->session->userdata('user_id');
            $current_lang_id = $this->pg_language->current_lang_id;
            $quick_view_data = $this->Store_products_model->get_product_by_id(intval($product_id), $current_lang_id);
            $this->view->assign('quick_view_data', $quick_view_data);
            $result['html'] = $this->view->fetch('ajax_quick_view');
        }

        return $result;
    }

    /**
     * Return categories
     *
     *
     * @return array
     */
    private function _get_categories()
    {
        $params = array('where' => array('status' => 1));
        $params['category_id'] = isset($params['category_id']) ? $params['category_id'] : '';
        $this->load->model('store/models/Store_categories_model');
        $categories = $this->Store_categories_model->get_categories_list(null, null, null, $params);
        foreach ($categories as $key => $category) {
            if ($params['category_id'] == $category['id']) {
                $categories[$key]['active'] = true;
            }
        }
        $this->view->assign("categories", $categories);
        $result['html'] = $this->view->fetch("helper_categories", 'user', 'store');

        return $result;
    }

    /**
     * Return shipping methods
     *
     * @param integer $id
     * @param integer $user_id
     *
     * @return array
     */
    private function _get_shipping_methods($id = null, $user_id = null)
    {
        $shippings = array();
        $this->load->model('store/models/Store_users_shippings_model');
        if (isset($user_id)) {
            $get_address = $this->Store_users_shippings_model->get_address_by_user_id($user_id);
            $country = $get_address[0]['country_code'];
        }
        if (isset($id)) {
            $user_id = $this->session->userdata('user_id');
            $address = $this->Store_users_shippings_model->get_address_by_id($id, $user_id, false);
            $country = $address['country'];
        }
        if (!empty($country)) {
            $attrs["where"]["status"] = 1;
            $this->load->model('store/models/Store_shippings_model');
            $shippings_count = $this->Store_shippings_model->get_shippings_count($attrs);
            if ($shippings_count) {
                $params['where']['id_country'] = $country;
                $shipping_ids = $this->Store_shippings_model->get_shippings_by_location(null, null, null, $params);
                if (!empty($shipping_ids)) {
                    $lang_id = $this->pg_language->current_lang_id;
                    $shippings = $this->Store_shippings_model->get_shippings_list(null, null, null, $attrs, $shipping_ids, true, false, $lang_id);
                }
            }
        }
        $this->view->assign("shippings", $shippings);
        $result['html'] = $this->view->fetch("shipping_methods", 'user', 'store');

        return $result;
    }

    /**
     * Save confirm address
     *
     * @param array $data
     *
     * @return void
     */
    private function _set_confirm_address($data = array())
    {
        if (!empty($data['address_id'])) {
            $this->load->model('store/models/Store_orders_model');
            $get_order = $this->Store_orders_model->get_order_by_id(intval($data['order_id']));
            $user_id = $this->session->userdata('user_id');
            if ($get_order['id_user'] == $user_id) {
                $data['user_id'] = $user_id;
                $data['status'] = $this->Store_orders_model->order_status[3];
                $validate_order = $this->Store_orders_model->validate_order($data['order_id'], $data);
                if (!empty($validate_order["errors"])) {
                    $result["errors"] = $validate_order["errors"];
                } else {
                    $order = $validate_order["data"];
                    $id = $this->Store_orders_model->save_order($order);
                    $result['success'][] = l('success_address_saved', 'store');
                    if (isset($id) && empty($get_order['shipping_address'])) {
                        $this->load->model('store/models/Store_orders_log_model');
                        $validate_log = $this->Store_orders_log_model->validate_order_log($id, $order);
                        if (!empty($validate_log["errors"])) {
                            $result["errors"] = $validate_log["errors"];
                        } else {
                            $this->Store_orders_log_model->save_order_log($validate_log['data']);
                        }
                    }
                }
            } else {
                $result['errors'][] = l('error_getting', 'store');
            }
        } else {
            $result["errors"][] = l('error_specified_address', 'store');
        }

        return $result;
    }

    /**
     * Add to cart by ajax
     *
     * @param integer $id_product
     * @param integer $id_recipient
     *
     * @return void
     */
    public function ajax_add_to_cart($id_product, $id_recipient)
    {
        $result = $this->_add_to_cart($id_product, $id_recipient);
        $this->view->assign($result);

        return;
    }

    /**
     * Quick view by ajax
     *
     * @param integer $product_id
     *
     * @return viod
     */
    public function ajax_quick_view($product_id = null)
    {
        $result = $this->_quick_view($product_id);
        $this->view->assign($result);

        return;
    }

    /**
     * Return media by ajax
     *
     * @param string  $type
     * @param integer $product_id
     * @param integer $media_id
     *
     * @return void
     */
    public function ajax_view_product_media($type, $product_id, $media_id)
    {
        $media_data = array();
        $media_data['media_id'] = intval($media_id);
        $media_data['product_id'] = intval($product_id);
        $media_data['type'] = trim(strip_tags($type));
        $media_ids = $this->Store_products_model->get_images_product($media_data['product_id']);
        $media_data['count'] = count($media_ids);
        $selections = array();
        $this->load->model('Uploads_model');
        $upload_config = $this->Uploads_model->get_config($this->Store_products_model->file_config_gid);
        foreach ($upload_config['thumbs'] as $thumb_config) {
            $selections[$thumb_config['prefix']] = array(
                'width'  => $thumb_config['width'],
                'height' => $thumb_config['height'],
            );
        }
        $this->view->assign('selections', $selections);
        $this->view->assign('media_data', $media_data);
        $this->view->assign('rand', rand(0, 999999));
        $this->view->output($this->view->fetchFinal('view_media'));
        $this->view->render();
    }

    /**
     * Return photos by ajax
     *
     * @param integer $product_id
     * @param array   $param
     * @param string  $size
     *
     * @return void
     */
    public function ajax_get_product_photos($product_id, $param = 'all', $size = 'small')
    {
        $this->view->assign($this->Store_products_model->get_photo_list($product_id, $param, 'photo', $size));
    }

    /**
     * View cart by ajax
     *
     *
     * @return void
     */
    public function ajax_load_cart()
    {
        $this->load->model('store/models/Store_cart_model');
        $data = array(
            'items' => $this->Store_cart_model->get_products_by_cart(),
        );
        $this->view->assign('data_cart', $data);
        exit($this->view->fetch('ajax_cart'));
    }

    /**
     * Remove from cart by ajax
     *
     *
     * @return void
     */
    public function ajax_remove_from_cart()
    {
        $this->load->model('store/models/Store_cart_model');
        $cart_items = $this->input->post('cart_item', true);
        $result = $this->Store_cart_model->delete_cart_products($cart_items);
        $this->view->assign($result);

        return;
    }

    /**
     * Order user by ajax
     *
     *
     * @return void
     */
    public function ajax_order_users()
    {
        exit($this->view->fetch('order_edit_recipient'));
    }

    /**
     * Return friends by ajax
     *
     * @param integer $all
     *
     * @return void
     */
    public function ajax_friends($all = 1)
    {
        $page = !empty($all) ? null : 1;
        $result = $this->_get_friendlist($page);
        exit($result);
    }

    /**
     * Select users by ajax
     *
     * @param integer $receiver_id
     *
     * @return void
     */
    public function ajax_select_users($receiver_id)
    {
        if (isset($receiver_id)) {
            $result = $this->_get_helper_links_html($receiver_id);
            exit($result);
        }
    }

    /**
     * Search users by ajax
     *
     *
     * @return void
     */
    public function ajax_search_users()
    {
        $search = $this->input->post('search', true);
        $result = $this->_get_search_users(1, $search);
        exit($result);
    }

    /**
     * Rerurn friend list by ajax
     *
     *
     * @return void
     */
    public function ajax_friend_list()
    {
        $result = $this->_get_friendlist(1);
        exit($result);
    }

    /**
     * Return sent list by ajax
     *
     *
     * @return void
     */
    public function ajax_sent_list()
    {
        $result = $this->_get_sent_list(1);
        exit($result);
    }

    /**
     * User address by ajax
     *
     *
     * @return void
     */
    public function ajax_user_address()
    {
        $user_id = $this->session->userdata('user_id');
        $result = $this->_get_user_address($user_id);
        exit($result);
    }

    /**
     * Address form by ajax
     *
     * @param integer $address_id
     *
     * @return void
     */
    public function ajax_address_form($address_id = 0)
    {
        if (isset($address_id)) {
            $result = $this->_get_address($address_id);
        }
        exit($result);
    }

    /**
     * Return counries by ajax
     *
     *
     * @return void
     */
    public function ajax_load_countries()
    {
        $this->load->model("Countries_model");
        $lang_id = $this->pg_language->current_lang_id;
        $result = $this->Countries_model->get_countries();
        $this->view->assign('countries', $result);

        return;
    }

    /**
     * Return region, city by ajax
     *
     *
     * @return void
     */
    public function ajax_load_region_city()
    {
        $data['country'] = trim(strip_tags($this->input->post('country', true)));
        $data['region'] = trim(strip_tags($this->input->post('region', true)));
        $data['location'] = $this->input->post('data', true);
        $result = $this->_get_load_region_city($data);

        if (!isset($result['regions'])) {
            $result['regions'] = array();
        }
        $this->view->assign('regions', $result['regions']);

        if (!isset($result['cities'])) {
            $result['cities'] = array();
        }
        $this->view->assign('cities', $result['cities']);

        return;
    }

    /**
     * Save address by ajax
     *
     * @param integer $order_id
     * @param integer $address_id
     *
     * @return void
     */
    public function ajax_save_address($order_id, $address_id = null)
    {
        $post_data = array(
            'country'        => $this->input->post('country', true),
            'region'         => $this->input->post('region', true),
            'city'           => $this->input->post('city', true),
            'phone'          => $this->input->post('phone', true),
            'street_address' => $this->input->post('street_address', true),
            'zip'            => $this->input->post('zip', true),
        );
        $address = array();
        $lang_id = $this->pg_language->current_lang_id;
        $this->load->model('store/models/Store_users_shippings_model');
        $validate_address = $this->Store_users_shippings_model->validate_address($post_data);
        if (!empty($validate_address["errors"])) {
            $result['errors'] = $validate_address["errors"];
        } else {
            $save_data = $validate_address["data"];
            $result['address_id'] = $this->Store_users_shippings_model->save_address($address_id, $save_data);
            $user_id = $this->session->userdata('user_id');
            $result['html'] = $this->_get_user_address($user_id);
        }
        $this->view->assign($result);

        return;
    }

    /**
     * Delete address by ajax
     *
     * @param integer $id
     *
     * @return integer
     */
    public function ajax_delete_address($id)
    {
        if (isset($id)) {
            $user_id = $this->session->userdata('user_id');
            $this->load->model('store/models/Store_users_shippings_model');
            $this->Store_users_shippings_model->delete_address($id, $user_id);
        }
        exit($id);
    }

    /**
     * Return terms delivery by ajax
     *
     *
     * @return void
     */
    public function ajax_terms_delivery()
    {
        $data = $this->Store_model->get_settings();
        $this->load->model('content/models/Content_model');
        $lang_id = $this->pg_language->current_lang_id;
        $result = $this->Content_model->get_page_by_gid($data['shipping_page_gid'], $lang_id);
        $this->view->assign($result);

        return;
    }

    /**
     * Return order log by ajax
     *
     * @param integer $oredr_id
     *
     * @return integer
     */
    public function ajax_show_order_log($oredr_id)
    {
        $result = $this->_get_show_order_log($oredr_id);
        $this->view->assign($result);

        return;
    }

    /**
     * Close preorder by ajax
     *
     * @param integer $oredr_id
     *
     * @return void
     */
    public function ajax_close_preorder($oredr_id)
    {
        $post_data = array('canceled_sender' => $this->input->post('canceled_sender', true));
        $result = $this->_set_close_preorder($oredr_id, $post_data);
        $this->view->assign($result);

        return;
    }

    /**
     * Delete history by ajax
     *
     * @param integer $oredr_id
     *
     * @return void
     */
    public function ajax_delete_history($oredr_id)
    {
        return $this->_delete_history($oredr_id);
    }

    /**
     * Confirm address by ajax
     *
     *
     * @return void
     */
    public function ajax_confirm_address()
    {
        $post_data = array(
            'order_id'   => $this->input->post('order_id', true),
            'address_id' => $this->input->post('address_id', true),
            'comment'    => $this->input->post('comment', true),
        );
        $result = $this->_set_confirm_address($post_data);
        $this->view->assign($result);

        return;
    }

    /**
     * Return categories by ajax
     *
     *
     * @return void
     */
    public function ajax_load_categories()
    {
        $result = $this->_get_categories();
        $this->view->assign($result);

        return;
    }

    /**
     * Return shipping methods by ajax
     *
     *
     * @return void
     */
    public function ajax_load_shipping_methods()
    {
        $id = $this->input->post('id', true) ?: null;
        $user_id = $this->input->post('user_id', true) ?: null;
        $result = $this->_get_shipping_methods($id, $user_id);
        $this->view->assign($result);

        return;
    }
}
