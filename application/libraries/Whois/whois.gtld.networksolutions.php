<?php

/* netsol.whois 2.0	david saez */

if (!defined('__NETWORKSOLUTIONS_HANDLER__')) {
    define('__NETWORKSOLUTIONS_HANDLER__', 1);
}

require_once 'whois.parser.php';

class networksolutions_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'              => 'Registrant:',
                  'admin'              => 'Administrative Contact',
                  'tech'               => 'Technical Contact',
                  'domain.name'        => 'Domain Name:',
                  'domain.nserver.'    => 'Domain servers in listed order:',
                  'domain.created'     => 'Record created on',
                  'domain.expires'     => 'Record expires on',
                      );

        return easy_parser($data_str, $items, 'dmy', false, true, true);
    }
}
