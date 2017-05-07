<?php

require_once 'whois.parser.php';

if (!defined('__BRIPW_HANDLER__')) {
    define('__BRIPW_HANDLER__', 1);
}

class bripw_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
                      'fax-no'     => 'fax',
                      'e-mail'     => 'email',
                      'nic-hdl-br' => 'handle',
                      'person'     => 'name',
                      'netname'    => 'name',
                          );

        $contacts = array(
                      'owner-c' => 'owner',
                      'tech-c'  => 'tech',
                      'abuse-c' => 'abuse',
                          );

        $r = generic_parser_a($data_str, $translate, $contacts, 'network');

        unset($r['network']['owner']);
        unset($r['network']['ownerid']);
        unset($r['network']['responsible']);
        unset($r['network']['address']);
        unset($r['network']['phone']);

        $r['network']['handle'] = $r['network']['aut-num'];

        unset($r['network']['aut-num']);
        unset($r['network']['nsstat']);
        unset($r['network']['nslastaa']);
        unset($r['network']['inetrev']);

        if (isset($r['network']['nserver'])) {
            $r['network']['nserver'] = array_unique($r['network']['nserver']);
        }

        return $r;
    }
}
