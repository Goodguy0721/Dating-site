<?php

if (!defined('__ASIA_HANDLER__')) {
    define('__ASIA_HANDLER__', 1);
}

require_once 'whois.parser.php';

class asia_handler
{
public function parse($data_str, $query)
{
    $items = array(
            'Domain ID:'                            => 'domain.handle',
                  'Domain Name:'                    => 'domain.name',
                  'Domain Create Date:'             => 'domain.created',
                  'Domain Last Updated Date:'       => 'domain.changed',
                  'Domain Expiration Date:'         => 'domain.expires',
                  'Sponsoring Registrar:'           => 'domain.sponsor',
                  'Nameservers:'                    => 'domain.nserver.',
                  'Registrant ID:'                  => 'owner.handle',
                  'Registrant Name:'                => 'owner.name',
                  'Registrant Organization:'        => 'owner.organization',
                  'Registrant Address:'             => 'owner.address.street.0',
                  'Registrant Address2:'            => 'owner.address.street.1',
                  'Registrant Address3:'            => 'owner.address.street.2',
                  'Registrant City:'                => 'owner.address.city',
                  'Registrant State/Province:'      => 'owner.address.state',
                  'Registrant Postal Code:'         => 'owner.address.pcode',
                  'Registrant Country/Economy:'     => 'owner.address.country',
                  'Registrant Phone:'               => 'owner.phone',
                  'Registrant FAX:'                 => 'owner.fax',
                  'Registrant E-mail:'              => 'owner.email',
                  'Administrative ID:'              => 'admin.handle',
                  'Administrative Name:'            => 'admin.name',
                  'Administrative Organization:'    => 'admin.organization',
                  'Administrative Address:'         => 'admin.address.street.0',
                  'Administrative Address2:'        => 'admin.address.street.1',
                  'Administrative Address3:'        => 'admin.address.street.2',
                  'Administrative City:'            => 'admin.address.city',
                  'Administrative State/Province:'  => 'admin.address.state',
                  'Administrative Postal Code:'     => 'admin.address.pcode',
                  'Administrative Country/Economy:' => 'admin.address.country',
                  'Administrative Phone:'           => 'admin.phone',
                  'Administrative FAX:'             => 'admin.fax',
                  'Administrative E-mail:'          => 'admin.email',
                  'Technical ID:'                   => 'tech.handle',
                  'Technical Name:'                 => 'tech.name',
                  'Technical Organization:'         => 'tech.organization',
                  'Technical Address:'              => 'tech.address.street.0',
                  'Technical Address2:'             => 'tech.address.street.1',
                  'Technical Address3:'             => 'tech.address.street.2',
                  'Technical City:'                 => 'tech.address.city',
                  'Technical State/Province:'       => 'tech.address.state',
                  'Technical Postal Code:'          => 'tech.address.pcode',
                  'Technical Country/Economy:'      => 'tech.address.country',
                  'Technical Phone:'                => 'tech.phone',
                  'Technical FAX:'                  => 'tech.fax',
                  'Technical E-mail:'               => 'tech.email',
                  'Billing ID:'                     => 'billing.handle',
                  'Billing Name:'                   => 'billing.name',
                  'Billing Organization:'           => 'billing.organization',
                  'Billing Address:'                => 'billing.address.street.0',
                  'Billing Address2:'               => 'billing.address.street.1',
                  'Billing Address3:'               => 'billing.address.street.2',
                  'Billing City:'                   => 'billing.address.city',
                  'Billing State/Province:'         => 'billing.address.state',
                  'Billing Postal Code:'            => 'billing.address.pcode',
                  'Billing Country/Economy:'        => 'billing.address.country',
                  'Billing Phone:'                  => 'billing.phone',
                  'Billing FAX:'                    => 'billing.fax',
                  'Billing E-mail:'                 => 'billing.email',
                );

    $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

    $r['regyinfo'] = array(
                        'referrer'  => 'http://www.dotasia.org/',
                        'registrar' => 'DotAsia',
                        );

    return($r);
}
}
