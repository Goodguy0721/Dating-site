<?php

if (!defined('__CAT_HANDLER__')) {
    define('__CAT_HANDLER__', 1);
}

require_once 'whois.parser.php';

class cat_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                    'Domain Name:'                     => 'domain.name',
                    'Expiration Date:'                 => 'domain.expires',
                    'Created On:'                      => 'domain.created',
                    'Last Updated On:'                 => 'domain.changed',
                    'Registrant Name:'                 => 'owner.name',
                    'Registrant ID:'                   => 'owner.handle',
                    'Registrant Organization:'         => 'owner.organization',
                    'Registrant Street1:'              => 'owner.address.address.0',
                    'Registrant Street2:'              => 'owner.address.address.1',
                    'Registrant Street3:'              => 'owner.address.address.2',
                    'Registrant Postal Code:'          => 'owner.address.pcode',
                    'Registrant City:'                 => 'owner.address.city',
                    'Registrant State/Province:'       => 'owner.address.state',
                    'Registrant Country:'              => 'owner.address.country',
                    'Registrant Phone:'                => 'owner.phone',
                    'Registrant FAX:'                  => 'owner.fax',
                    'Registrant Email:'                => 'owner.email',
                    'Admin ID:'                        => 'admin.handle',
                    'Admin Name:'                      => 'admin.name',
                    'Admin Organization:'              => 'admin.organization',
                    'Admin Street1:'                   => 'admin.address.address.0',
                    'Admin Street2:'                   => 'admin.address.address.1',
                    'Admin Street2:'                   => 'admin.address.address.2',
                    'Admin Postal Code:'               => 'admin.address.pcode',
                    'Admin City:'                      => 'admin.address.city',
                    'Admin Country:'                   => 'admin.address.country',
                    'Admin Phone:'                     => 'admin.phone',
                    'Admin FAX:'                       => 'admin.fax',
                    'Admin Email:'                     => 'admin.email',
                    'Tech ID:'                         => 'tech.handle',
                    'Tech Name:'                       => 'tech.name',
                    'Tech Organization:'               => 'tech.organization',
                    'Tech Street1:'                    => 'tech.address.address.0',
                    'Tech Street2:'                    => 'tech.address.address.1',
                    'Tech Street3:'                    => 'tech.address.address.2',
                    'Tech Postal Code:'                => 'tech.address.pcode',
                    'Tech City:'                       => 'tech.address.city',
                    'Tech Country:'                    => 'tech.address.country',
                    'Tech Phone:'                      => 'tech.phone',
                    'Tech FAX:'                        => 'tech.fax',
                    'Tech Email:'                      => 'tech.email',
                    'Billing ID:'                      => 'billing.handle',
                    'Billing Name:'                    => 'billing.name',
                    'Billing Organization:'            => 'billing.organization',
                    'Billing Street1:'                 => 'billing.address.address.0',
                    'Billing Street2:'                 => 'billing.address.address.1',
                    'Billing Street3:'                 => 'billing.address.address.2',
                    'Billing Postal Code:'             => 'billing.address.pcode',
                    'Billing City:'                    => 'billing.address.city',
                    'Billing Country:'                 => 'billing.address.country',
                    'Billing Phone:'                   => 'billing.phone',
                    'Billing FAX:'                     => 'billing.fax',
                    'Billing Email:'                   => 'billing.email',
                      );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

        if (!isset($r['regrinfo']['domain']['name'])) {
            $r['regrinfo']['registered'] = 'no';
        }

        $r['regyinfo']['referrer'] = 'http://www.domini.cat/';
        $r['regyinfo']['registrar'] = 'Domini punt CAT';

        return ($r);
    }
}
