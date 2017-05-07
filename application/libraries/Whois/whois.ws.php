<?php

if (!defined('__WS_HANDLER__')) {
    define('__WS_HANDLER__', 1);
}

require_once 'whois.parser.php';

class ws_handler extends WhoisClient
{
  public function parse($data_str, $query)
  {
      $items = array(
                'Domain Name:'                        => 'domain.name',
                'Registrant Name:'                    => 'owner.organization',
                'Registrant Email:'                   => 'owner.email',
                'Domain Created:'                     => 'domain.created',
                'Domain Last Updated:'                => 'domain.changed',
                'Registrar Name:'                     => 'domain.sponsor',
                'Current Nameservers:'                => 'domain.nserver.',
                'Administrative Contact Email:'       => 'admin.email',
                'Administrative Contact Telephone:'   => 'admin.phone',
                'Registrar Whois:'                    => 'rwhois',
                );

      $r['regrinfo'] = generic_parser_b($data_str['rawdata'], $items, 'ymd');

      $r['regyinfo']['referrer'] = 'http://www.samoanic.ws';
      $r['regyinfo']['registrar'] = 'Samoa Nic';

      if (!empty($r['regrinfo']['domain']['name'])) {
          $r['regrinfo']['registered'] = 'yes';

          if (isset($r['regrinfo']['rwhois'])) {
              if ($this->deep_whois) {
                  $r['regyinfo']['whois']    = $r['regrinfo']['rwhois'];
                  $r = $this->DeepWhois($query, $r);
              }

              unset($r['regrinfo']['rwhois']);
          }
      } else {
          $r['regrinfo']['registered'] = 'no';
      }

      return ($r);
  }
}
