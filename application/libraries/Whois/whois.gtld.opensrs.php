<?php

/* opensrsnet.whois     2.1     david@ols.es            2003/02/15 */

if (!defined('__OPENSRS_HANDLER__')) {
    define('__OPENSRS_HANDLER__', 1);
}

require_once 'whois.parser.php';

class opensrs_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner'              => 'Registrant:',
                  'admin'              => 'Administrative Contact',
                  'tech'               => 'Technical Contact',
                  'domain.name'        => 'Domain name:',
                  'domain.sponsor'     => 'Registration Service Provider:',
                  'domain.nserver'     => 'Domain servers in listed order:',
                  'domain.changed'     => 'Record last updated on',
                  'domain.created'     => 'Record created on',
                  'domain.expires'     => 'Record expires on',
                  'domain.sponsor'     => 'Registrar of Record:',
                      );

        $r = easy_parser($data_str, $items, 'dmy', false, false, true);

        if (isset($r['domain']['sponsor']) && is_array($r['domain']['sponsor'])) {
            $r['domain']['sponsor'] = $r['domain']['sponsor'][0];
        }

        return ($r);
    }
}
