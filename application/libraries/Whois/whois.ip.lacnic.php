<?php

require_once 'whois.parser.php';

if (!defined("__LACNIC_HANDLER__")) {
    define("__LACNIC_HANDLER__", 1);
}

class lacnic_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
                      "fax-no"  => "fax",
                      "e-mail"  => "email",
                      "nic-hdl" => "handle",
                      "person"  => "name",
                      "netname" => "name",
                      "descr"   => "desc",
                      "country" => "address.country",
                          );

        $contacts = array(
                      "admin-c" => "admin",
                      "tech-c"  => "tech",
                      "owner-c" => "owner",
                          );

        $r = generic_parser_a($data_str, $translate, $contacts, "network");

        if (isset($r['network']['nsstat'])) {
            unset($r['network']['nsstat']);
            unset($r['network']['nslastaa']);
        }

        if (isset($r['network']['owner'])) {
            $r['owner']['organization'] = $r['network']['owner'];
            unset($r['network']['owner']);
            unset($r['network']['responsible']);
            unset($r['network']['address']);
            unset($r['network']['phone']);
            unset($r['network']['inetrev']);
            unset($r['network']['ownerid']);
        }

        return $r;
    }
}
