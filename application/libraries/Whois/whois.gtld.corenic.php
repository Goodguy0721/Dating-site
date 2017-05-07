<?php

/* Adapted from netsol.whois by Denny Reiter 2000/12/12	*/
/* core.whois   2.0     david@ols.es 2003/02/26 */

require_once 'whois.parser.php';

if (!defined('__CORENIC_HANDLER__')) {
    define('__CORENIC_HANDLER__', 1);
}

class corenic_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'Domain ID:'                 => 'domain.handle',
                'Domain Name:'               => 'domain.name',
                'Creation Date:'             => 'domain.created',
                'Expiration Date:'           => 'domain.expires',
                'Last Modification Date:'    => 'domain.changed',
                'Sponsoring Registrar:'      => 'domain.sponsor',
                'Name Server:'               => 'domain.nserver.',
                'Registrant Organization:'   => 'owner.organization',
                'Registrant Name:'           => 'owner.name',
                'Registrant Address:'        => 'owner.address.street.0',
                'Registrant Address 2:'      => 'owner.address.street.1',
                'Registrant City:'           => 'owner.address.city',
                'Registrant State/Province:' => 'owner.address.state',
                'Registrant Postal Code:'    => 'owner.address.pcode',
                'Registrant Country:'        => 'owner.address.country',
                'Registrant Phone Number:'   => 'owner.phone',
                'Registrant Fax Number:'     => 'owner.fax',
                'Registrant Email:'          => 'owner.email',
                'Admin ID:'                  => 'admin.handle',
                'Admin Organization:'        => 'admin.organization',
                'Admin Name:'                => 'admin.name',
                'Admin Address:'             => 'admin.address.street.0',
                'Admin Address 2:'           => 'admin.address.street.1',
                'Admin City:'                => 'admin.address.city',
                'Admin State/Province:'      => 'admin.address.state',
                'Admin Postal Code:'         => 'admin.address.pcode',
                'Admin Country:'             => 'admin.address.country',
                'Admin Phone Number:'        => 'admin.phone',
                'Admin Fax Number:'          => 'admin.fax',
                'Admin Email:'               => 'admin.email',
                'Tech ID:'                   => 'tech.handle',
                'Tech Organization:'         => 'tech.organization',
                'Tech Name:'                 => 'tech.name',
                'Tech Address:'              => 'tech.address.street.0',
                'Tech Address 2:'            => 'tech.address.street.1',
                'Tech City:'                 => 'tech.address.city',
                'Tech State/Province:'       => 'tech.address.state',
                'Tech Postal Code:'          => 'tech.address.pcode',
                'Tech Country:'              => 'tech.address.country',
                'Tech Phone Number:'         => 'tech.phone',
                'Tech Fax Number:'           => 'tech.fax',
                'Tech Email:'                => 'tech.email',
                'Zone ID:'                   => 'zone.handle',
                'Zone Organization:'         => 'zone.organization',
                'Zone Name:'                 => 'zone.name',
                'Zone Address:'              => 'zone.address.street.0',
                'Zone Address 2:'            => 'zone.address.street.1',
                'Zone City:'                 => 'zone.address.city',
                'Zone State/Province:'       => 'zone.address.state',
                'Zone Postal Code:'          => 'zone.address.pcode',
                'Zone Country:'              => 'zone.address.country',
                'Zone Phone Number:'         => 'zone.phone',
                'Zone Fax Number:'           => 'zone.fax',
                'Zone Email:'                => 'zone.email',
                  );

        return generic_parser_b($data_str, $items);
    }
}
