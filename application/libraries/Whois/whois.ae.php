<?php

/* Based upon atnic.whois  2.00    David Saez <david@ols.es> */

if (!defined('__AE_HANDLER__')) {
    define('__AE_HANDLER__', 1);
}

require_once 'whois.parser.php';

class ae_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
            'fax-no'         => 'fax',
            'e-mail'         => 'email',
            'nic-hdl'        => 'handle',
            'person'         => 'name',
            );

        $contacts = array(
                    'owner-c'   => 'owner',
                    'admin-c'   => 'admin',
                    'tech-c'    => 'tech',
                    'billing-c' => 'billing',
                    'zone-c'    => 'zone',
                        );

        $r['regyinfo'] = array(
                    'referrer'  => 'http://www.nic.ae',
                    'registrar' => 'UAENIC',
                    );

        $reg = generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

        if (isset($reg['domain']['remarks'])) {
            unset($reg['domain']['remarks']);
        }

        if (isset($reg['domain']['descr'])) {
            $reg['owner'] = get_contact($reg['domain']['descr']);
            unset($reg['domain']['descr']);
        }

        $r['regrinfo'] = $reg;

        return ($r);
    }
}
