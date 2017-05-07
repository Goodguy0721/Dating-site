<?php

if (!defined('__SRSPLUS_HANDLER__')) {
    define('__SRSPLUS_HANDLER__', 1);
}

require_once 'whois.parser.php';

class srsplus_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'          => 'Registrant:',
                  'admin'          => 'Administrative',
                  'tech'           => 'Technical',
                  'billing'        => 'Billing',
                  'domain.name'    => 'Domain Name:',
                  'domain.nserver' => 'Domain servers:',
                  'domain.created' => 'Record created on',
                  'domain.expires' => 'Record expires on',
                      );

        return easy_parser($data_str, $items, 'ymd', false, true, true);
    }
}
