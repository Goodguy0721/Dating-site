<?php

/* senic.whois	0.99	Stefan Alfredsson <stefan@alfredsson.org> */
/* Based upon uknic.whois by David Saez Padros */

if (!defined('__SE_HANDLER__')) {
    define('__SE_HANDLER__', 1);
}

require_once 'whois.parser.php';

class se_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                    'domain'   => 'domain.name',
                    'state:'   => 'domain.status.',
                    'status:'  => 'domain.status.',
                    'expires:' => 'domain.expires',
                    'created:' => 'domain.created',
                    'nserver:' => 'domain.nserver.',
                    'holder:'  => 'owner.handle',
                    );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, 'mdy', false);

        $r['regrinfo']['registered'] = isset($r['regrinfo']['domain']['name']) ? 'yes' : 'no';

        $r['regyinfo'] = array(
                    'referrer'  => 'http://www.nic-se.se',
                    'registrar' => 'NIC-SE',
                        );

        return ($r);
    }
}
