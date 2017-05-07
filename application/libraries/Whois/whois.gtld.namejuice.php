<?php

if (!defined('__NAMEJUICE_HANDLER__')) {
    define('__NAMEJUICE_HANDLER__', 1);
}

require_once 'whois.parser.php';

class namejuice_handler
{
        public function parse($data_str, $query)
        {
            $items = array(
                                'owner'           => 'Registrant Contact:',
                                'admin'           => 'Administrative Contact:',
                                'tech'            => 'Technical Contact:',
                                'domain.name'     => 'Domain name:',
                                'domain.nserver.' => 'Name Servers:',
                                'domain.created'  => 'Creation date:',
                                'domain.expires'  => 'Expiration date:',
                                'domain.changed'  => 'Update date:',
                                'domain.status'   => 'Status:',
                                'domain.sponsor'  => 'Registration Service Provided By:',
                              );

            return easy_parser($data_str, $items, 'dmy', false, true, true);
        }
}
