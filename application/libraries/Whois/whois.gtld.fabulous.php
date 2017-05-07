<?php

if (!defined('__FABULOUS_HANDLER__')) {
    define('__FABULOUS_HANDLER__', 1);
}

require_once 'whois.parser.php';

class fabulous_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
              'owner'   => 'Domain ' . $query . ':',
              'admin'   => 'Administrative contact:',
              'tech'    => 'Technical contact:',
              'billing' => 'Billing contact:',
              ''        => 'Record dates:',
                  );

        $r = easy_parser($data_str, $items, 'mdy', false, false, true);

        if (!isset($r['tech'])) {
            $r['tech'] = $r['billing'];
        }

        if (!isset($r['admin'])) {
            $r['admin'] = $r['tech'];
        }

        return ($r);
    }
}
