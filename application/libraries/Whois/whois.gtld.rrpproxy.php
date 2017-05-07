<?php

if (!defined('__RRPPROXY_HANDLER__')) {
    define('__RRPPROXY_HANDLER__', 1);
}

require_once 'whois.parser.php';

class rrpproxy_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'created-date:'                 => 'domain.created',
                  'updated-date:'                 => 'domain.changed',
                  'registration-expiration-date:' => 'domain.expires',
                  'RSP:'                          => 'domain.sponsor',
                  'owner-contact:'                => 'owner.handle',
                  'owner-fname:'                  => 'owner.name.first',
                  'owner-lname:'                  => 'owner.name.last',
                  'owner-organization:'           => 'owner.organization',
                  'owner-street:'                 => 'owner.address.street',
                  'owner-city:'                   => 'owner.address.city',
                  'owner-zip:'                    => 'owner.address.pcode',
                  'owner-country:'                => 'owner.address.country',
                  'owner-phone:'                  => 'owner.phone',
                  'owner-fax:'                    => 'owner.fax',
                  'owner-email:'                  => 'owner.email',
                  'admin-contact:'                => 'admin.handle',
                  'admin-fname:'                  => 'admin.name.first',
                  'admin-lname:'                  => 'admin.name.last',
                  'admin-organization:'           => 'admin.organization',
                  'admin-street:'                 => 'admin.address.street',
                  'admin-city:'                   => 'admin.address.city',
                  'admin-zip:'                    => 'admin.address.pcode',
                  'admin-country:'                => 'admin.address.country',
                  'admin-phone:'                  => 'admin.phone',
                  'admin-fax:'                    => 'admin.fax',
                  'admin-email:'                  => 'admin.email',
                  'tech-contact:'                 => 'tech.handle',
                  'tech-fname:'                   => 'tech.name.first',
                  'tech-lname:'                   => 'tech.name.last',
                  'tech-organization:'            => 'tech.organization',
                  'tech-street:'                  => 'tech.address.street',
                  'tech-city:'                    => 'tech.address.city',
                  'tech-zip:'                     => 'tech.address.pcode',
                  'tech-country:'                 => 'tech.address.country',
                  'tech-phone:'                   => 'tech.phone',
                  'tech-fax:'                     => 'tech.fax',
                  'tech-email:'                   => 'tech.email',
                  'billing-contact:'              => 'bill.handle',
                  'billing-fname:'                => 'bill.name.first',
                  'billing-lname:'                => 'bill.name.last',
                  'billing-organization:'         => 'bill.organization',
                  'billing-street:'               => 'bill.address.street',
                  'billing-city:'                 => 'bill.address.city',
                  'billing-zip:'                  => 'bill.address.pcode',
                  'billing-country:'              => 'bill.address.country',
                  'billing-phone:'                => 'bill.phone',
                  'billing-fax:'                  => 'bill.fax',
                  'billing-email:'                => 'bill.email',
                      );

        $r = generic_parser_b($data_str, $items);

        return ($r);
    }
}
