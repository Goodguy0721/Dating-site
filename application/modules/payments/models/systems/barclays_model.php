<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Barclays payment system driver model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Barclays_model extends Payment_driver_model
{
    public $payment_data = array();
    public $settings = array(
        "seller_id"   => array("type" => "text", "content" => "string", "size" => "middle"),
        "secret_word" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "oid"               => "id_payment",
        "total"             => "amount",
        "transactionstatus" => "transaction_status",
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        $currency = $this->get_currency_num($payment_data["currency_gid"]);

        $params = "clientid=" . $system_settings["settings_data"]["seller_id"];
        $params .= "&password=" . $system_settings["settings_data"]["secret_word"];
        $params .= "&oid=" . $payment_data["id_payment"];
        $params .= "&chargetype=Auth";
        $params .= "&currencycode=" . $currency;
        $params .= "&total=" . $payment_data["amount"];
        $response = $this->pullpage("secure2.epdq.co.uk", "/cgi-bin/CcxBarclaysEpdqEncTool.e", $params);
        $response_lines = explode("\n", $response);
        $response_line_count = count($response_lines);
        for ($i = 0; $i < $response_line_count; ++$i) {
            if (preg_match('/epdqdata/', $response_lines[$i])) {
                $strEPDQ = $response_lines[$i];
            }
        }
        $tmp = explode("\"", $strEPDQ);
        $strEPDQ = $tmp[1];

        $send_data = array(
            "clientid"            => $system_settings["settings_data"]["seller_id"],
            "secret_word"         => $system_settings["settings_data"]["secret_word"],
            "total"               => $payment_data["amount"],
            "currencycode"        => $currency,
            "oid"                 => $payment_data["id_payment"],
            "merchantdisplayname" => $payment_data["payment_data"]["name"],
            "chargetype"          => 'Auth',
            "epdqdata"            => $strEPDQ,
            "returnurl"           => site_url() . "payments/responce/barclays",
        );

        $this->send_data("https://secure2.epdq.co.uk/cgi-bin/CcxBarclaysEpdq.e", $send_data, "post");

        return $return;
    }

    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "exit");

        foreach ($this->variables as $payment_var => $site_var) {
            $return["data"][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
        }

        //// get status
        $return["data"]["status"] = 0;
        if (isset($return["data"]['transaction_status'])) {
            switch ($return["data"]['transaction_status']) {
                case "success": $return["data"]["status"] = 1;
                    break;
                default: $return["data"]["status"] = -1;
                    break;
            }
        }

        return $return;
    }

    public function get_settings_map()
    {
        foreach ($this->settings as $param_id => $param_data) {
            $this->settings[$param_id]["name"] = l('system_field_' . $param_id, 'payments');
        }

        return $this->settings;
    }

    public function pullpage($host, $usepath, $postdata = "")
    {
        $fp = fsockopen($host, 80, $errno, $errstr, 60);
        if (!$fp) {
            print "$errstr ($errno)<br>\n";
        } else {
            fputs($fp, "POST $usepath HTTP/1.0\n");
            $strlength = strlen($postdata);
            fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
            fputs($fp, "Content-length: " . $strlength . "\n\n");
            fputs($fp, $postdata . "\n\n");
            $output = "";
            while (!feof($fp)) {
                $output .= fgets($fp, 1024);
            }
            #close the socket connection
            fclose($fp);
        }

        return $output;
    }

    public function get_currency_num($code)
    {
        $codes = array("AFN" => "971", "EUR" => "978", "ALL" => "008", "DZD" => "012", "USD" => "840", "EUR" => "978", "AOA" => "973", "XCD" => "951", "XCD" => "951", "ARS" => "032", "AMD" => "051", "AWG" => "533", "AUD" => "036", "EUR" => "978", "AZN" => "944", "BSD" => "044", "BHD" => "048", "BDT" => "050", "BBD" => "052", "BYR" => "974", "EUR" => "978", "BZD" => "084", "XOF" => "952", "BMD" => "060", "INR" => "356", "BTN" => "064", "BOB" => "068", "BOV" => "984", "USD" => "840", "BAM" => "977", "BWP" => "072", "NOK" => "578", "BRL" => "986", "USD" => "840", "BND" => "096", "BGN" => "975", "XOF" => "952", "BIF" => "108", "KHR" => "116", "XAF" => "950", "CAD" => "124", "CVE" => "132", "KYD" => "136", "XAF" => "950", "XAF" => "950", "CLP" => "152", "CLF" => "990", "CNY" => "156", "AUD" => "036", "AUD" => "036", "COP" => "170", "COU" => "970", "KMF" => "174", "XAF" => "950", "CDF" => "976", "NZD" => "554", "CRC" => "188", "XOF" => "952", "HRK" => "191", "CUP" => "192", "CUC" => "931", "ANG" => "532", "EUR" => "978", "CZK" => "203", "DKK" => "208", "DJF" => "262", "XCD" => "951", "DOP" => "214", "USD" => "840", "EGP" => "818", "SVC" => "222", "USD" => "840", "XAF" => "950", "ERN" => "232", "EUR" => "978", "ETB" => "230", "EUR" => "978", "FKP" => "238", "DKK" => "208", "FJD" => "242", "EUR" => "978", "EUR" => "978", "EUR" => "978", "XPF" => "953", "EUR" => "978", "XAF" => "950", "GMD" => "270", "GEL" => "981", "EUR" => "978", "GHS" => "936", "GIP" => "292", "EUR" => "978", "DKK" => "208", "XCD" => "951", "EUR" => "978", "USD" => "840", "GTQ" => "320", "GBP" => "826", "GNF" => "324", "XOF" => "952", "GYD" => "328", "HTG" => "332", "USD" => "840", "AUD" => "036", "EUR" => "978", "HNL" => "340", "HKD" => "344", "HUF" => "348", "ISK" => "352", "INR" => "356", "IDR" => "360", "XDR" => "960", "IRR" => "364", "IQD" => "368", "EUR" => "978", "GBP" => "826", "ILS" => "376", "EUR" => "978", "JMD" => "388", "JPY" => "392", "GBP" => "826", "JOD" => "400", "KZT" => "398", "KES" => "404", "AUD" => "036", "KPW" => "408", "KRW" => "410", "KWD" => "414", "KGS" => "417", "LAK" => "418", "LVL" => "428", "LBP" => "422", "LSL" => "426", "ZAR" => "710", "LRD" => "430", "LYD" => "434", "CHF" => "756", "LTL" => "440", "EUR" => "978", "MOP" => "446", "MKD" => "807", "MGA" => "969", "MWK" => "454", "MYR" => "458", "MVR" => "462", "XOF" => "952", "EUR" => "978", "USD" => "840", "EUR" => "978", "MRO" => "478", "MUR" => "480", "EUR" => "978", "XUA" => "965", "MXN" => "484", "MXV" => "979", "USD" => "840", "MDL" => "498", "EUR" => "978", "MNT" => "496", "EUR" => "978", "XCD" => "951", "MAD" => "504", "MZN" => "943", "MMK" => "104", "NAD" => "516", "ZAR" => "710", "AUD" => "036", "NPR" => "524", "EUR" => "978", "XPF" => "953", "NZD" => "554", "NIO" => "558", "XOF" => "952", "NGN" => "566", "NZD" => "554", "AUD" => "036", "USD" => "840", "NOK" => "578", "OMR" => "512", "PKR" => "586", "USD" => "840", "PAB" => "590", "USD" => "840", "PGK" => "598", "PYG" => "600", "PEN" => "604", "PHP" => "608", "NZD" => "554", "PLN" => "985", "EUR" => "978", "USD" => "840", "QAR" => "634", "EUR" => "978", "RON" => "946", "RUB" => "643", "RWF" => "646", "SHP" => "654", "XCD" => "951", "XCD" => "951", "EUR" => "978", "EUR" => "978", "XCD" => "951", "EUR" => "978", "WST" => "882", "EUR" => "978", "STD" => "678", "SAR" => "682", "XOF" => "952", "RSD" => "941", "SCR" => "690", "SLL" => "694", "SGD" => "702", "ANG" => "532", "XSU" => "994", "EUR" => "978", "EUR" => "978", "SBD" => "090", "SOS" => "706", "ZAR" => "710", "EUR" => "978", "LKR" => "144", "SDG" => "938", "SRD" => "968", "NOK" => "578", "SZL" => "748", "SEK" => "752", "CHF" => "756", "CHE" => "947", "CHW" => "948", "SYP" => "760", "TWD" => "901", "TJS" => "972", "TZS" => "834", "THB" => "764", "USD" => "840", "XOF" => "952", "NZD" => "554", "TOP" => "776", "TTD" => "780", "TND" => "788", "TRY" => "949", "TMT" => "934", "USD" => "840", "AUD" => "036", "UGX" => "800", "UAH" => "980", "AED" => "784", "GBP" => "826", "USD" => "840", "USN" => "997", "USS" => "998", "USD" => "840", "UYU" => "858", "UYI" => "940", "UZS" => "860", "VUV" => "548", "EUR" => "978", "VEF" => "937", "VND" => "704", "USD" => "840", "USD" => "840", "XPF" => "953", "MAD" => "504", "YER" => "886", "ZMK" => "894", "ZWL" => "932", "XBA" => "955", "XBB" => "956", "XBC" => "957", "XBD" => "958", "XFU" => "Nil", "XTS" => "963", "XXX" => "999", "XAU" => "959", "XPD" => "964", "XPT" => "962", "XAG" => "961");

        return $codes[$code];
    }
}
