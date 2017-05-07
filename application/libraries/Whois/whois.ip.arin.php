<?php

if (!defined('__ARIN_HANDLER__')) {
    define('__ARIN_HANDLER__', 1);
}

require_once 'whois.parser.php';

class arin_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'OrgName:'          => 'owner.organization',
                  'CustName:'         => 'owner.organization',
                  'OrgID:'            => 'owner.handle',
                  'Address:'          => 'owner.address.street',
                  'City:'             => 'owner.address.city',
                  'StateProv:'        => 'owner.address.state',
                  'PostalCode:'       => 'owner.address.pcode',
                  'Country:'          => 'owner.address.country',
                  'NetRange:'         => 'network.inetnum',
                  'NetName:'          => 'network.name',
                  'NetHandle:'        => 'network.handle',
                  'NetType:'          => 'network.status',
                  'NameServer:'       => 'network.nserver.',
                  'Comment:'          => 'network.desc.',
                  'RegDate:'          => 'network.created',
                  'Updated:'          => 'network.changed',
                  'ASHandle:'         => 'AS.handle',
                  'ASName:'           => 'network.name',
                  'NetHandle:'        => 'network.handle',
                  'NetName:'          => 'network.name',
                  'TechHandle:'       => 'tech.handle',
                  'TechName:'         => 'tech.name',
                  'TechPhone:'        => 'tech.phone',
                  'TechEmail:'        => 'tech.email',
                  'OrgAbuseName:'     => 'abuse.name',
                  'OrgAbuseHandle:'   => 'abuse.handle',
                  'OrgAbusePhone:'    => 'abuse.phone',
                  'OrgAbuseEmail:'    => 'abuse.email.',
                  'ReferralServer:'   => 'rwhois',
                      );

        $r = generic_parser_b($data_str, $items, 'ymd', false, true);

        if (isset($r['AS'])) {
            $ash = $r['AS']['handle'];
            $r['AS'] = $r['network'];
            $r['AS']['handle'] = $ash;
            unset($r['network']);
        }

        if (isset($r['abuse']['email'])) {
            $r['abuse']['email'] = implode(',', $r['abuse']['email']);
        }

        return $r;
    }
}
