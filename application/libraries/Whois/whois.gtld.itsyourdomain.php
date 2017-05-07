<?php

if (!defined('__ITSYOURDOMAIN_HANDLER__')) {
    define('__ITSYOURDOMAIN_HANDLER__', 1);
}

require_once 'whois.parser.php';

class itsyourdomain_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner'           => 'Registrant',
                'admin'           => 'Administrative',
                'tech'            => 'Technical',
                'billing'         => 'Billing',
                'domain.name'     => 'Domain:',
                'domain.nserver.' => 'Domain Name Servers:',
                'domain.created'  => 'Record created on ',
                'domain.expires'  => 'Record expires on ',
                'domain.changed'  => 'Record last updated on ',
                    );

        return easy_parser($data_str, $items, 'mdy');
    }
}
