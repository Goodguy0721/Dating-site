<?php

if (!defined('__COOP_HANDLER__')) {
    define('__COOP_HANDLER__', 1);
}

require_once 'whois.parser.php';

class coop_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                        'owner'           => 'Contact Type:            registrant',
                        'admin'           => 'Contact Type:            admin',
                        'tech'            => 'Contact Type:            tech',
                        'billing'         => 'Contact Type:            billing',
                        'domain.name'     => 'Domain Name:',
                        'domain.handle'   => 'Domain ID:',
                        'domain.expires'  => 'Expiry Date:',
                        'domain.created'  => 'Created:',
                        'domain.changed'  => 'Last updated:',
                        'domain.status'   => 'Domain Status:',
                        'domain.sponsor'  => 'Sponsoring registrar:',
                        'domain.nserver.' => 'Host Name:',
                        );

        $translate = array(
                        'Contact ID:'     => 'handle',
                        'Name:'           => 'name',
                        'Organisation:'   => 'organization',
                        'Street 1:'       => 'address.street.0',
                        'Street 2:'       => 'address.street.1',
                        'Street 3:'       => 'address.street.2',
                        'City:'           => 'address.city',
                        'State/Province:' => 'address.state',
                        'Postal code:'    => 'address.pcode',
                        'Country:'        => 'address.country',
                        'Voice:'          => 'phone',
                        'Fax:'            => 'fax',
                        'Email:'          => 'email',
                        );

        $blocks = get_blocks($data_str['rawdata'], $items);

        $r = array();

        if (isset($blocks['domain'])) {
            $r['regrinfo']['domain'] = format_dates($blocks['domain'], 'dmy');
            $r['regrinfo']['registered'] = 'yes';

            if (isset($blocks['owner'])) {
                $r['regrinfo']['owner'] = generic_parser_b($blocks['owner'], $translate, 'dmy', false);

                if (isset($blocks['tech'])) {
                    $r['regrinfo']['tech'] = generic_parser_b($blocks['tech'], $translate, 'dmy', false);
                }

                if (isset($blocks['admin'])) {
                    $r['regrinfo']['admin'] = generic_parser_b($blocks['admin'], $translate, 'dmy', false);
                }

                if (isset($blocks['billing'])) {
                    $r['regrinfo']['billing'] = generic_parser_b($blocks['billing'], $translate, 'dmy', false);
                }
            } else {
                $r['regrinfo']['owner'] = generic_parser_b($data_str['rawdata'], $translate, 'dmy', false);
            }
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        return $r;
    }
}
