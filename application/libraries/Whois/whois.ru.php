<?php

if (!defined('__RU_HANDLER__')) {
    define('__RU_HANDLER__', 1);
}

require_once 'whois.parser.php';

class ru_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'domain:'    => 'domain.name',
                  'state:'     => 'domain.status',
                  'nserver:'   => 'domain.nserver.',
                  'source:'    => 'domain.source',
                  'created:'   => 'domain.created',
                  'paid-till:' => 'domain.expires',
                  'type:'      => 'owner.type',
                  'org:'       => 'owner.organization',
                  'phone:'     => 'owner.phone',
                  'fax-no:'    => 'owner.fax',
                  'email:'     => 'admin.email',
                      );

        $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, 'dmy');

        $r['regyinfo'] = array(
                            'referrer'  => 'http://www.ripn.net',
                            'registrar' => 'RUCENTER-REG-RIPN',
                          );

        return ($r);
    }
}
