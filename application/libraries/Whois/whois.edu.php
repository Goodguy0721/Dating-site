<?php

if (!defined('__EDU_HANDLER__')) {
    define('__EDU_HANDLER__', 1);
}

require_once 'whois.parser.php';

class edu_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                'domain.name'    => 'Domain name:',
                'domain.sponsor' => 'Registrar:',
                'domain.nserver' => 'Name Servers:',
                'domain.changed' => 'Domain record last updated:',
                'domain.created' => 'Domain record activated:',
                'owner'          => 'Registrant:',
                'admin'          => 'Administrative Contact:',
                'tech'           => 'Technical Contact:',
                'billing'        => 'Billing Contact:',
                    );

        $r['regrinfo'] = easy_parser($data_str['rawdata'], $items, 'dmy');

        if (isset($b['tech'])) {
            if ($r['regrinfo']['tech']['name'] == 'Same as above') {
                $r['regrinfo']['tech'] = $r['regrinfo']['admin'];
            }
        }

        $r['regyinfo']['referrer'] = 'http://whois.educause.net';
        $r['regyinfo']['registrar'] = 'EDUCASE';

        return ($r);
    }
}
