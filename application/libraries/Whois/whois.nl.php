<?php

/* nlnic.whois    1.1    David Saez - common object model */
/* nlnic.whois    1.0    Matthijs Koot - 2003/01/14 - <koot@cyberwar.nl> */

if (!defined('__NL_HANDLER__')) {
    define('__NL_HANDLER__', 1);
}

require_once 'whois.parser.php';

class nl_handler
{
    public function parse($data, $query)
    {
        $items = array(
                  'domain.name'    => 'Domain name:',
                  'domain.status'  => 'Status:',
                  'domain.nserver' => 'Domain nameservers:',
                  'domain.created' => 'Date registered:',
                  'domain.changed' => 'Record last updated:',
                  'domain.sponsor' => 'Record maintained by:',
                  'owner'          => 'Registrant:',
                  'admin'          => 'Administrative contact:',
                  'tech'           => 'Technical contact(s):',
                  'zone'           => 'Registrar:',
                    );

        $r['regrinfo'] = get_blocks($data['rawdata'], $items);
        $r['regyinfo']['referrer'] = 'http://www.domain-registry.nl';
        $r['regyinfo']['registrar'] = 'Stichting Internet Domeinregistratie NL';

        if (!isset($r['regrinfo']['domain']['status'])) {
            $r['regrinfo']['registered'] = 'no';

            return $r;
        }

        if (isset($r['regrinfo']['tech'])) {
            $r['regrinfo']['tech'] = $this->get_contact($r['regrinfo']['tech']);
        }

        if (isset($r['regrinfo']['zone'])) {
            $r['regrinfo']['zone'] = $this->get_contact($r['regrinfo']['zone']);
        }

        if (isset($r['regrinfo']['admin'])) {
            $r['regrinfo']['admin'] = $this->get_contact($r['regrinfo']['admin']);
        }

        if (isset($r['regrinfo']['owner'])) {
            $r['regrinfo']['owner'] = $this->get_contact($r['regrinfo']['owner']);
        }

        $r['regrinfo']['registered'] = 'yes';
        format_dates($r, 'dmy');

        return ($r);
    }

    public function get_contact($data)
    {
        $r = get_contact($data);

        if (isset($r['name']) && preg_match('/^[A-Z0-9]+-[A-Z0-9]+$/', $r['name'])) {
            $r['handle'] = $r['name'];
            $r['name'] = array_shift($r['address']);
        }

        return $r;
    }
}
