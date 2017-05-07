<?php

if (!defined('__US_HANDLER__')) {
    define('__US_HANDLER__', 1);
}

require_once 'whois.parser.php';

class us_handler
{
public function parse($data_str, $query)
{
    $items = array(
            'Domain Name:'                             => 'domain.name',
            'Domain ID:'                               => 'domain.handle',
            'Sponsoring Registrar:'                    => 'domain.sponsor',
            'Domain Status:'                           => 'domain.status',
            'Name Server:'                             => 'domain.nserver.',
            'Domain Registration Date:'                => 'domain.created',
            'Domain Expiration Date:'                  => 'domain.expires',
            'Domain Last Updated Date:'                => 'domain.changed',
            'Registrant ID:'                           => 'owner.handle',
            'Registrant Name:'                         => 'owner.name',
            'Registrant Organization:'                 => 'owner.organization',
            'Registrant Address1:'                     => 'owner.address.street',
            'Registrant Postal Code:'                  => 'owner.address.pcode',
            'Registrant City:'                         => 'owner.address.city',
            'Registrant State/Province:'               => 'owner.address.state',
            'Registrant Country:'                      => 'owner.address.country',
            'Registrant Phone Number:'                 => 'owner.phone',
            'Registrant Facsimile Number:'             => 'owner.fax',
            'Registrant Email:'                        => 'owner.email',
            'Administrative Contact ID:'               => 'admin.handle',
            'Administrative Contact Name:'             => 'admin.name',
            'Administrative Contact Organization:'     => 'admin.organization',
            'Administrative Contact Address1:'         => 'admin.address.street',
            'Administrative Contact Postal Code:'      => 'admin.address.pcode',
            'Administrative Contact City:'             => 'admin.address.city',
            'Administrative Contact State/Province:'   => 'admin.address.state',
            'Administrative Contact Country:'          => 'admin.address.country',
            'Administrative Contact Phone Number:'     => 'admin.phone',
            'Administrative Contact Email:'            => 'admin.email',
            'Administrative Contact Facsimile Number:' => 'admin.fax',
            'Technical Contact ID:'                    => 'tech.handle',
            'Technical Contact Name:'                  => 'tech.name',
            'Technical Contact Organization:'          => 'tech.organization',
            'Technical Contact Address1:'              => 'tech.address.street',
            'Technical Contact Postal Code:'           => 'tech.address.pcode',
            'Technical Contact City:'                  => 'tech.address.city',
            'Technical Contact State/Province:'        => 'tech.address.state',
            'Technical Contact Country:'               => 'tech.address.country',
            'Technical Contact Phone Number:'          => 'tech.phone',
            'Technical Contact Email:'                 => 'tech.email',
            'Technical Contact Facsimile Number:'      => 'tech.fax',
            'Billing Contact ID:'                      => 'billing.handle',
            'Billing Contact Name:'                    => 'billing.name',
            'Billing Contact Organization:'            => 'billing.organization',
            'Billing Contact Address1:'                => 'billing.address.street',
            'Billing Contact Postal Code:'             => 'billing.address.pcode',
            'Billing Contact City:'                    => 'billing.address.city',
            'Billing Contact State/Province:'          => 'billing.address.state',
            'Billing Contact Country:'                 => 'billing.address.country',
            'Billing Contact Phone Number:'            => 'billing.phone',
            'Billing Contact Email:'                   => 'billing.email',
            'Billing Contact Facsimile Number:'        => 'billing.fax',
            );

    $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, '-md--y');

    $r['regyinfo'] = array(
                        'referrer'          => 'http://www.neustar.us',
                                'registrar' => 'NEUSTAR INC.',
                        );

    return($r);
}
}
