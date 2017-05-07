<?php

if (!defined('__SU_HANDLER__')) {
    define('__SU_HANDLER__', 1);
}

require_once 'whois.parser.php';

class su_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'domain:'    => 'domain.name',
                  'state:'     => 'domain.status',
                  'person:'    => 'owner.name',
                  'phone:'     => 'owner.phone',
                  'e-mail:'    => 'owner.email',
                  'created:'   => 'domain.created',
                  'paid-till:' => 'domain.expires',
/*
                  'nserver:' => 'domain.nserver.',
                  'source:' => 'domain.source',
                  'type:' => 'owner.type',
                  'org:' => 'owner.organization',
                  'fax-no:' => 'owner.fax',
*/
                      );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, 'dmy');

        $r['regyinfo'] = array(
                            'referrer'  => 'http://www.ripn.net',
                            'registrar' => 'RUCENTER-REG-RIPN',
                          );

        return ($r);
    }
}
