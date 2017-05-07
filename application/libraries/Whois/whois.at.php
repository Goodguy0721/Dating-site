<?php

/* atnic.whois	0.99	Martin Pircher <martin@pircher.net> */
/* dedicated to klopfer, *24.07.1999, +21.01.2001         */
/* based upon brnic.whois by Marcelo Sanches  msanches@sitebox.com.br */

if (!defined('__AT_HANDLER__')) {
    define('__AT_HANDLER__', 1);
}

require_once 'whois.parser.php';

class at_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
            'fax-no'         => 'fax',
            'e-mail'         => 'email',
            'nic-hdl'        => 'handle',
            'person'         => 'name',
            'personname'     => 'name',
            'street address' => 'address.street',
            'city'           => 'address.city',
            'postal code'    => 'address.pcode',
            'country'        => 'address.country',
            );

        $contacts = array(
                    'owner-c'   => 'owner',
                    'admin-c'   => 'admin',
                    'tech-c'    => 'tech',
                    'billing-c' => 'billing',
                    'zone-c'    => 'zone',
                        );

        $r['regyinfo'] = array(
                    'referrer'  => 'http://www.nic.at',
                    'registrar' => 'NIC-AT',
                    );

        $reg = generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

        if (isset($reg['domain']['remarks'])) {
            unset($reg['domain']['remarks']);
        }

        if (isset($reg['domain']['descr'])) {
            while (list($key, $val) = each($reg['domain']['descr'])) {
                $v = trim(substr(strstr($val, ':'), 1));
                if (strstr($val, '[organization]:')) {
                    $reg['owner']['organization'] = $v;
                    continue;
                }
                if (strstr($val, '[phone]:')) {
                    $reg['owner']['phone'] = $v;
                    continue;
                }
                if (strstr($val, '[fax-no]:')) {
                    $reg['owner']['fax'] = $v;
                    continue;
                }
                if (strstr($val, '[e-mail]:')) {
                    $reg['owner']['email'] = $v;
                    continue;
                }

                $reg['owner']['address'][$key] = $v;
            }

            if (isset($reg['domain']['descr'])) {
                unset($reg['domain']['descr']);
            }
        }

        $r['regrinfo'] = $reg;

        return ($r);
    }
}
