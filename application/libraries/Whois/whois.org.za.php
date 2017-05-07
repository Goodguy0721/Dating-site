<?php

require_once 'whois.parser.php';

if (!defined('__ORG_ZA_HANDLER__')) {
    define('__ORG_ZA_HANDLER__', 1);
}

class org_za_handler
{
    public function parse($data, $query)
    {
        $items = array(
                'domain.status'        => 'Status:',
                'domain.nserver'       => 'Domain name servers in listed order:',
                'domain.updated'       => 'Record last updated on',
                'owner'                => "rwhois search on 'sex.org.za':",
                'admin'                => 'Administrative Contact:',
                'tech'                 => 'Technical Contact:',
                'billing'              => 'Billing Contact:',
                '#'                    => 'Search Again',
                );

        $r['regrinfo'] = get_blocks($data['rawdata'], $items);

        if (isset($r['regrinfo']['domain']['status'])) {
            $r['regrinfo']['registered'] = 'yes';
            $r['regrinfo']['domain']['handler'] = strtok(array_shift($r['regrinfo']['owner']), ' ');
            $r['regrinfo'] = get_contacts($r['regrinfo']);
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        $r['regyinfo']['referrer'] = 'http://www.org.za';
        $r['regyinfo']['registrar'] = 'The ORG.ZA Domain';
        $r['rawdata'] = $data['rawdata'];

        return ($r);
    }
}
