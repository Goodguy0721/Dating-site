<?php

namespace Pg\Modules\Store\Models;

use Pg\Libraries\View;
use Pg\Libraries\EventDispatcher;
use Pg\Modules\Store\Models\Events\EventStore;
use Pg\Modules\Store\Models\Store_model;

/**
 * Store orders model
 *
 * @package PG_Dating
 * @subpackage application
 * @orders	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 **/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
if (!defined('TABLE_STORE_ORDERS')) {
    define('TABLE_STORE_ORDERS', DB_PREFIX . 'store_orders');
}
if (!defined('TABLE_STORE_ORDERS_PRODUCTS')) {
    define('TABLE_STORE_ORDERS_PRODUCTS', DB_PREFIX . 'store_orders_products');
}

class Store_orders_model extends \Model
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE = '0000-00-00 00:00:00';

    private $CI;
    private $DB;
    private $fields = array(
        'id',
        'code',
        'id_customer',
        'customer_name',
        'id_user',
        'user',
        'total',
        'gid_currency',
        'products_count',
        'id_shipping',
        'shipping_name',
        'shipping_country',
        'shipping_region',
        'shipping_city',
        'shipping_address',
        'shipping_zip',
        'contact_phone',
        'comment',
        'status',
        'is_formed',
        'is_alert',
        'is_archive',
        'date_created',
        'date_updated',
    );
    private $fields_prod = array(
        'id_order',
        'id_product',
        'name',
        'category_name',
        'price',
        'gid_currency',
        'count',
        'options',
        'options_text',
        'description',
    );

    public $order_status = array(
        'status_pending_payment',
        'status_waits_user_consent',
        'status_rejected_recipient',
        'status_confirmed_recipient',
        'status_canceled_sender',
        'status_paid',
        'status_rejected_administrator',
        'status_during_delivery',
        'status_delivered',
    );

    public $order_status4editing = array(
        'status_paid',
        'status_rejected_administrator',
        'status_during_delivery',
        'status_delivered',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->DB->memcache_tables(array(TABLE_STORE_ORDERS, TABLE_STORE_ORDERS_PRODUCTS));
    }

    public function get_order_by_id($id, $is_formatted=true)
    {
        $result = $this->DB->select(implode(", ", $this->fields))
                ->from(TABLE_STORE_ORDERS)
                ->where("id", $id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } elseif ($is_formatted) {
            return $this->format_order($result[0]);
        } else {
            return $result[0];
        }
    }

    public function get_order_by_code($code)
    {
        $result = $this->DB->select(implode(", ", $this->fields))
                ->from(TABLE_STORE_ORDERS)
                ->where("code", $code)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $this->format_order($result[0]);
        }
    }

    public function get_order_id_by_code($code)
    {
        $result = $this->DB->select('id')
                ->from(TABLE_STORE_ORDERS)
                ->where("code", $code)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0]['id'];
        }
    }

    public function get_order_products_by_order_id($order_id, $formatted = true)
    {
        $result = $this->DB->select(implode(", ", $this->fields_prod))
                ->from(TABLE_STORE_ORDERS_PRODUCTS)
                ->where("id_order", $order_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } elseif ($formatted) {
            return $this->format_order_products($result);
        } else {
            return $result;
        }
    }

    public function get_order_ids_by_status($status)
    {
        $result = $this->DB->select('id')
                ->from(TABLE_STORE_ORDERS_PRODUCTS)
                ->where("status", $status)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result;
        }
    }

    public function get_status_by_order_id($order_id)
    {
        $result = $this->DB->select('status')
                ->from(TABLE_STORE_ORDERS)
                ->where("id", $order_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0]['status'];
        }
    }

    public function get_formed_by_order_id($order_id)
    {
        $result = $this->DB->select('is_formed')
                ->from(TABLE_STORE_ORDERS)
                ->where("id", $order_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0]['is_formed'];
        }
    }

    public function get_orders_list($page = null, $items_on_page = null, $order_by = null, $params = array())
    {
        $this->DB->select(implode(", ", $this->fields));
        $this->DB->from(TABLE_STORE_ORDERS);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params["like"]) && is_array($params["like"]) && count($params["like"])) {
            foreach ($params["like"] as $field => $value) {
                $this->DB->like($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value, null, false);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                $this->DB->order_by($field . " " . $dir);
            }
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->DB->limit($items_on_page, $items_on_page * ($page - 1));
        }
        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return $this->format_orders($results);
        }

        return array();
    }

    public function get_orders_count($params = array(), $filter_object_ids = null)
    {
        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params["like"]) && is_array($params["like"]) && count($params["like"])) {
            foreach ($params["like"] as $field => $value) {
                $this->DB->like($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value, null, false);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->DB->where_in("id", $filter_object_ids);
        }
        $result = $this->DB->count_all_results(TABLE_STORE_ORDERS);

        return $result;
    }

    public function get_address_last_order()
    {
        $params['id_user'] = $params['id_customer'] = $this->CI->session->userdata('user_id');
        $params['status'] = 'success';
        $order_by['date_updated'] = 'DESC';
        $result = $this->get_orders_list(1, 1, $order_by, $params);
        $format_result = $this->format_address($result);

        return $format_result;
    }

    public function validate_address($order_id, $data, $lang_id)
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($order_id)) {
            $return["data"]["id"] = intval($order_id);
        }

        $user_for_location[$order_id] = array($data["country"], $data["region"], $data["city"]);

        if (!empty($user_for_location)) {
            $this->CI->load->helper('countries');
            $user_locations = cities_output_format($user_for_location, $lang_id);
            $users_locations_data = get_location_data($user_for_location, 'city');
            if (isset($users_locations_data['country'][$data['country']])) {
                $return["data"]['shipping_country'] = $users_locations_data['country'][$data['country']]['name'];
            } else {
                $return["errors"]["country"] = l('error_specified_country', 'store');
            }
            if (isset($users_locations_data['region'][$data['region']])) {
                $return["data"]['shipping_region'] =  $users_locations_data['region'][$data['region']]['name'];
            } else {
                $return["errors"]["region"] = l('error_specified_region', 'store');
            }
            if (isset($users_locations_data['city'][$data['city']])) {
                $return["data"]['shipping_city'] =  $users_locations_data['city'][$data['city']]['name'];
            } else {
                $return["errors"]["city"] = l('error_specified_city', 'store');
            }
        }

        if (isset($data["street_address"])) {
            $return["data"]["shipping_address"] = strip_tags($data["street_address"]);
        } else {
            $return["errors"]["city"] = l('error_specified_address', 'store');
        }

        if (isset($data["phone"])) {
            $return["data"]["contact_phone"] = strip_tags($data["phone"]);
        }

        if (isset($data["comment"])) {
            $return["data"]["comment"] = strip_tags($data["comment"]);
        }

        if (isset($data["zip"])) {
            $return["data"]["shipping_zip"] = strip_tags($data["zip"]);
        }

        return $return;
    }

    private function get_order_price($order_id)
    {
        $data = $this->get_order_products_by_order_id($order_id, false);
        $result = '';
        foreach ($data as $item) {
            $result[] = $item['count'] * (double) $item['price'];
        }

        return array_sum($result);
    }

    public function get_owner_order($order_id)
    {
        $data = $this->get_order_by_id($order_id);
        $user_id = $this->CI->session->userdata('user_id');
        if ($user_id == $data['id_customer']) {
            return true;
        } else {
            return false;
        }
    }

    public function format_address($data)
    {
        $result["country"] = $data['shipping_country'];
        $result["region"] = $data['shipping_region'];
        $result["city"] = $data['shipping_city'];
        $result["address"] = $data['shipping_address'];
        $result["zip"] = $data['shipping_zip'];

        return $result;
    }

    public function format_order($data)
    {
        if ($data) {
            $return = $this->format_orders(array(0 => $data));

            return $return[0];
        }

        return array();
    }

    public function format_orders($orders)
    {        
        foreach ($orders as $key => $order) {
            if (!empty($order['total'])) {
                $order['total'] = (double) $order["total"];
            }
            if (!empty($order['status'])) {
                $order['status_text'] = l($order["status"], 'store');
            }  
            
            $shipping_location = [];
           
            if (!empty($order["shipping_address"])) {
                $shipping_location[] = $order["shipping_address"];
            }
            
            if (!empty($order["shipping_city"])) {
                $shipping_location[] = $order["shipping_city"];
            }
            
            if (!empty($order["shipping_region"])) {
                $shipping_location[] = $order["shipping_region"];
            }
            
            if (!empty($order["shipping_country"])) {
                $shipping_location[] = $order["shipping_country"];
            }
            
            $order['shipping_location'] = implode(', ', $shipping_location);
            $order["items"] = $this->get_order_products_by_order_id($order["id"]);
            $orders[$key] = $order;
        }
        
        return $orders;
    }

    public function format_order_products($data)
    {
        $lang_id = $this->CI->pg_language->current_lang_id;
        $this->CI->load->model('store/models/Store_products_model');
        foreach ($data as $key => $item) {
            if (!empty($item['price'])) {
                $item['price'] = (double) $item["price"];
            }
            if (!empty($item['options'])) {
                $item['options'] = unserialize($item['options']);
            }
            if (!empty($item['id_product'])) {
                $item['product'] = $this->CI->Store_products_model->get_product_by_id($item['id_product'], $lang_id);
            }
            $data[$key] = $item;
        }

        return $data;
    }

    public function format_order_product($data)
    {
        if ($data) {
            $return = $this->format_order_products(array(0 => $data));

            return $return[0];
        }

        return array();
    }

    public function validate_preorder($cart_data, $data)
    {
        $return = array("errors" => array(), "data" => array());
        $return['data']['id'] = isset($data['order']['id']) ? $data['order']['id'] : null;
        $return['data']['id_customer'] = $this->CI->session->userdata('user_id');
        $return['data']['customer_name'] = $this->CI->session->userdata('output_name');
        $return['data']['code'] = strtoupper(substr(md5(date(self::DB_DATE_FORMAT) . $this->CI->session->userdata('nickname')), 0, 10) . "-" . substr(md5(date('Y-m-d')), 0, 3));
        $return['data']['status'] = $this->order_status[0];
        $return['data']['total'] = 0;
        $return['data']['products_count'] = 0;
        foreach ($cart_data as $key => $value) {
            $return['data']['total'] += $data['count'][$value['id']] * $value['product']['price_reduced'];
            $return['data']['gid_currency'] = $value['product']['gid_currency'];
            $return['data']['products_count'] += $data['count'][$value['id']];
        }

        empty($return["errors"]) and $this->notification($return["data"]);

        return $return;
    }

    public function validate_order($order_id, $data, $lang_id = null)
    {
        if (!isset($lang_id) || empty($lang_id)) {
            $lang_id = $this->pg_language->current_lang_id;
        }
        $return = array("errors" => array(), "data" => array());
        if (!empty($order_id)) {
            $return["data"]["id"] = $order_id;
        }
        if (!isset($data["status"]) && empty($data["canceled_sender"])) {
            $status = $this->get_status_by_order_id($order_id);
            if (!empty($data['for_friend']) && $status != $this->order_status[3]) {
                if (isset($data['user_id'])) {
                    $return["data"]["id_user"] = intval($data["user_id"]);
                    if (!empty($return["data"]["id_user"])) {
                        $this->CI->load->model("Users_model");
                        $user_data = $this->CI->Users_model->get_user_by_id($return["data"]["id_user"]);
                        $return['data']['user'] = $this->CI->Users_model->set_user_output_name($user_data);
                        $return['data']['status'] = $status = $this->order_status[1];
                    }
                }
            }
            if (!empty($data['for_myself'])) {
                $return["data"]["id_user"] = $this->CI->session->userdata('user_id');
                $return['data']['user'] = $this->CI->session->userdata('output_name');
            }
            if (empty($return["data"]["id_user"]) && $status != $this->order_status[1]) {
                if ($status != $this->order_status[3]) {
                    $return["errors"][] = l("error_select_user", "store");
                }
            }
            if (isset($data['shipping_id']) && $status != $this->order_status[1]) {
                $return["data"]["id_shipping"] = intval($data["shipping_id"]);
                $user_id = $this->CI->session->userdata('user_id');
                if ($status != $this->order_status[3]) {
                    $build_addresses = $this->build_addresses($data["address_id"], $user_id);
                    $return = array_merge_recursive($return, $build_addresses);
                }
                $this->CI->load->model('store/models/Store_shippings_model');
                $shippings_count = $this->CI->Store_shippings_model->get_shippings_count();
                if ($shippings_count != 0) {
                    if (empty($return["data"]["id_shipping"])) {
                        $return["errors"][] = l("error_select_shipping_method", "store");
                    } else {
                        $shipping_data = $this->CI->Store_shippings_model->get_shipping_by_id($return["data"]["id_shipping"], false, false, $lang_id);
                        $order_price = $this->get_order_price($order_id);
                        $return["data"]["shipping_name"] = $shipping_data["name"];
                        $return["data"]["total"] = $shipping_data["price"] + $order_price;
                        $return["data"]["is_formed"] = 1;
                    }
                } else {
                    $return["data"]["id_shipping"] = 'none';
                    $order_price = $this->get_order_price($order_id);
                    $return["data"]["total"] = $order_price;
                    $return["data"]["is_formed"] = 1;
                }
            }
            if (empty($data['agree_terms_delivery'])) {
                $return["errors"][] = l("error_agree_terms_delivery", "store");
            }
        } else {
            if (isset($data["canceled_sender"])) {
                $return['data']['status'] = $this->order_status[4];
                $return["data"]["id_user"] = $this->CI->session->userdata('user_id');
                $return['data']['user'] = $this->CI->session->userdata('output_name');
                $return["data"]["is_archive"] = 1;
            } else {
                $return['data']['status'] = strip_tags($data["status"]);
            }
            switch ($return['data']['status']) {
                case $this->order_status[3]:
                    $build_addresses = $this->build_addresses($data['address_id'], $data['user_id']);
                    $return = array_merge_recursive($return, $build_addresses);
                    break;
                case $this->order_status[8]:
                    $return["data"]["is_archive"] = 1;
                    break;
                case $this->order_status[6]:
                    if (empty($data["comment_log"])) {
                        $return["errors"][] = l("error_comment_rejection", "store");
                    } else {
                        $return["data"]["is_archive"] = 1;
                    }
                    break;
            }
        }

        if (isset($data['comment'])) {
            $return["data"]["comment"] = strip_tags($data["comment"]);
        }

        empty($return["errors"]) and $this->notification($return["data"], $data);

        return $return;
    }

    public function validate_order_product($cart_data, $data)
    {
        $return = array("errors" => array(), "data" => array());
        $this->CI->load->model("store/models/Store_categories_model");
        foreach ($cart_data as $key => $value) {
            if (isset($data['order_id'])) {
                $return['data'][$key]['id_order'] = intval($data['order_id']);
            }
            $return['data'][$key]['id_product'] = intval($value['product']['id']);
            $return['data'][$key]['name'] = $value['product']['name'];
            $return['data'][$key]['price'] = $value['product']['price_reduced'];
            $return['data'][$key]['gid_currency'] = $value['product']['gid_currency'];
            $return['data'][$key]["category_name"] = $this->CI->Store_categories_model->get_name_by_product_id($value['product']['id']);
            if (isset($data['count'][$value['id']])) {
                $return['data'][$key]['count'] = intval($data['count'][$value['id']]);
            }
            if (isset($data['options'][$value['id']]) && is_array($data['options'][$value['id']])) {
                foreach ($data['options'][$value['id']] as $k => $option) {
                    $data['options'][$value['id']][$k] = serialize(array($option => $option));
                }
            }
            if (!empty($data["options"][$value['id']])) {
                $return['data'][$key]["options"] = serialize($data['options'][$value['id']]);
            }
            if (isset($data['description'][$value['id']])) {
                $return['data'][$key]['description'] = trim(strip_tags($data['description'][$value['id']]));
            }
        }

        return $return;
    }

    private function build_addresses($address_id, $user_id)
    {
        if (isset($address_id)) {
            if (empty($address_id)) {
                $return["errors"][] = l("error_address_id", "store");
            } else {
                $this->CI->load->model('store/models/Store_users_shippings_model');
                $address_data = $this->CI->Store_users_shippings_model->get_address_by_id($address_id, $user_id);
                if (!empty($address_data["country"])) {
                    $return["data"]["shipping_country"] = $address_data["country"];
                } else {
                    $return["errors"][] = l("error_specified_country", "store");
                }
                if (!empty($address_data["region"])) {
                    $return["data"]["shipping_region"] = $address_data["region"];
                } else {
                    $return["errors"][] = l("error_specified_region", "store");
                }
                if (!empty($address_data["city"])) {
                    $return["data"]["shipping_city"] = $address_data["city"];
                } else {
                    $return["errors"][] = l("error_specified_city", "store");
                }
                if (!empty($address_data["address"])) {
                    $return["data"]["shipping_address"] = $address_data["address"];
                } else {
                    $return["errors"][] = l("error_specified_address", "store");
                }
                if (!empty($address_data["zip"])) {
                    $return["data"]["shipping_zip"] = $address_data["zip"];
                }
                if (!empty($address_data["phone"])) {
                    $return["data"]["contact_phone"] = $address_data["phone"];
                }
            }
        }

        return $return;
    }

    /**
     *  Send notification
     *
     *  @param array $data
     *  @param array $add_data
     *
     *  @return void
     */
    public function notification($data, $add_data = array())
    {
        if (!isset($data['status'])) {
            return;
        }
        
        $this->CI->load->model('Users_model');
        $this->CI->load->model('Notifications_model');
        switch ($data['status']) {
                case $this->order_status[0]:
                    $user = $this->CI->Users_model->get_user_by_id($data['id_customer']);
                    $alert = array(
                        "sender_nickname" => $data['customer_name'],
                        "code"            => $data['code'],
                        "status"          => l($this->order_status[0], 'store'),
                    );
                    $this->CI->Notifications_model->send_notification($user['email'], 'store_pending_payment', $alert);
                    break;
                case $this->order_status[1]:
                    $order = $this->get_order_by_id($data['id']);
                    $user = $this->CI->Users_model->get_user_by_id($order['id_customer']);
                    $alert = array(
                        "sender_nickname"    => $order['customer_name'],
                        "code"               => $order['code'],
                        "status"             => l($this->order_status[1], 'store'),
                        "recipient_nickname" => $order['user'],
                    );
                    $this->CI->Notifications_model->send_notification($user['email'], 'store_waits_user_consent', $alert);
                    break;
                case $this->order_status[2]:
                    $order = $this->get_order_by_id($data['id']);
                    $user = $this->CI->Users_model->get_user_by_id($order['id_customer']);
                    $alert = array(
                        "sender_nickname"    => $order['customer_name'],
                        "code"               => $order['code'],
                        "recipient_nickname" => $order['user'],
                    );
                    $this->CI->Notifications_model->send_notification($user['email'], 'store_rejected_recipient', $alert);
                    break;
                case $this->order_status[3]:
                    $order = $this->get_order_by_id($data['id']);
                    $user = $this->CI->Users_model->get_user_by_id($order['id_customer']);
                    $alert = array(
                        "sender_nickname"    => $order['customer_name'],
                        "code"               => $order['code'],
                        "recipient_nickname" => $order['user'],
                    );
                    $this->CI->Notifications_model->send_notification($user['email'], 'store_confirmed_recipient', $alert);
                    break;
                case $this->order_status[4]:
                    $order = $this->get_order_by_id($data['id']);
                    $user = $this->CI->Users_model->get_user_by_id($order['id_customer']);
                    $alert = array(
                        "sender_nickname" => $order['customer_name'],
                        "code"            => $order['code'],
                    );
                    $this->CI->Notifications_model->send_notification($user['email'], 'store_canceled_sender', $alert);
                    break;
                case $this->order_status[5]:
                    $order = $this->get_order_by_id($data['id']);
                    $user = $this->CI->Users_model->get_user_by_id($order['id_customer']);
                    $alert = array(
                        "sender_nickname" => $order['customer_name'],
                        "code"            => $order['code'],
                    );
                    $this->CI->Notifications_model->send_notification($user['email'], 'store_paid', $alert);
                    break;
                case $this->order_status[6]:
                    $this->CI->load->model("store/models/Store_orders_log_model");
                    $order = $this->get_order_by_id($data['id']);
                    $user = $this->CI->Users_model->get_user_by_id($order['id_customer']);
                    $params['where']['id_order'] = $data['id'];
                    $alert = array(
                        "comment" => $add_data["comment_log"],
                    );
                    $this->CI->Notifications_model->send_notification($user['email'], 'store_rejected_admin', $alert);
                    break;
                case $this->order_status[7]:
                    $order = $this->get_order_by_id($data['id']);
                    $user = $this->CI->Users_model->get_user_by_id($order['id_customer']);
                    $alert = array(
                        "sender_nickname" => $order['customer_name'],
                        "code"            => $order['code'],
                    );
                    $this->CI->Notifications_model->send_notification($user['email'], 'store_during_delivery', $alert);
                    break;
                case $this->order_status[8]:
                    $order = $this->get_order_by_id($data['id']);
                    $from = $this->CI->Users_model->get_user_by_id($order['id_customer']);
                    $alert = array(
                        "sender_nickname"    => $order['customer_name'],
                        "code"               => $order['code'],
                        "recipient_nickname" => $order['user'],
                    );
                    $this->CI->Notifications_model->send_notification($from['email'], 'store_delivered_from', $alert);
                    if ($order['id_customer'] != $order['id_user']) {
                        $for = $this->CI->Users_model->get_user_by_id($order['id_user']);
                        $this->CI->Notifications_model->send_notification($for['email'], 'store_delivered_for', $alert);
                    }
                    break;
        }
    }

    public function set_order_archive($order_ids)
    {
        foreach ($order_ids as $id) {
            $params['id'] = $id;
            $params['is_archive'] = 1;
            $this->save_order($params);
        }
    }

    public function set_order_status($order_ids, $status)
    {
        foreach ($order_ids as $id) {
            $params['id'] = $id;
            $params['status'] = $status;
            $this->save_order($params);
        }
    }

    public function save_order($params)
    {
        $order_id = $params['id'];
        $is_new = is_null($order_id);
        if ($is_new) {
            $params['date_created'] = $params['date_updated'] = date(self::DB_DATE_FORMAT);
            $this->DB->insert(TABLE_STORE_ORDERS, $params);
            $order_id = $this->DB->insert_id();
        } else {
            $params['date_updated'] = date(self::DB_DATE_FORMAT);
            $this->DB->where('id', $params['id']);
            $this->DB->update(TABLE_STORE_ORDERS, $params);
        }
        
        $this->sendEvent(Store_model::EVENT_ORDER_CHANGED, [
            'id' => $order_id,
            'type' => Store_model::TYPE_STORE_ORDER,
            'status' => $is_new ? Store_model::STATUS_ORDER_ADDED : Store_model::STATUS_ORDER_SAVED,
        ]);

        return $order_id;
    }

    public function save_order_product($order_id, $params)
    {
        $this->delete_order_product($order_id);
        foreach ($params as $value) {
            $this->DB->insert(TABLE_STORE_ORDERS_PRODUCTS, $value);
            $this->DB->insert_id();
        }
    }

    public function delete_order($id)
    {
        $order = $this->get_order_by_id($id);
        $user_id = $this->CI->session->userdata('user_id');
        if (isset($id) && $order['id_customer'] == $user_id) {
            $this->DB->where('id', $id);
            $this->DB->delete(TABLE_STORE_ORDERS);
            $this->delete_order_product($id);

            $this->sendEvent(Store_model::EVENT_ORDER_CHANGED, [
                'id' => $id,
                'type' => Store_model::TYPE_STORE_ORDER,
                'status' => STATUS_ORDER_DELETED,
            ]);

            return true;
        }

        return false;
    }

    public function order_rejection($id)
    {
        $order = $this->get_order_by_id($id);
        $user_id = $this->CI->session->userdata('user_id');
        if (isset($id) && $order['id_user'] == $user_id) {
            $params['id'] = $id;
            $params['status'] = $this->order_status[2];
            $params['is_archive'] = 1;
            $this->save_order($params);

            return true;
        }

        return false;
    }

    public function delete_order_product($order_id)
    {
        if (isset($order_id)) {
            $this->DB->where('id_order', $order_id);
            $this->DB->delete(TABLE_STORE_ORDERS_PRODUCTS);
        }
    }

    /**
     * Service functions
     *
     * @param integer $user_id
     * @param array   $data
     * @param array   $service_data
     * @param string  $price
     *
     * @return array
     */
    public function service_validate_store($user_id, $data, $service_data = array(), $price = '')
    {
        $return = array("errors" => array(), "data" => $data);

        return $return;
    }

    /**
     * Service buy store
     *
     * @param integer $id_user
     * @param string  $price
     * @param array   $service
     * @param array   $template
     * @param array   $payment_data
     * @param integer $users_package_id
     * @param integer $count
     *
     * @return void
     */
    public function service_buy_store($id_user, $price, $service, $template, $payment_data, $users_package_id = 0, $count = 1)
    {
        $this->CI->load->model("store/models/Store_shippings_model");
        $shippings_count = $this->CI->Store_shippings_model->get_shippings_count();
        $sum = 0;
        if ($shippings_count != 0) {
            $shipping = $this->CI->Store_shippings_model->get_shipping_by_id($payment_data['user_data']['id_shipping']);
            $sum = floatval($shipping['price']);
        }
        $order_id = $payment_data['user_data']['id_order_payment'];
        $products = $this->get_order_products_by_order_id($order_id, false);
        foreach ($products as $product) {
            $sum += $product['count'] * floatval($product['price']);
        }
        $sum = (string) $sum;
        if ($sum != $price) {
            return false;
        }
        $params['id'] = $order_id;
        $params['status'] = $this->order_status[5];
        $this->save_order($params);
        $this->CI->load->model('store/models/Store_orders_log_model');
        $validate_log = $this->CI->Store_orders_log_model->validate_order_log($order_id, $params);
        $this->CI->Store_orders_log_model->save_order_log($validate_log['data']);
        $this->CI->system_messages->addMessage(View::MSG_SUCCESS, l('note_paid_order', 'store'));
        $this->notification($params);

        return;
    }

    public function service_activate_store()
    {
        $this->CI->system_messages->addMessage(View::MSG_SUCCESS, l('link_order_log', 'services'));
    }
    
    public function sendEvent($event_gid, $event_data)
    {   
        $event_data['module'] = Store_model::MODULE_GID;
        $event_data['action'] = $event_gid;
        
        $event = new EventStore();
        $event->setData($event_data);
        
        $event_handler = EventDispatcher::getInstance();
        $event_handler->dispatch($event_gid, $event);
    }
}
