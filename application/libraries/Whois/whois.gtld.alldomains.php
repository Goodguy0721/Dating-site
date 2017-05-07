<?php

if (!defined('__ALLDOMAINS_HANDLER__')) {
    define('__ALLDOMAINS_HANDLER__', 1);
}

require_once 'whois.parser.php';

class alldomains_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner'           => 'Registrant:',
                'admin'           => 'Administrative',
                'tech'            => 'Technical',
                'domain.name'     => 'Domain name:',
                'domain.sponsor'  => 'Registrar:',
                'domain.nserver.' => 'Domain servers in listed order:',
                    );

        return easy_parser($data_str, $items, 'ymd');
    }
}
