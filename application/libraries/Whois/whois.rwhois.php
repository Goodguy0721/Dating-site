<?php

if (!defined('__RWHOIS_HANDLER__')) {
    define('__RWHOIS_HANDLER__', 1);
}

require_once 'whois.parser.php';

class rwhois_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                        'network:Organization-Name:'    => 'owner.name',
                        'network:Organization;I:'       => 'owner.organization',
                        'network:Organization-City:'    => 'owner.address.city',
                        'network:Organization-Zip:'     => 'owner.address.pcode',
                        'network:Organization-Country:' => 'owner.address.country',
                        'network:IP-Network-Block:'     => 'network.inetnum',
                        'network:IP-Network:'           => 'network.inetnum',
                        'network:Network-Name:'         => 'network.name',
                        'network:ID:'                   => 'network.handle',
                        'network:Created:'              => 'network.created',
                        'network:Updated:'              => 'network.changed',
                        'network:Tech-Contact;I:'       => 'tech.email',
                        'network:Admin-Contact;I:'      => 'admin.email',
                        );

        $res = generic_parser_b($data_str, $items, 'Ymd', false);

        unset($res['disclaimer']);

        return ($res);
    }
}
