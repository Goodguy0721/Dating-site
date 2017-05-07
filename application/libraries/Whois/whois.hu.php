<?php

/* based upon org.whois and atnic.whois */

if (!defined('__HU_HANDLER__')) {
    define('__HU_HANDLER__', 1);
}

require_once 'whois.parser.php';

class hu_handler
{
  public function parse($data_str, $query)
  {
      $translate = array(
                        'fax-no'         => 'fax',
                        'e-mail'         => 'email',
                        'hun-id'         => 'handle',
                        'person'         => 'name',
                        'nameserver'     => 'nserver',
                        'person'         => 'name',
                        'org'            => 'organization',
                        'registered'     => 'created',
                        );

      $contacts = array(
                        'registrar'        => 'owner',
                        'admin-c'          => 'admin',
                        'tech-c'           => 'tech',
                        'billing-c'        => 'billing',
                        'zone-c'           => 'zone',
                        'owner-hun-id'     => 'owner',
                      );

    // make those broken hungary comments standards-conforming
    // replace first found hun-id with owner-hun-id (will be parsed later on)
    // make output UTF-8

    $comments = true;
      $owner_id = true;

      foreach ($data_str['rawdata'] as $i => $val) {
          if ($comments) {
              if (strpos($data_str['rawdata'][$i], 'domain:') === false) {
                  if ($i) {
                      $data_str['rawdata'][$i] = '% ' . $data_str['rawdata'][$i];
                  }
              } else {
                  $comments = false;
              }
          } elseif ($owner_id && substr($data_str['rawdata'][$i], 0, 7) == 'hun-id:') {
              $data_str['rawdata'][$i] = 'owner-' . $data_str['rawdata'][$i];
              $owner_id = false;
          }

          $data_str['rawdata'][$i] = utf8_encode($data_str['rawdata'][$i]);
      }

      $reg = generic_parser_a($data_str['rawdata'], $translate, $contacts);

      unset($reg['domain']['organization']);
      unset($reg['domain']['address']);
      unset($reg['domain']['phone']);
      unset($reg['domain']['fax']);

      $r['regrinfo'] = $reg;
      $r['regyinfo'] = array('referrer' => 'http://www.nic.hu','registrar' => 'HUNIC');
      $r['rawdata'] = $data_str['rawdata'];
      format_dates($r, 'ymd');

      return($r);
  }
}
