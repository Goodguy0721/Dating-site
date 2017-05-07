<?php

if (!defined('__TVCORP_HANDLER__')) {
    define('__TVCORP_HANDLER__', 1);
}

require_once 'whois.parser.php';

class tvcorp_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner'           => 'Registrant',
                'admin'           => 'Admin',
                'tech'            => 'Technical',
                'billing'         => 'Billing',
                'domain.nserver.' => 'Domain servers:',
                'domain.created'  => 'Record created on',
                'domain.expires'  => 'Record expires on',
                    );

        return easy_parser($data_str, $items, 'mdy');
    }
}
