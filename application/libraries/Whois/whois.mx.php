<?php

/* Based upon info.whois by David Saez Padros <david@ols.es> */

if (!defined('__MX_HANDLER__')) {
    define('__MX_HANDLER__', 1);
}

require_once 'whois.parser.php';

class mx_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                        'owner'          => 'Registrant:',
                        'admin'          => 'Administrative Contact:',
                        'tech'           => 'Technical Contact:',
                        'billing'        => 'Billing Contact:',
                        'domain.nserver' => 'Name Servers:',
                        'domain.created' => 'Created On:',
                        'domain.expires' => 'Expiration Date:',
                        'domain.changed' => 'Last Updated On:',
                        'domain.sponsor' => 'Registrar:',
                        );

        $extra = array(
                        'city:'     => 'address.city',
                        'state:'    => 'address.state',
                        'dns:'      => '0',
                        );

        $r['regrinfo'] = easy_parser($data_str['rawdata'], $items, 'dmy', $extra);

        $r['regyinfo'] = array(
                  'registrar' => 'NIC Mexico',
                  'referrer'  => 'http://www.nic.mx/',
                  );

        if (empty($r['regrinfo']['domain']['created'])) {
            $r['regrinfo']['registered'] = 'no';
        } else {
            $r['regrinfo']['registered'] = 'yes';
        }

        return ($r);
    }
}
