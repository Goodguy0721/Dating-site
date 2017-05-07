<?php

if (!defined('__NAME_HANDLER__')) {
    define('__NAME_HANDLER__', 1);
}

require_once 'whois.parser.php';

class name_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'Domain Name:'               => 'domain.name',
                  'Domain Status:'             => 'domain.status',
                  'Sponsoring Registrar:'      => 'domain.sponsor',
                  'Created On:'                => 'domain.created',
                  'Expires On:'                => 'domain.expires',
                  'Updated On:'                => 'domain.changed',
                  'Name Server:'               => 'domain.nserver.',
                  'Registrant ID:'             => 'owner.handle',
                  'Registrant Name:'           => 'owner.name',
                  'Registrant Organization:'   => 'owner.organization',
                  'Registrant Address:'        => 'owner.address.street',
                  'Registrant City:'           => 'owner.address.city',
                  'Registrant Postal Code:'    => 'owner.address.pcode',
                  'Registrant State/Province:' => 'owner.address.state',
                  'Registrant Country:'        => 'owner.address.country',
                  'Admin ID:'                  => 'admin.handle',
                  'Admin Name:'                => 'admin.name',
                  'Admin Organization:'        => 'admin.organization',
                  'Admin Address:'             => 'admin.address.street',
                  'Admin City:'                => 'admin.address.city',
                  'Admin Postal Code:'         => 'admin.address.pcode',
                  'Admin State/Province:'      => 'admin.address.state',
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

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

        $r['regyinfo'] = array(
                          'referrer'  => 'http://www.nic.name/',
                          'registrar' => 'Global Name Registry',
                          );

        return $r;
    }
}
