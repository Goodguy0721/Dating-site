<?php

/*                      Fixed detection of non-existant domains */
/* uknic.whois  1.3     8/1/2002 Added status (active/inactive/detagged) */
/*                      and corrected error for detagged domains */
/*                      (like blue.co.uk) thanx to Adam Greedus */
/* uknic.whois  1.4     16/10/2002 Updated for new Nominet whois output */
/*                      also updated for common object model */
/* uknic.whois  1.5     03/03/2003 minor fixes */

if (!defined('__UK_HANDLER__')) {
    define('__UK_HANDLER__', 1);
}

require_once 'whois.parser.php';

class uk_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner.organization' => 'Registrant:',
                'owner.address'      => "Registrant's address:",
                'domain.created'     => 'Registered on:',
                'domain.changed'     => 'Last updated:',
                'domain.expires'     => 'Renewal date:',
                'domain.nserver'     => 'Name servers:',
                'domain.sponsor'     => 'Registrar:',
                'domain.status'      => 'Registration status:',
                ''                   => 'WHOIS lookup made at',
                );

        $r['regrinfo'] = get_blocks($data_str['rawdata'], $items);

        if (isset($r['regrinfo']['owner'])) {
            $r['regrinfo']['owner']['organization'] = $r['regrinfo']['owner']['organization'][0];
            $r['regrinfo']['domain']['sponsor'] = $r['regrinfo']['domain']['sponsor'][0];

            //unset($r['regrinfo']['domain']['nserver'][count($r['regrinfo']['domain']['nserver']) - 1]);
            $r['regrinfo']['registered'] = 'yes';

            $r = format_dates($r, 'dmy');
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        $r['regyinfo'] = array(
                    'referrer'  => 'http://www.monimet.uk',
                    'registrar' => 'Nominet UK',
                        );

        return $r;
    }
}
