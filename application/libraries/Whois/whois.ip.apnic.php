<?php

require_once 'whois.parser.php';

if (!defined('__APNIC_HANDLER__')) {
    define('__APNIC_HANDLER__', 1);
}

class apnic_handler
{
  public function parse($data_str, $query)
  {
      $translate = array(
                      'fax-no'  => 'fax',
                      'e-mail'  => 'email',
                      'nic-hdl' => 'handle',
                      'person'  => 'name',
                      'country' => 'address',
                      'netname' => 'name',
                      'descr'   => 'desc',
                      );

      $contacts = array(
                      'admin-c' => 'admin',
                      'tech-c'  => 'tech',
                      );

      $r = generic_parser_a($data_str, $translate, $contacts, 'network', 'Ymd');

      if (isset($r['network']['desc'])) {
          if (is_array($r['network']['desc'])) {
              $r['owner']['organization'] = array_shift($r['network']['desc']);
              $r['owner']['address'] = $r['network']['desc'];
          } else {
              $r['owner']['organization'] = $r['network']['desc'];
          }

          unset($r['network']['desc']);
      }

      if (isset($r['network']['address'])) {
          if (isset($r['owner']['address'])) {
              $r['owner']['address'][] = $r['network']['address'];
          } else {
              $r['owner']['address'] = $r['network']['address'];
          }

          unset($r['network']['address']);
      }

      return $r;
  }
}
