<?php

if (!defined('__PL_HANDLER__')) {
    define('__PL_HANDLER__', 1);
}

require_once 'whois.parser.php';

class pl_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                    'created:'                 => 'domain.created',
                    'last modified'            => 'domain.changed',
                    'REGISTRAR:'               => 'domain.sponsor',
                    "registrant's handle:"     => 'owner.handle',

                    );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, 'ymd');

        if ($r['regrinfo']['registered'] == 'yes') {
            $found = false;

            foreach ($data_str['rawdata'] as $line) {
                if ($found) {
                    if (strpos($line, ':')) {
                        break;
                    }
                    $r['regrinfo']['domain']['nserver'][] = $line;
                }

                if (strpos($line, 'nameservers:') !== false) {
                    $found = true;
                    $r['regrinfo']['domain']['nserver'][] = substr($line, 13);
                }
            }
        }

        $r['regyinfo'] = array(
            'referrer'  => 'http://www.dns.pl/english/index.html',
            'registrar' => 'NASK',
            );

        return ($r);
    }
}
