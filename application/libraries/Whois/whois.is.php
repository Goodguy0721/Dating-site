<?php

if (!defined("__IS_HANDLER__")) {
    define("__IS_HANDLER__", 1);
}

require_once 'whois.parser.php';

class is_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
                      "fax-no"  => "fax",
                      "e-mail"  => "email",
                      "nic-hdl" => "handle",
                      "person"  => "name",
                          );

        $contacts = array(
                      "owner-c"   => "owner",
                      "admin-c"   => "admin",
                      "tech-c"    => "tech",
                      "billing-c" => "billing",
                      "zone-c"    => "zone",
                          );

        $r["regyinfo"] = array(
                          "referrer"  => "http://www.isnic.is",
                          "registrar" => "ISNIC",
                          );

        $reg = generic_parser_a($data_str["rawdata"], $translate, $contacts, 'domain', 'mdy');

        if (isset($reg['domain']['descr'])) {
            $reg['owner']['name'] = array_shift($reg['domain']['descr']);
            $reg['owner']['address'] = $reg['domain']['descr'];
            unset($reg['domain']['descr']);
        }

        $r["regrinfo"] = $reg;

        return ($r);
    }
}
