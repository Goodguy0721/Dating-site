<?php

/* Based upon uknic.whois by David Saez Padros */

if (!defined('__NU_HANDLER__')) {
    define('__NU_HANDLER__', 1);
}

require_once 'whois.parser.php';

class nu_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
                  'name'    => 'Domain Name (UTF-8):',
                  'created' => 'Record created on',
                  'expires' => 'Record expires on',
                  'changed' => 'Record last updated on',
                  'status'  => 'Record status:',
                  'handle'  => 'Record ID:',
                      );

        while (list($key, $val) = each($data_str['rawdata'])) {
            $val = trim($val);

            if ($val != '') {
                if ($val == 'Domain servers in listed order:') {
                    while (list($key, $val) = each($data_str['rawdata'])) {
                        $val = trim($val);
                        if ($val == '') {
                            break;
                        }
                        $r['regrinfo']['domain']['nserver'][] = $val;
                    }
                    break;
                }

                reset($items);

                while (list($field, $match) = each($items)) {
                    if (strstr($val, $match)) {
                        $r['regrinfo']['domain'][$field] = trim(substr($val, strlen($match)));
                        break;
                    }
                }
            }
        }

        if (isset($r['regrinfo']['domain'])) {
            $r['regrinfo']['registered'] = 'yes';
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        $r['regyinfo'] = array(
                          'whois'     => 'whois.nic.nu',
                          'referrer'  => 'http://www.nunames.nu',
                          'registrar' => '.NU Domain, Ltd',
                              );

        format_dates($r, 'dmy');

        return ($r);
    }
}
