<?php

/* NICLINE.whois 1.1	David Saez */
/* Example 'niclide.com' */

if (!defined('__NICLINE_HANDLER__')) {
    define('__NICLINE_HANDLER__', 1);
}

require_once 'whois.parser.php';

class nicline_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner'           => 'Registrant:',
                'admin'           => 'Administrative contact:',
                'tech'            => 'Technical contact:',
                'domain.name'     => 'Domain name:',
                'domain.nserver.' => 'Domain servers in listed order:',
                'domain.created'  => 'Created:',
                'domain.expires'  => 'Expires:',
                'domain.changed'  => 'Last updated:',
                      );

        return easy_parser($data_str, $items, 'dmy');
    }
}
