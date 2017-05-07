<?php

if (!defined('__ASSORTED_HANDLER__')) {
    define('__ASSORTED_HANDLER__', 1);
}

require_once 'whois.parser.php';

class assorted_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner'           => 'Registrant:',
                'admin'           => 'Administrative Contact:',
                'tech'            => 'Technical Contact:',
                'domain.name'     => 'Domain Name:',
                'domain.nserver.' => 'Domain servers in listed order:',
                'domain.created'  => 'Record created on',
                'domain.expires'  => 'Record expires on',
                'domain.changed'  => 'Record last updated',
                    );

        return easy_parser($data_str, $items, 'ymd', false, false, true);
    }
}
