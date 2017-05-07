<?php

if (!defined('__IANA_HANDLER__')) {
    define('__IANA_HANDLER__', 1);
}

require_once 'whois.parser.php';

class iana_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'           => 'Registrant:',
                  'admin'           => 'Administrative Contact:',
                  'tech'            => 'Technical Contact:',
                  'domain.nserver.' => 'Nameserver:',
                  ''                => 'Nameserver Information:',
                      );

        $fields = array(
                    'Name:'                => 'name',
                    'Organization:'        => 'organization',
                    'Address1:'            => 'address.street.0',
                    'Address2:'            => 'address.street.1',
                    'Address3:'            => 'address.street.2',
                    'City:'                => 'address.city',
                    'State/Province:'      => 'address.state',
                    'Postal Code:'         => 'address.pcode',
                    'Country:'             => 'address.country',
                    'Phone:'               => 'phone',
                    'Fax:'                 => 'fax',
                    'Email:'               => 'email',
                    'Registration Date:'   => '',
                    'Last Updated Date:'   => '',
                        );

        $r = get_blocks($data_str, $items);

        if (isset($r['owner'])) {
            $r['owner'] = generic_parser_b($r['owner'], $fields, 'ymd', false);

            if (isset($r['admin'])) {
                $r['admin'] = generic_parser_b($r['admin'], $fields, 'ymd', false);
            }

            if (isset($r['tech'])) {
                $r['tech'] = generic_parser_b($r['tech'], $fields, 'ymd', false);
            }

            $r['registered'] = 'yes';
        } else {
            $r['registered'] = 'no';
        }

        return ($r);
    }
}
