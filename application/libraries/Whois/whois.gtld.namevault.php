<?php

if (!defined('__NAMEVAULT_HANDLER__')) {
    define('__NAMEVAULT_HANDLER__', 1);
}

require_once 'whois.parser.php';

class namevault_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner'           => 'Registrant',
                'admin'           => 'Administrative Contact:',
                'tech'            => 'Technical Contact:',
                'billing'         => 'Billing Contact:',
                'domain.name'     => 'Domain Name:',
                'domain.nserver.' => 'Name Servers',
                'domain.created'  => 'Creation Date:',
                'domain.expires'  => 'Expiration Date:',
                'domain.status'   => 'Status:',
                    );

        return easy_parser($data_str, $items, 'dmy', false, true, true);
    }
}
