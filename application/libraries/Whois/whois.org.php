<?php

if (!defined('__ORG_HANDLER__')) {
    define('__ORG_HANDLER__', 1);
}

require_once 'whois.parser.php';

class org_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                    'Domain Name:'                     => 'domain.name',
                    'Status:'                          => 'domain.status.',
                    'Name Server:'                     => 'domain.nserver.',
                    'Created On:'                      => 'domain.created',
                    'Last Updated On:'                 => 'domain.changed',
                    'Expiration Date:'                 => 'domain.expires',
                    'Sponsoring Registrar:'            => 'domain.sponsor',
                    'Registrant Name:'                 => 'owner.name',
                    'Registrant ID:'                   => 'owner.handle',
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
                    'Admin Name:'                      => 'admin.name',
                    'Admin ID:'                        => 'admin.handle',
                    'Admin Street1:'                   => 'admin.address.address.0',
                    'Admin Street2:'                   => 'admin.address.address.1',
                    'Admin Street2:'                   => 'admin.address.address.2',
                    'Admin Postal Code:'               => 'admin.address.pcode',
                    'Admin City:'                      => 'admin.address.city',
                    'Admin Country:'                   => 'admin.address.country',
                    'Admin Phone:'                     => 'admin.phone',
                    'Admin FAX:'                       => 'admin.fax',
                    'Admin Email:'                     => 'admin.email',
                    'Tech Name:'                       => 'tech.name',
                    'Tech ID:'                         => 'tech.handle',
                    'Tech Street1:'                    => 'tech.address.address.0',
                    'Tech Street2:'                    => 'tech.address.address.1',
                    'Tech Street3:'                    => 'tech.address.address.2',
                    'Tech Postal Code:'                => 'tech.address.pcode',
                    'Tech City:'                       => 'tech.address.city',
                    'Tech Country:'                    => 'tech.address.country',
                    'Tech Phone:'                      => 'tech.phone',
                    'Tech FAX:'                        => 'tech.fax',
                    'Tech Email:'                      => 'tech.email',
                      );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

        if (!strncmp($data_str['rawdata'][0], 'WHOIS LIMIT EXCEEDED', 20)) {
            $r['regrinfo']['registered'] = 'unknown';
        }

        $r['regyinfo']['referrer'] = 'http://www.pir.org/';
        $r['regyinfo']['registrar'] = 'Public Interest Registry';

        return ($r);
    }
}
