<?php

if (!defined('__ENCIRCA_HANDLER__')) {
    define('__ENCIRCA_HANDLER__', 1);
}

require_once 'whois.parser.php';

class encirca_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'Domain Name:'                             => 'domain.name',
                  'Sponsoring Registrar:'                    => 'domain.sponsor',
                  'Name Server:'                             => 'domain.nserver.',
                  'Registrant Name:'                         => 'owner.name',
                  'Registrant Organization:'                 => 'owner.organization',
                  'Registrant Address1:'                     => 'owner.address.street.0',
                  'Registrant Address2:'                     => 'owner.address.street.1',
                  'Registrant Postal Code:'                  => 'owner.address.pcode',
                  'Registrant City:'                         => 'owner.address.city',
                  'Registrant State/Province:'               => 'owner.address.state',
                  'Registrant Country:'                      => 'owner.address.country',
                  'Registrant Phone:'                        => 'owner.phone',
                  'Registrant Facsimile Number:'             => 'owner.fax',
                  'Registrant Email:'                        => 'owner.email',
                  'Administrative Contact Name:'             => 'admin.name',
                  'Administrative Contact Organization:'     => 'admin.organization',
                  'Administrative Contact Address1:'         => 'admin.address.street.0',
                  'Administrative Contact Address2:'         => 'admin.address.street.1',
                  'Administrative Contact Postal Code:'      => 'admin.address.pcode',
                  'Administrative Contact City:'             => 'admin.address.city',
                  'Administrative Contact State/Province:'   => 'admin.address.state',
                  'Administrative Contact Country:'          => 'admin.address.country',
                  'Administrative Contact Phone:'            => 'admin.phone',
                  'Administrative Contact Email:'            => 'admin.email',
                  'Administrative Contact Facsimile Number:' => 'admin.fax',
                  'Technical Contact Name:'                  => 'tech.name',
                  'Technical Contact Organization:'          => 'tech.organization',
                  'Technical Contact Address1:'              => 'tech.address.street.0',
                  'Technical Contact Address2:'              => 'tech.address.street.1',
                  'Technical Contact Postal Code:'           => 'tech.address.pcode',
                  'Technical Contact City:'                  => 'tech.address.city',
                  'Technical Contact State/Province:'        => 'tech.address.state',
                  'Technical Contact Country:'               => 'tech.address.country',
                  'Technical Contact Phone:'                 => 'tech.phone',
                  'Technical Contact Facsimile Number:'      => 'tech.fax',
                  'Technical Contact Email:'                 => 'tech.email',
                    );

        $r['regrinfo'] = generic_parser_b($data_str, $items, '-md--y');

        $r['regyinfo'] = array(
                  'referrer'  => 'http://www.encirca.biz',
                  'registrar' => 'ENCIRCA',
                  );

        return ($r);
    }
}
