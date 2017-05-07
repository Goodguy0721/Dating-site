<?php

if (!defined('__MONIKER_HANDLER__')) {
    define('__MONIKER_HANDLER__', 1);
}

require_once 'whois.parser.php';

class moniker_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'           => 'Registrant',
                  'admin'           => 'Administrative ',
                  'tech'            => 'Technical ',
                  'billing'         => 'Billing ',
                  'domain.name'     => 'Domain Name:',
                  'domain.nserver.' => 'Domain servers in listed order:',
                  'domain.created'  => 'Record created on: ',
                  'domain.expires'  => 'Domain Expires on: ',
                  'domain.changed'  => 'Database last updated on: ',
                      );

        return easy_parser($data_str, $items, 'ymd');
    }
}
