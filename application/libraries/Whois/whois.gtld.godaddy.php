<?php

if (!defined('__GODADDY_HANDLER__')) {
    define('__GODADDY_HANDLER__', 1);
}

require_once 'whois.parser.php';

class godaddy_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'           => 'Registrant:',
                  'admin'           => 'Administrative Contact',
                  'tech'            => 'Technical Contact',
                  'domain.name'     => 'Domain Name:',
                  'domain.nserver.' => 'Domain servers in listed order:',
                  'domain.created'  => 'Created on:',
                  'domain.expires'  => 'Expires on:',
                  'domain.changed'  => 'Last Updated on:',
                  'domain.sponsor'  => 'Registered through:',
                      );

        $r = get_blocks($data_str, $items);
        $r['owner'] = get_contact($r['owner']);
        $r['admin'] = get_contact($r['admin'], false, true);
        $r['tech'] = get_contact($r['tech'], false, true);
        $r = format_dates($r, 'dmy');

        return ($r);
    }
}
