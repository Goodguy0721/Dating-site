<?php

/* registercom.whois    2.1     david@ols.es    2003/02/18 */

if (!defined('__REGISTER_HANDLER__')) {
    define('__REGISTER_HANDLER__', 1);
}

require_once 'whois.parser.php';

class register_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'owner#0'          => 'Registrant Info:',
                  'owner#1'          => 'Organization:',
                  'owner#2'          => 'Registrant:',
                  'owner#3'          => 'Registrant Contact:',
                  'admin'            => 'Administrative',
                  'tech'             => 'Technical',
                  'zone'             => 'Zone',
                  'domain.sponsor#0' => 'Registrar Name....:',
                  'domain.sponsor#1' => 'Registration Service Provided By:',
                  'domain.nserver'   => 'Domain servers in listed order:',
                  'domain.name'      => 'Domain name:',
                  'domain.created#0' => 'Created on..............:',
                  'domain.created#1' => 'Creation date:',
                  'domain.expires#0' => 'Expires on..............:',
                  'domain.expires#1' => 'Expiration date:',
                  'domain.changed'   => 'Record last updated on..:',
                  'domain.status'    => 'Status:',
                    );

        return easy_parser($data_str, $items, 'ymd');
    }
}
