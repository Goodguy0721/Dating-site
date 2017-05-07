<?php

/* info.whois	1.0  David Saez Padros <david@ols.es> */

if (!defined('__FI_HANDLER__')) {
    define('__FI_HANDLER__', 1);
}

require_once 'whois.parser.php';

class fi_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'domain:'  => 'domain.name',
                  'created:' => 'domain.created',
                  'expires:' => 'domain.expires',
                  'status:'  => 'domain.status',
                  'nserver:' => 'domain.nserver.',
                  'descr:'   => 'owner.name.',
                  'address:' => 'owner.address.',
                  'phone:'   => 'owner.phone',
                    );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items);

        $r['regyinfo'] = array(
                          'referrer'  => 'https://domain.ficora.fi/',
                          'registrar' => 'Finnish Communications Regulatory Authority',
                          );

        return $r;
    }
}
