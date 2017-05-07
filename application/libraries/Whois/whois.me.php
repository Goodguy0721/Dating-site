<?php

/* info.whois	1.0  David Saez Padros <david@ols.es> */

if (!defined('__ME_HANDLER__')) {
    define('__ME_HANDLER__', 1);
}

require_once 'whois.parser.php';

class me_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'Domain ID:'                  => 'domain.handle',
                  'Domain Name:'                => 'domain.name',
                  'Domain Create Date:'         => 'domain.created',
                  'Domain Last Updated Date:'   => 'domain.changed',
                  'Domain Expiration Date:'     => 'domain.expires',
                  'Sponsoring Registrar:'       => 'domain.sponsor',
                  'Domain Status:'              => 'domain.status',
                  'Nameservers:'                => 'domain.nserver.',
                  'Registrant ID:'              => 'owner.handle',
                  'Registrant Name:'            => 'owner.name',
                  'Registrant Organization:'    => 'owner.organization',
                  'Registrant Address:'         => 'owner.address.street.0',
                  'Registrant Address2:'        => 'owner.address.street.1',
                  'Registrant Address3:'        => 'owner.address.street.2',
                  'Registrant City:'            => 'owner.address.city',
                  'Registrant State/Province:'  => 'owner.address.state',
                  'Registrant Postal Code:'     => 'owner.address.pcode',
                  'Registrant Country/Economy:' => 'owner.address.country',
                  'Registrant Phone:'           => 'owner.phone',
                  'Registrant FAX:'             => 'owner.fax',
                  'Registrant E-mail:'          => 'owner.email',
                  'Admin ID:'                   => 'admin.handle',
                  'Admin Name:'                 => 'admin.name',
                  'Admin Organization:'         => 'admin.organization',
                  'Admin Address:'              => 'admin.address.street.0',
                  'Admin Address2:'             => 'admin.address.street.1',
                  'Admin Address3:'             => 'admin.address.street.2',
                  'Admin City:'                 => 'admin.address.city',
                  'Admin State/Province:'       => 'admin.address.state',
                  'Admin Postal Code:'          => 'admin.address.pcode',
                  'Admin Country/Economy:'      => 'admin.address.country',
                  'Admin Phone:'                => 'admin.phone',
                  'Admin FAX:'                  => 'admin.fax',
                  'Admin E-mail:'               => 'admin.email',
                  'Tech ID:'                    => 'tech.handle',
                  'Tech Name:'                  => 'tech.name',
                  'Tech Organization:'          => 'tech.organization',
                  'Tech Address:'               => 'tech.address.street.0',
                  'Tech Address2:'              => 'tech.address.street.1',
                  'Tech Address3:'              => 'tech.address.street.2',
                  'Tech City:'                  => 'tech.address.city',
                  'Tech State/Province:'        => 'tech.address.state',
                  'Tech Postal Code:'           => 'tech.address.pcode',
                  'Tech Country/Economy:'       => 'tech.address.country',
                  'Tech Phone:'                 => 'tech.phone',
                  'Tech FAX:'                   => 'tech.fax',
                  'Tech E-mail:'                => 'tech.email',
                  'Billing ID:'                 => 'billing.handle',
                  'Billing Name:'               => 'billing.name',
                  'Billing Organization:'       => 'billing.organization',
                  'Billing Address:'            => 'billing.address.street.0',
                  'Billing Address2:'           => 'billing.address.street.1',
                  'Billing Address3:'           => 'billing.address.street.2',
                  'Billing City:'               => 'billing.address.city',
                  'Billing State/Province:'     => 'billing.address.state',
                  'Billing Postal Code:'        => 'billing.address.pcode',
                  'Billing Country/Economy:'    => 'billing.address.country',
                  'Billing Phone:'              => 'billing.phone',
                  'Billing FAX:'                => 'billing.fax',
                  'Billing E-mail:'             => 'billing.email',
                    );

        $r['regyinfo'] = array(
                          'referrer'  => 'http://domain.me',
                          'registrar' => 'doMEn',
                          );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

        return $r;
    }
}
