<?php

if (!defined('__INT_HANDLER__')) {
    define('__INT_HANDLER__', 1);
}

require_once 'whois.gtld.iana.php';

class int_handler
{
    public function parse($data_str, $query)
    {
        $iana = new iana_handler();

        $r['regrinfo'] = $iana->parse($data_str['rawdata'], $query);

        $r['regyinfo']['referrer'] = 'http://www.iana.org/int-dom/int.htm';
        $r['regyinfo']['registrar'] = 'Internet Assigned Numbers Authority';

        return ($r);
    }
}
