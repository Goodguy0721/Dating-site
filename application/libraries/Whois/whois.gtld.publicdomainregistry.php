<?php

if (!defined('__PUBLICDOMAINREGISTRY_HANDLER__')) {
    define('__PUBLICDOMAINREGISTRY_HANDLER__', 1);
}

require_once 'whois.parser.php';

class publicdomainregistry_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
              'owner'           => 'Registrant:',
              'owner#'          => '(Registrant):',
              'admin'           => 'Administrative Contact',
              'tech'            => 'Technical Contact',
              'domain.name'     => 'Domain name:',
              'domain.sponsor'  => 'Registration Service Provided By:',
              'domain.nserver'  => 'Domain servers in listed order:',
              'domain.changed'  => 'Record last updated ',
              'domain.created'  => 'Record created on',
              'domain.created#' => 'Creation Date:',
              'domain.expires'  => 'Record expires on',
              'domain.expires#' => 'Expiration Date:',
                  );

        return easy_parser($data_str, $items, 'mdy', false, true, true);
    }
}
