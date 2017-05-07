<?php

/* aunic.whois 2.0 by David Saez <david@ols.es> common object model */
/* aunic.whois 1.0 by Edi Wibowo <edi@ausnik-it.com,http://www.ausnik-it.com> */
/* check with telstra.com.au */

if (!defined('__AU_HANDLER__')) {
    define('__AU_HANDLER__', 1);
}

require_once 'whois.parser.php';

class au_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                    'Domain Name:'                => 'domain.name',
                    'Last Modified:'              => 'domain.changed',
                    'Registrar Name:'             => 'domain.sponsor',
                    'Status:'                     => 'domain.status',
                    'Domain ROID:'                => 'domain.handle',
                    'Registrant:'                 => 'owner.organization',
                    'Registrant Contact ID:'      => 'owner.handle',
                    'Registrant Contact Email:'   => 'owner.email',
                    'Registrant Contact Name:'    => 'owner.name',
                    'Tech Contact Name:'          => 'tech.name',
                    'Tech Contact Email:'         => 'tech.email',
                    'Tech Contact ID:'            => 'tech.handle',
                    'Name Server:'                => 'domain.nserver.',
                      );

        $r['regyinfo'] = array(
                    'referrer'  => 'http://www.aunic.net',
                    'registrar' => 'AU-NIC',
                    );

        if ($data_str['rawdata'][0] == 'Not Available') {
            $r['regrinfo']['registered'] = 'yes';

            return $r;
        }

        if ($data_str['rawdata'][0] == 'Available') {
            $r['regrinfo']['registered'] = 'no';

            return $r;
        }

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

        return $r;
    }
}
