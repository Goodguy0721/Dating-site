<?php

/* 17/1/2002 Fixed !! */
/* 2003/02/18 updated to common object model */

if (!defined('__DOTSTER_HANDLER__')) {
    define('__DOTSTER_HANDLER__', 1);
}

require_once 'whois.parser.php';

class dotster_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'          => 'Registrant:',
                  'admin'          => 'Administrative',
                  'tech'           => 'Technical',
                  'domain.nserver' => 'Domain servers in listed order:',
                  'domain.name'    => 'Domain name:',
                  'domain.created' => 'Created on:',
                  'domain.expires' => 'Expires on:',
                  'domain.changed' => 'Last Updated on:',
                  'domain.sponsor' => 'Registrar:',
                      );

        return easy_parser($data_str, $items, 'dmy');
    }
}
