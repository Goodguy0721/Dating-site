<?php

if (!defined('__TMAGNIC_HANDLER__')) {
    define('__TMAGNIC_HANDLER__', 1);
}

require_once 'whois.parser.php';

class tmagnic_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
              'owner'           => 'Owner Contact:',
              'admin'           => 'Admin Contact',
              'tech'            => 'Technical Contact',
              'domain.name'     => 'Domain Name:',
              'domain.nserver.' => 'Domain servers in listed order:',
              'domain.expires'  => 'Record expires on: ',
              'domain.changed'  => 'Record last updated on: ',
              ''                => 'Zone Contact',
              '#'               => 'Punycode Name:',
                  );

        return easy_parser($data_str, $items, 'ymd', false, false, true);
    }
}
