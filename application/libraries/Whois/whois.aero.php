<?php

if (!defined('__AERO_HANDLER__')) {
    define('__AERO_HANDLER__', 1);
}

require_once 'whois.parser.php';

class aero_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'Domain ID:'                 => 'domain.handle',
                  'Domain Name:'               => 'domain.name',
                  'Creation Date:'             => 'domain.created',
                  'Last Modification Date:'    => 'domain.changed',
                  'Expiration Date:'           => 'domain.expires',
                  'Sponsoring Registrar:'      => 'domain.sponsor',
                  'Name Server:'               => 'domain.nserver.',
                  'Registrant ID:'             => 'owner.handle',
                  'Registrant Name:'           => 'owner.name',
                  'Registrant Organization:'   => 'owner.organization',
                  'Registrant Address:'        => 'owner.address.street',
                  'Registrant City:'           => 'owner.address.city',
                  'Registrant State/Province:' => 'owner.address.state',
                  'Registrant Postal Code:'    => 'owner.address.pcode',
                  'Registrant Country:'        => 'owner.address.country',
                  'Registrant Phone Number:'   => 'owner.phone',
                  'Registrant Fax Number:'     => 'owner.fax',
                  'Registrant Email:'          => 'owner.email',
                  'Admin ID:'                  => 'admin.handle',
                  'Admin Name:'                => 'admin.name',
                  'Admin Organization:'        => 'admin.organization',
                  'Admin Address:'             => 'admin.address.street',
                  'Admin City:'                => 'admin.address.city',
                  'Admin State/Province:'      => 'admin.address.state',
                  'Admin Postal Code:'         => 'admin.address.pcode',
                  'Admin Country:'             => 'admin.address.country',
                  'Admin Phone Number:'        => 'admin.phone',
                  'Admin Fax Number:'          => 'admin.fax',
                  'Admin Email:'               => 'admin.email',
                  'Tech ID:'                   => 'tech.handle',
                  'Tech Name:'                 => 'tech.name',
                  'Tech Organization:'         => 'tech.organization',
                  'Tech Address:'              => 'tech.address.street.',
                  'Tech City:'                 => 'tech.address.city',
                  'Tech Postal Code:'          => 'tech.address.pcode',
                  'Tech State/Province:'       => 'tech.address.state',
                  'Tech Country:'              => 'tech.address.country',
                  'Tech Phone Number:'         => 'tech.phone',
                  'Tech Fax Number:'           => 'tech.fax',
                  'Tech Email:'                => 'tech.email',
                  'Billing ID:'                => 'billing.handle',
                  'Billing Name:'              => 'billing.name',
                  'Billing Organization:'      => 'billing.organization',
                  'Billing Address:'           => 'billing.address.street',
                  'Billing City:'              => 'billing.address.city',
                  'Billing Postal Code:'       => 'billing.address.pcode',
                  'Billing State/Province:'    => 'billing.address.state',
                  'Billing Country:'           => 'billing.address.country',
                  'Billing Phone Number:'      => 'billing.phone',
                  'Billing Fax Number:'        => 'billing.fax',
                  'Billing Email:'             => 'billing.email',
                    );

        $r['regyinfo'] = array(
                          'referrer'  => 'http://www.nic.aero',
                          'registrar' => 'Societe Internationale de Telecommunications Aeronautiques SC',
                          );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, 'ymd');

        return $r;
    }
}
