<?php

if (!defined('__IE_HANDLER__')) {
    define('__IE_HANDLER__', 1);
}

require_once 'whois.parser.php';

class ie_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
            'nic-hdl' => 'handle',
            'person'  => 'name',
            'renewal' => 'expires',
            );

        $contacts = array(
                    'admin-c' => 'admin',
                    'tech-c'  => 'tech',
                        );

        $r['regyinfo'] = array(
                    'referrer'  => 'http://www.domainregistry.ie',
                    'registrar' => 'IE Domain Registry',
                    );

        $reg = generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

        if (isset($reg['domain']['descr'])) {
            $reg['owner']['organization'] = $reg['domain']['descr'][0];
            unset($reg['domain']['descr']);
        }

        $r['regrinfo'] = $reg;

        return ($r);
    }
}
