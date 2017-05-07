<?php

if (!defined('__LT_HANDLER__')) {
    define('__LT_HANDLER__', 1);
}

require_once 'whois.parser.php';

class lt_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
                    'contact nic-hdl:' => 'handle',
                    'contact name:'    => 'name',
                    );

        $items = array(
                        'admin'               => 'Contact type:      Admin',
                        'tech'                => 'Contact type:      Tech',
                        'zone'                => 'Contact type:      Zone',
                        'owner.name'          => 'Registrar:',
                        'owner.email'         => 'Registrar email:',
                        'domain.status'       => 'Status:',
                        'domain.created'      => 'Registered:',
                        'domain.changed'      => 'Last updated:',
                        'domain.nserver.'     => 'NS:',
                        ''                    => '%',
                        );

        $r['regrinfo'] = easy_parser($data_str['rawdata'], $items, 'ymd', $translate);

        $r['regyinfo'] = array(
                    'referrer'  => 'http://www.domreg.lt',
                    'registrar' => 'DOMREG.LT',
                    );

        return ($r);
    }
}
