<?php

if (!defined('__NOMINALIA_HANDLER__')) {
    define('__NOMINALIA_HANDLER__', 1);
}

require_once 'whois.parser.php';

class nominalia_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'Domain name:'                                 => 'domain.name',
                'Primary Name Server Hostname:'                => 'domain.nserver.0',
                'Secondary Name Server Hostname:'              => 'domain.nserver.1',
                'Created on:'                                  => 'domain.created',
                'Expires on:'                                  => 'domain.expires',
                'Updated on:'                                  => 'domain.changed',
                'Registrant Name:'                             => 'owner.name',
                'Registrant Address:'                          => 'owner.address.street',
                'Registrant City:'                             => 'owner.address.city',
                'Registrant Postal Code:'                      => 'owner.address.pcode',
                'Registrant Country:'                          => 'owner.address.country',
                'Administrative Contact Name:'                 => 'admin.name',
                'Administrative Contact Organization:'         => 'admin.organization',
                'Administrative Contact Address:'              => 'admin.address.street',
                'Administrative Contact City:'                 => 'admin.address.city',
                'Administrative Contact Postal Code:'          => 'admin.address.pcode',
                'Administrative Contact Country:'              => 'admin.address.country',
                'Administrative Contact Email:'                => 'admin.email',
                'Administrative Contact Tel:'                  => 'admin.phone',
                'Administrative Contact Fax:'                  => 'admin.fax',
                'Technical Contact Contact Name:'              => 'tech.name',
                'Technical Contact Contact Organization:'      => 'tech.organization',
                'Technical Contact Contact Address:'           => 'tech.address.street',
                'Technical Contact Contact City:'              => 'tech.address.city',
                'Technical Contact Contact Postal Code:'       => 'tech.address.pcode',
                'Technical Contact Contact Country:'           => 'tech.address.country',
                'Technical Contact Contact Email:'             => 'tech.email',
                'Technical Contact Contact Tel:'               => 'tech.phone',
                'Technical Contact Contact Fax:'               => 'tech.fax',
                    );

        return generic_parser_b($data_str, $items, 'ymd');
    }
}
