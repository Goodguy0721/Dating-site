<?php

/*		     2.0	David Saez <david@ols.es> */
/*				standarized object model */

if (!defined('__CA_HANDLER__')) {
    define('__CA_HANDLER__', 1);
}

require_once 'whois.parser.php';

class ca_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                        'owner'            => 'Registrant:',
                        'admin'            => 'Administrative contact:',
                        'tech'             => 'Technical contact:',
                        'domain.nserver'   => 'Name servers:',
                        'domain.status'    => 'Domain status:',
                        'domain.created'   => 'Approval date:',
                        'domain.expires'   => 'Renewal date:',
                        'domain.changed'   => 'Name servers last changed:',
                        );

        $extra = array(
                        'postal address:' => 'address.0',
                        'job title:'      => '',
                        'number:'         => 'handle',
                        'description:'    => 'organization',
                        );

        $r['regrinfo'] = easy_parser($data_str['rawdata'], $items, 'ymd', $extra);

        $r['regyinfo'] = array(
                  'registrar' => 'CIRA',
                  'referrer'  => 'http://www.cira.ca/',
                  );

        if (empty($r['regrinfo']['domain']['status']) || $r['regrinfo']['domain']['status'] == 'AVAIL') {
            $r['regrinfo']['registered'] = 'no';
        } else {
            $r['regrinfo']['registered'] = 'yes';
        }

        return ($r);
    }
}
