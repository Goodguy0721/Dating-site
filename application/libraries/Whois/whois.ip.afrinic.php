<?php

require_once 'whois.parser.php';

if (!defined('__AFRINIC_HANDLER__')) {
    define('__AFRINIC_HANDLER__', 1);
}

class afrinic_handler
{
    public function parse($data_str, $query)
    {
        $translate = array(
                      'fax-no'       => 'fax',
                      'e-mail'       => 'email',
                      'nic-hdl'      => 'handle',
                      'person'       => 'name',
                      'netname'      => 'name',
                      'descr'        => 'desc',
                      'organisation' => 'handle',
                      'org-name'     => 'organization',
                      'org-type'     => 'type',
                          );

        $contacts = array(
                      'admin-c' => 'admin',
                      'tech-c'  => 'tech',
                      'org'     => 'owner',
                          );

        $r = generic_parser_a($data_str, $translate, $contacts, 'network', 'Ymd');

        if (isset($r['owner']['remarks']) && is_array($r['owner']['remarks'])) {
            while (list($key, $val) = each($r['owner']['remarks'])) {
                $pos = strpos($val, 'rwhois://');

                if ($pos !== false) {
                    $r['rwhois'] = strtok(substr($val, $pos), ' ');
                }
            }
        }

        return $r;
    }
}
