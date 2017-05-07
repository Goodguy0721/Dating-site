<?php

if (!defined('__ASCIO_HANDLER__')) {
    define('__ASCIO_HANDLER__', 1);
}

require_once 'whois.parser.php';

class ascio_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner'           => 'Registrant:',
                'admin'           => 'Administrative ',
                'tech'            => 'Technical ',
                'domain.name'     => 'Domain name:',
                'domain.nserver.' => 'Domain servers in listed order:',
                'domain.created'  => 'Record created:',
                'domain.expires'  => 'Record expires:',
                'domain.changed'  => 'Record last updated:',
                    );

        return easy_parser($data_str, $items, 'ymd', false, false, true);
    }
}
