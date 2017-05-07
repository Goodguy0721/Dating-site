<?php

if (!defined('__FM_HANDLER__')) {
    define('__FM_HANDLER__', 1);
}

require_once 'whois.parser.php';

class fm_handler
{
    public function parse($data, $query)
    {
        $items = array(
                  'owner'          => 'Registrant',
                  'admin'          => 'Administrative',
                  'tech'           => 'Technical',
                  'billing'        => 'Billing',
                  'domain.nserver' => 'Name Servers:',
                  'domain.created' => 'Created:',
                  'domain.expires' => 'Expires:',
                  'domain.changed' => 'Modified:',
                  'domain.status'  => 'Status:',
                  'domain.sponsor' => 'Registrar:',
                  );

        $r['regrinfo'] = get_blocks($data['rawdata'], $items);

        $items = array('voice:' => 'phone');

        if (!empty($r['regrinfo']['domain']['created'])) {
            $r['regrinfo'] = get_contacts($r['regrinfo'], $items);

            if (count($r['regrinfo']['billing']['address']) > 4) {
                $r['regrinfo']['billing']['address'] = array_slice($r['regrinfo']['billing']['address'], 0, 4);
            }

            $r['regrinfo']['registered'] = 'yes';
            format_dates($r['regrinfo']['domain'], 'dmY');
        } else {
            $r = '';
            $r['regrinfo']['registered'] = 'no';
        }

        $r['regyinfo']['referrer'] = 'http://www.dot.dm';
        $r['regyinfo']['registrar'] = 'dotFM';
        $r['rawdata'] = $data['rawdata'];

        return ($r);
    }
}
