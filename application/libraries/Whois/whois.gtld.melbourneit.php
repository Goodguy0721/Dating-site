<?php

/* inwwcom.whois        2.0     david@ols.es            2003/02/09 */

require_once 'whois.parser.php';

if (!defined('__MELBOURNEIT_HANDLER__')) {
    define('__MELBOURNEIT_HANDLER__', 1);
}

class melbourneit_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'Domain Name..........' => 'domain.name',
                  'Registration Date....' => 'domain.created',
                  'Expiry Date..........' => 'domain.expires',
                  'Organisation Name....' => 'owner.name',
                  'Organisation Address.' => 'owner.address.',
                  'Admin Name...........' => 'admin.name',
                  'Admin Address........' => 'admin.address.',
                  'Admin Email..........' => 'admin.email',
                  'Admin Phone..........' => 'admin.phone',
                  'Admin Fax............' => 'admin.fax',
                  'Tech Name............' => 'tech.name',
                  'Tech Address.........' => 'tech.address.',
                  'Tech Email...........' => 'tech.email',
                  'Tech Phone...........' => 'tech.phone',
                  'Tech Fax.............' => 'tech.fax',
                  'Name Server..........' => 'domain.nserver.',
                      );

        return generic_parser_b($data_str, $items, 'ymd');
    }
}
