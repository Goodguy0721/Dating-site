<?php

if (!defined('__CZ_HANDLER__')) {
    define('__CZ_HANDLER__', 1);
}

require_once 'whois.parser.php';

class cz_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
                      'expire'     => 'expires',
                      'registered' => 'created',
                      'nserver'    => 'nserver',
                      'domain'     => 'name',
                      'contact'    => 'handle',
                      'reg-c'      => '',
                      'descr'      => 'desc',
                      'e-mail'     => 'email',
                      'person'     => 'name',
                      'role'       => 'organization',
                      'fax-no'     => 'fax',
                          );

        $contacts = array(
                      'admin-c'    => 'admin',
                      'tech-c'     => 'tech',
                      'bill-c'     => 'billing',
                      'registrant' => 'owner',
                          );

        $r['regyinfo'] = array(
                          'referrer'  => 'http://www.nic.cz',
                          'registrar' => 'CZ-NIC',
                          );

        $reg = generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'dmy');
        $r['regrinfo'] = $reg;

        return ($r);
    }
}
