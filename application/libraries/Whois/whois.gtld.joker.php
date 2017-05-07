<?php

if (!defined('__JOKER_HANDLER__')) {
    define('__JOKER_HANDLER__', 1);
}

require_once 'whois.parser.php';

class joker_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
                'contact-hdl'     => 'handle',
                'modified'        => 'changed',
                'reseller'        => 'sponsor',
                'address'         => 'address.street',
                'postal-code'     => 'address.pcode',
                'city'            => 'address.city',
                'state'           => 'address.state',
                'country'         => 'address.country',
                'person'          => 'name',
                'domain'          => 'name',
                );

        $contacts = array(
                'admin-c'    => 'admin',
                'tech-c'     => 'tech',
                'billing-c'  => 'billing',
                );

        $items = array(
                'owner'            => 'name',
                'organization'     => 'organization',
                'email'            => 'email',
                'phone'            => 'phone',
                'address'          => 'address',
                    );

        $r = generic_parser_a($data_str, $translate, $contacts, 'domain', 'Ymd');

        foreach ($items as $tag => $convert) {
            if (isset($r['domain'][$tag])) {
                $r['owner'][$convert] = $r['domain'][$tag];
                unset($r['domain'][$tag]);
            }
        }

        return ($r);
    }
}
