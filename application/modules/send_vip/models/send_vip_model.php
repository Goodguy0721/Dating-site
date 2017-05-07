<?php

namespace Pg\Modules\Send_vip\Models;

/**
 * Send_vip module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2015 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define('SEND_VIP_TABLE', DB_PREFIX . 'send_vip');

/**
 * Base model
 *
 * @package 	PG_Dating
 * @subpackage 	Send_vip
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2015 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Send_vip_model extends \Model
{
    const MODULE_GID = 'send_vip';

    /**
     * Payment type as write off from account
     */
    const PAYMENT_TYPE_ACCOUNT = 'account';

    /**
     * Payment type as write off from account and direct payment
     */
    const PAYMENT_TYPE_ACCOUNT_AND_DIRECT = 'account_and_direct';

    /**
     * Payment type as direct payment
     */
    const PAYMENT_TYPE_DIRECT = 'direct';

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
     * Send_vip object properties
     *
     * @var array
     */
    protected $_fields = array(
        'id',
        'id_user',
        'id_sender',
        'id_membership',
        'status',
        'declined_by_sender',
        'date_created',
        'transfer_fee',
    );

    public function getAllowedPaymentTypes()
    {
        return array(
            self::PAYMENT_TYPE_ACCOUNT,
            self::PAYMENT_TYPE_DIRECT,
            self::PAYMENT_TYPE_ACCOUNT_AND_DIRECT,
        );
    }

    /**
     * Class constructor
     *
     * @return Memberships_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
    }

    public function validateSettings($settings)
    {
        $return = array('errors' => array(), 'data' => array());
        if (isset($settings['use_fee'])) {
            if (in_array($settings['use_fee'], array('use', ''))) {
                $return['data']['use_fee'] = $settings['use_fee'];
            } else {
                $return['errors'][] = l('admin_errors_use_fee', 'send_vip');
            }
        }
        if (isset($settings['fee_price']) && !empty($settings['fee_price'])) {
            $return['data']['fee_price'] = floatval($settings['fee_price']);
        }
        if (isset($settings['fee_currency']) && !empty($settings['fee_currency'])) {
            if (in_array($settings['fee_currency'], $settings['currencies'])) {
                $return['data']['fee_currency'] = $settings['fee_currency'];
            } else {
                $return['errors'][] = l('admin_errors_fee_currency', 'send_vip');
            }
        }
        if (isset($settings['to_whom'])) {
            if (in_array($settings['to_whom'], array('to_all', 'to_friends'))) {
                $return['data']['to_whom'] = $settings['to_whom'];
            } else {
                $return['errors'][] = l('admin_errors_to_whom', 'send_vip');
            }
        }
        if (isset($settings['transfer_type'])) {
            if (in_array($settings['transfer_type'], $this->getAllowedPaymentTypes())) {
                $return['data']['transfer_type'] = $settings['transfer_type'];
            } else {
                $return['errors'][] = l('admin_errors_transfer_type', 'send_vip');
            }
        }

        return $return;
    }

    public function getTransaction($transaction_id = null, $page = null, $items_on_page = 0)
    {
        $result    = array();
        $db_fields = $this->_fields;
        $db_table  = SEND_VIP_TABLE;
        $this->DB->select(implode(", ", $db_fields))->from($db_table);
        if (isset($transaction_id)) {
            $this->DB->where('id', $transaction_id);
            $this->DB->order_by('date_created DESC');
            $result = $this->DB->get()->row_array();
        } else {
            $this->DB->order_by('date_created DESC');
            if (!is_null($page)) {
                $page = intval($page) ? intval($page) : 1;
                $this->DB->limit($items_on_page, $items_on_page * ($page - 1));
            }

            $result = $this->DB->get()->result_array();
        }

        return $result;
    }

    public function getTransactionsCount()
    {
        $db_table = SEND_VIP_TABLE;
        $result   = $this->DB->from($db_table)->count_all_results();

        return $result;
    }

    public function validateTransaction($transaction_id, $validate_data, $koef = null, $transfer_fee = null)
    {
        $return = array('errors' => array(), 'data' => array());
        $this->load->model('Users_model');
        $this->load->model('memberships/models/Memberships_model');

        $user = $this->Users_model->get_user_by_id($this->session->userdata('user_id'));

        if (empty($validate_data['id_user'])) {
            $return['errors'][] = l('send_vip_no_recipient', 'send_vip');
        } else {
            $return['data']['id_user'] = $validate_data['id_user'];
        }

        if (!isset($validate_data['id_sender'])) {
            $return['errors'][] = l('send_vip_no_sender', 'send_vip');
        } else {
            $return['data']['id_sender'] = $validate_data['id_sender'];
        }

        if (isset($validate_data['id_membership'])) {
            $membership = $this->CI->Memberships_model->getMembershipById($validate_data['id_membership']);
            if (empty($membership)) {
                $return['errors'][] = l('send_vip_wrong_membership', 'send_vip');
            } else {
                $return['data']['id_membership']   = $validate_data['id_membership'];
                $lang_id                           = $this->pg_language->current_lang_id;
                $return['data']['membership_name'] = $membership['name_' . $lang_id];

                if (!isset($koef)) {
                    $koef = 0;
                }
                if (($koef > 0) && ($koef < 1)) {
                    $price                          = (float) $membership['price'] + (float) $membership['price'] * $koef;
                    $return['data']['transfer_fee'] = number_format((float) $membership['price'] * $koef, 2, '.', '');
                } elseif ($koef >= 1) {
                    $price                          = (float) $membership['price'] + $transfer_fee;
                    $return['data']['transfer_fee'] = number_format((float) $transfer_fee, 2, '.', '');
                } else {
                    $price                          = $membership['price'];
                    $return['data']['transfer_fee'] = 0;
                }
                if ($price > $user['account']) {
                    $return['errors'][] = l('send_vip_few_funds', 'send_vip');
                } else {
                    $return['data']['amount']      = number_format(floatval($membership['price']), 2, '.', '');
                    $return['data']['full_amount'] = number_format((float) $price, 2, '.', '');
                }
            }
        } else {
            $return['errors'][] = l('send_vip_no_membership', 'send_vip');
        }

        return $return;
    }

    public function saveTransaction($transaction_id, $transaction_data)
    {
        unset($transaction_data['membership_name']);
        unset($transaction_data['amount']);
        unset($transaction_data['full_amount']);
        if (!$transaction_id) {
            $this->DB->insert(SEND_VIP_TABLE, $transaction_data);
            $transaction_id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $transaction_data['id']);
            $this->DB->update(SEND_VIP_TABLE, $transaction_data);
        }

        return $transaction_id;
    }

    public function statusTransaction($transaction_id, $status)
    {
        $transaction_data = $this->getTransaction($transaction_id);
        if ($transaction_data['status'] == 'waiting') {
            $this->load->model('Users_model');
            $this->load->model('Memberships_model');

            $user       = $this->Users_model->get_user_by_id($transaction_data['id_user']);
            $membership = $this->CI->Memberships_model->getMembershipById($transaction_data['id_membership']);
            if ($status == 'approve') {
                $user                         = $this->Users_model->get_user_by_id($transaction_data['id_user']);
                $transaction_data['status']   = 'approved';
                $membership['services_array'] = unserialize($membership['services']);
                $this->CI->Memberships_model->applyMembership($membership, $transaction_data['id_user'], $membership['price']);
                $return                       = l('send_vip_approved', 'send_vip');
            } elseif ($status == 'decline') {
                $user                       = $this->Users_model->get_user_by_id($transaction_data['id_sender']);
                $transaction_data['status'] = 'declined';
                if ($this->CI->session->userdata('user_id') == $user['id']) {
                    $transaction_data['declined_by_sender'] = 1;
                    $return                                 = l('send_vip_declined_by_me', 'send_vip');
                } elseif ($this->CI->session->userdata('user_id') == $transaction_data['id_user']) {
                    $return = l('send_vip_declined_by_me', 'send_vip');
                } else {
                    $return = l('send_vip_declined', 'send_vip');
                }
                $user['account'] += $membership['price'];
                $this->Users_model->save_user($transaction_data['id_sender'], $user);
            }
            $this->saveTransaction($transaction_id, $transaction_data);
        } else {
            $return = l('admin_view_no_data', 'send_vip');
        }

        return $return;
    }

    /**
     * Update membership payment status
     *
     * callback method for payment module
     *
     * Expected status values: 1, 0, -1
     *
     * @param array   $payment        payment data
     * @param integer $payment_status payment status
     *
     * @return void
     */
    public function paymentSendVipStatus($payment, $payment_status)
    {
        if ($payment_status != 1) {
            return;
        }
        $transaction_data                   = $payment["payment_data"]["transaction"];
        $transaction_data['id_transaction'] = $this->saveTransaction(null, $transaction_data);
        $transaction_data['currency_gid']   = $payment['currency_gid'];
        $this->sendLetter($transaction_data);
    }

    public function sendLetter($data)
    {
        $this->CI->load->model('notifications/models/Notifications_model');
        $receiver = $this->CI->Users_model->get_user_by_id($data['id_user']);
        $sender = $this->CI->Users_model->get_user_by_id($data['id_sender']);

        $lang_id                     = $this->CI->pg_language->current_lang_id;
        $membership                  = $this->CI->Memberships_model->getMembershipById($data['id_membership'], array($lang_id));
        $template_data['membership'] = $membership['name_' . $lang_id];
        $template_data['approve']    = l('send_vip_approve', 'send_vip');
        $template_data['decline']    = l('send_vip_decline', 'send_vip');
        $template_data['id']         = $data['id_transaction'];
        $template_data['sender']     = $sender['nickname'];

        $return = $this->CI->Notifications_model->send_notification($receiver['email'], 'send_vip_msg', $template_data);

        return $return;
    }

    /**
     *  Site map xml
     *
     *  @return array
     */
    public function getSitemapXmlUrls()
    {
        $this->CI->load->helper('seo');
        $return = array();

        return $return;
    }
}
