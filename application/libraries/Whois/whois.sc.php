<?php

if (!defined('__SC_HANDLER__')) {
    define('__SC_HANDLER__', 1);
}

require_once 'whois.parser.php';

class sc_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'Domain Name:'               => 'domain.name',
                  'Domain ID:'                 => 'domain.handle',
                  'Sponsoring Registrar:'      => 'domain.sponsor',
                  'Status:'                    => 'domain.status',
                  'Name Server:'               => 'domain.nserver.',
                  'Created On:'                => 'domain.created',
                  'Expiration Date:'           => 'domain.expires',
                  'Last Updated On:'           => 'domain.changed',
                  'Registrant ID:'             => 'owner.handle',
                  'Registrant Name:'           => 'owner.name',
                  'Registrant Organization:'   => 'owner.organization',
                  'Registrant Street1:'        => 'owner.address.street.0',
                  'Registrant Street2:'        => 'owner.address.street.1',
                  'Registrant Postal Code:'    => 'owner.address.pcode',
                  'Registrant City:'           => 'owner.address.city',
                  'Registrant State/Province:' => 'owner.address.state',
                  'Registrant Country'         => 'owner.address.country',
                  'Registrant Phone:'          => 'owner.phone',
                  'Registrant FAX:'            => 'owner.fax',
                  'Registrant Email:'          => 'owner.email',
                  'Admin ID:'                  => 'admin.handle',
                  'Admin Name:'                => 'admin.name',
                  'Admin Organization:'        => 'admin.organization',
                  'Admin Street1:'             => 'admin.address.street.0',
                  'Admin Street2:'             => 'admin.address.street.1',
                  'Admin Postal Code:'         => 'admin.address.pcode',
                  'Admin City:'                => 'admin.address.city',
                  'Admin State/Province:'      => 'admin.address.state',
                  'Admin Country:'             => 'admin.address.country',
                  'Admin Phone:'               => 'admin.phone',
                  'Admin Email:'               => 'admin.email',
                  'Admin FAX:'                 => 'admin.fax',
                  'Tech ID:'                   => 'tech.handle',
                  'Tech Name:'                 => 'tech.name',
                  'Tech Organization:'         => 'tech.organization',
                  'Tech Street1:'              => 'tech.address.street.0',
                  'Tech Street2:'              => 'tech.address.street.1',
                  'Tech Postal Code:'          => 'tech.address.pcode',
                  'Tech City:'                 => 'tech.address.city',
                  'Tech State/Province:'       => 'tech.address.state',
                  'Tech Country:'              => 'tech.address.country',
                  'Tech Phone:'                => 'tech.phone',
                  'Tech FAX:'                  => 'tech.fax',
                  'Tech Email:'                => 'tech.email',
                  'Billing ID:'                => 'billing.handle',
                  'Billing Name:'              => 'billing.name',
                  'Billing Organization:'      => 'billing.organization',
                  'Billing Street1:'           => 'billing.address.street.1',
                  'Billing Street2:'           => 'billing.address.street.0',
                  'Billing Postal Code:'       => 'billing.address.pcode',
                  'Billing City:'              => 'billing.address.city',
                  'Billing State/Province:'    => 'billing.address.state',
                  'Billing Country:'           => 'billing.address.country',
                  'Billing Phone:'             => 'billing.phone',
                  'Billing FAX:'               => 'billing.fax',
                  'Billing Email:'             => 'billing.email',
                    );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, 'dmy');

        $r['regyinfo'] = array(
                  'referrer'  => 'http://www.nic.sc',
                  'registrar' => 'VCS (Pty) Limited',
                  );

        return ($r);
    }
}
