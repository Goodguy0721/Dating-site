<?php

if (!defined('__NAMEKING_HANDLER__')) {
    define('__NAMEKING_HANDLER__', 1);
}

require_once 'whois.parser.php';

class nameking_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'          => 'Registrant',
                  'admin'          => 'Admin Contact',
                  'tech'           => 'Tech Contact',
                  'billing'        => 'Billing Contact',
                  'domain.sponsor' => 'Registration Provided By:',
                  'domain.created' => 'Creation Date:',
                  'domain.expires' => 'Expiration Date:',
                      );

        $extra = array(
                    'tel--'                      => 'phone',
                    'tel:'                       => 'phone',
                    'tel --:'                    => 'phone',
                    'email-:'                    => 'email',
                    'email:'                     => 'email',
                    'mail:'                      => 'email',
                    'name--'                     => 'name',
                    'org:'                       => 'organization',
                    'zipcode:'                   => 'address.pcode',
                    'postcode:'                  => 'address.pcode',
                    'address:'                   => 'address.street',
                    'city:'                      => 'address.city',
                    'province:'                  => 'address.city.',
                    ',province:'                 => '',
                    ',country:'                  => 'address.country',
                    'organization:'              => 'organization',
                    'city, province, post code:' => 'address.city',
                    );

        return easy_parser($data_str, $items, 'mdy', $extra, false, true);
    }
}
