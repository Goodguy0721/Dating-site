<?php

if (!defined('__IT_HANDLER__')) {
    define('__IT_HANDLER__', 1);
}

require_once 'whois.parser.php';

class it_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
            'domain.name'    => 'Domain:',
            'domain.nserver' => 'Nameservers',
            'domain.status'  => 'Status:',
            'domain.expires' => 'Expire Date:',
            'owner'          => 'Registrant',
            'admin'          => 'Admin Contact',
            'tech'           => 'Technical Contacts',
            'registrar'      => 'Registrar',
                    );

        $extra = array(
            'address:'        => 'address.',
            'contactid:'      => 'handle',
            'organization:'   => 'organization',
            'created:'        => 'created',
            'last update:'    => 'changed',
                    );

        $r['regrinfo'] = easy_parser($data_str['rawdata'], $items, 'ymd', $extra);

        if (isset($r['regrinfo']['registrar'])) {
            $r['domain']['registrar'] = $r['regrinfo']['registrar'][0];
            unset($r['regrinfo']['registrar']);
        }

        $r['regyinfo'] = array(
                  'registrar' => 'IT-Nic',
                  'referrer'  => 'http://www.nic.it/',
                  );

        return ($r);
    }
}
