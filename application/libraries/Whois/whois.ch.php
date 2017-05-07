<?php

/*  8/1/2002    1.2     Added status (active/inactive) and corrected error */
/*                      for inactive domains */
/*                      (like creart.ch) thanx to Roger Fichmann */
/* 24/7/2002    2.0     David Saez - updated to new object model */
/* 17/3/2003    2.1     David Saez - rewritten to use generic3.whois */

require_once 'whois.parser.php';

if (!defined('__CH_HANDLER__')) {
    define('__CH_HANDLER__', 1);
}

class ch_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'owner'          => 'Holder of domain name:',
                'domain.name'    => 'Domain name:',
                'domain.created' => 'Date of last registration:',
                'domain.changed' => 'Date of last modification:',
                'tech'           => 'Technical contact:',
                'domain.nserver' => 'Name servers:',
                    );

        $r['regrinfo'] = get_blocks($data_str['rawdata'], $items);

        if (!empty($r['regrinfo']['domain']['name'])) {
            $r['regrinfo'] = get_contacts($r['regrinfo']);

            $r['regrinfo']['domain']['name'] = $r['regrinfo']['domain']['name'][0];

            if (isset($r['regrinfo']['domain']['changed'][0])) {
                $r['regrinfo']['domain']['changed'] = get_date($r['regrinfo']['domain']['changed'][0], 'dmy');
            }

            if (isset($r['regrinfo']['domain']['created'][0])) {
                $r['regrinfo']['domain']['created'] = get_date($r['regrinfo']['domain']['created'][0], 'dmy');
            }

            $r['regyinfo'] = array(
                          'referrer'  => 'http://www.nic.ch',
                          'registrar' => 'SWITCH Domain Name Registration',
                          );

            $r['regrinfo']['registered'] = 'yes';
        } else {
            $r = '';
            $r['regrinfo']['registered'] = 'no';
        }

        return ($r);
    }
}
