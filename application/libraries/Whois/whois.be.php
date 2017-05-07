<?php

/* benic.whois        1.2	 David Saez */

require_once 'whois.parser.php';

if (!defined('__BE_HANDLER__')) {
    define('__BE_HANDLER__', 1);
}

class be_handler
{
    public function parse($data, $query)
    {
        $items = array(
                'domain.name'          => 'Domain:',
                'domain.status'        => 'Status:',
                'domain.nserver'       => 'Nameservers:',
                'domain.created'       => 'Registered:',
                'owner'                => 'Licensee:',
                'admin'                => 'Onsite Contacts:',
                'tech'                 => 'Agent Technical Contacts:',
                'agent'                => 'Agent:',
                );

        $r['regrinfo'] = get_blocks($data['rawdata'], $items);

        if (isset($r['regrinfo']['domain']['name'])) {
            $r['regrinfo']['registered'] = 'yes';

            $r['regrinfo'] = get_contacts($r['regrinfo']);

            if (isset($r['regrinfo']['agent'])) {
                $sponsor = get_contact($r['regrinfo']['agent']);
                unset($r['regrinfo']['agent']);
                $r['regrinfo']['domain']['sponsor'] = $sponsor['name'];
            }

            $r = format_dates($r, '-mdy');
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        $r['regyinfo']['referrer'] = 'http://www.domain-registry.nl';
        $r['regyinfo']['registrar'] = 'DNS Belgium';
        $r['rawdata'] = $data['rawdata'];

        return ($r);
    }
}
