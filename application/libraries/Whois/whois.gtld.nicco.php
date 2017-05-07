<?php

if (!defined('__NICCO_HANDLER__')) {
    define('__NICCO_HANDLER__', 1);
}

require_once 'whois.parser.php';

class nicco_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'           => 'Holder Contact',
                  'admin'           => 'Admin Contact',
                  'tech'            => 'Tech. Contact',
                  'domain.nserver.' => 'Nameservers',
                  'domain.created'  => 'Creation Date:',
                  'domain.expires'  => 'Expiration Date:',
                      );

        $translate = array(
                    'city:'            => 'address.city',
                    'org. name:'       => 'organization',
                    'address1:'        => 'address.street.',
                    'address2:'        => 'address.street.',
                    'state:'           => 'address.state',
                    'postal code:'     => 'address.zip',
                    );

        $r = get_blocks($data_str, $items, true);
        $r['owner'] = get_contact($r['owner'], $translate);
        $r['admin'] = get_contact($r['admin'], $translate, true);
        $r['tech'] = get_contact($r['tech'], $translate, true);
        $r = format_dates($r, 'dmy');

        return ($r);
    }
}
