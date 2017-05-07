<?php

if (!defined('__MUSEUM_HANDLER__')) {
    define('__MUSEUM_HANDLER__', 1);
}

require_once 'whois.parser.php';

class museum_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'Domain ID:'                 => 'domain.handle',
                  'Domain Name:'               => 'domain.name',
                  'Created On:'                => 'domain.created',
                  'Expiration Date:'           => 'domain.expires',
                  'Registrar ID:'              => 'domain.sponsor',
                  'Name Server:'               => 'domain.nserver.',
                  'Registrant ID:'             => 'owner.handle',
                  'Registrant Name:'           => 'owner.name',
                  'Registrant Organization:'   => 'owner.organization',
                  'Registrant Street:'         => 'owner.address.street',
                  'Registrant City:'           => 'owner.address.city',
                  'Registrant Postal Code:'    => 'owner.address.pcode',
                  'Registrant Country:'        => 'owner.address.country',
                  'Registrant Phone:'          => 'owner.phone',
                  'Registrant Fax:'            => 'owner.fax',
                  'Registrant Email:'          => 'owner.email',
                  'Admin ID:'                  => 'admin.handle',
                  'Admin Name:'                => 'admin.name',
                  'Admin Organization:'        => 'admin.organization',
                  'Admin Street:'              => 'admin.address.street',
                  'Admin City:'                => 'admin.address.city',
                  'Admin Postal Code:'         => 'admin.address.pcode',
                  'Admin Country:'             => 'admin.address.country',
                  'Admin Phone:'               => 'admin.phone',
                  'Admin Fax:'                 => 'admin.fax',
                  'Admin Email:'               => 'admin.email',
                  'Tech ID:'                   => 'tech.handle',
                  'Tech Name:'                 => 'tech.name',
                  'Tech Organization:'         => 'tech.organization',
                  'Tech Street:'               => 'tech.address.street.',
                  'Tech City:'                 => 'tech.address.city',
                  'Tech Postal Code:'          => 'tech.address.pcode',
                  'Tech Country:'              => 'tech.address.country',
                  'Tech Phone:'                => 'tech.phone',
                  'Tech Fax:'                  => 'tech.fax',
                  'Tech Email:'                => 'tech.email',
                  'Billing ID:'                => 'billing.handle',
                  'Billing Name:'              => 'billing.name',
                  'Billing Organization:'      => 'billing.organization',
                  'Billing Street:'            => 'billing.address.street',
                  'Billing City:'              => 'billing.address.city',
                  'Billing Postal Code:'       => 'billing.address.pcode',
                  'Billing Country:'           => 'billing.address.country',
                  'Billing Phone:'             => 'billing.phone',
                  'Billing Fax:'               => 'billing.fax',
                  'Billing Email:'             => 'billing.email',
                    );

        $r['regyinfo'] = array(
                          'referrer'  => 'http://musedoma.museum',
                          'registrar' => 'Museum Domain Management Association',
                          );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

        return $r;
    }
}
