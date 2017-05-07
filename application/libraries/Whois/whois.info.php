<?php

/* info.whois	1.0  David Saez Padros <david@ols.es> */

if (!defined('__INFO_HANDLER__')) {
    define('__INFO_HANDLER__', 1);
}

require_once 'whois.parser.php';

class info_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'Domain ID:'                 => 'domain.handle',
                  'Domain Name:'               => 'domain.name',
                  'Created On:'                => 'domain.created',
                  'Last Updated On:'           => 'domain.changed',
                  'Expiration Date:'           => 'domain.expires',
                  'Sponsoring Registrar:'      => 'domain.sponsor',
                  'Status:'                    => 'domain.status',
                  'Name Server:'               => 'domain.nserver.',
                  'Registrant ID:'             => 'owner.handle',
                  'Registrant Name:'           => 'owner.name',
                  'Registrant Organization:'   => 'owner.organization',
                  'Registrant Street1:'        => 'owner.address.street.0',
                  'Registrant Street2:'        => 'owner.address.street.1',
                  'Registrant City:'           => 'owner.address.city',
                  'Registrant State/Province:' => 'owner.address.state',
                  'Registrant Postal Code:'    => 'owner.address.pcode',
                  'Registrant Country:'        => 'owner.address.country',
                  'Registrant Phone:'          => 'owner.phone',
                  'Registrant FAX:'            => 'owner.fax',
                  'Registrant Email:'          => 'owner.email',
                  'Admin ID:'                  => 'admin.handle',
                  'Admin Name:'                => 'admin.name',
                  'Admin Organization:'        => 'admin.organization',
                  'Admin Street1:'             => 'admin.address.street.0',
                  'Admin Street2:'             => 'admin.address.street.1',
                  'Admin City:'                => 'admin.address.city',
                  'Admin State/Province:'      => 'admin.address.state',
                  'Admin Postal Code:'         => 'admin.address.pcode',
                  'Admin Country:'             => 'admin.address.country',
                  'Admin Phone:'               => 'admin.phone',
                  'Admin FAX:'                 => 'admin.fax',
                  'Admin Email:'               => 'admin.email',
                  'Tech ID:'                   => 'tech.handle',
                  'Tech Name:'                 => 'tech.name',
                  'Tech Organization:'         => 'tech.organization',
                  'Tech Street1:'              => 'tech.address.street.0',
                  'Tech Street2:'              => 'tech.address.street.1',
                  'Tech City:'                 => 'tech.address.city',
                  'Tech State/Province:'       => 'tech.address.state',
                  'Tech Postal Code:'          => 'tech.address.pcode',
                  'Tech Country:'              => 'tech.address.country',
                  'Tech Phone:'                => 'tech.phone',
                  'Tech FAX:'                  => 'tech.fax',
                  'Tech Email:'                => 'tech.email',
                  'Billing ID:'                => 'billing.handle',
                  'Billing Name:'              => 'billing.name',
                  'Billing Organization:'      => 'billing.organization',
                  'Billing Street1:'           => 'billing.address.street.0',
                  'Billing Street2:'           => 'billing.address.street.1',
                  'Billing City:'              => 'billing.address.city',
                  'Billing State/Province:'    => 'billing.address.state',
                  'Billing Postal Code:'       => 'billing.address.pcode',
                  'Billing Country:'           => 'billing.address.country',
                  'Billing Phone:'             => 'billing.phone',
                  'Billing FAX:'               => 'billing.fax',
                  'Billing Email:'             => 'billing.email',
                    );

        $r['regyinfo'] = array(
                          'referrer'  => 'http://whois.afilias.info',
                          'registrar' => 'Afilias Global Registry Services',
                          );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

        return $r;
    }
}
