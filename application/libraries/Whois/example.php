<?php

header('Content-Type: text/html; charset=UTF-8');

$out =  implode('', file('example.html'));

$out = str_replace('{self}', $_SERVER['PHP_SELF'], $out);

$resout = extract_block($out, 'results');

if (isset($_GET['query'])) {
    $query = $_GET['query'];

    if (!empty($_GET['output'])) {
        $output = $_GET['output'];
    } else {
        $output = '';
    }

    include_once 'whois.main.php';
    include_once 'whois.utils.php';

    $whois = new Whois();

    // Set to true if you want to allow proxy requests
    $allowproxy = false;

    // uncomment the following line to get faster but less acurate results
    // $whois->deep_whois = false;

    // To use special whois servers (see README)
    //$whois->UseServer('uk','whois.nic.uk:1043?{hname} {ip} {query}');
    //$whois->UseServer('au','whois-check.ausregistry.net.au');

    // uncomment the following line to add support for non ICANN tld's
    // $whois->non_icann = true;

    $result = $whois->Lookup($query);

    $resout = str_replace('{query}', $query, $resout);
    $winfo = '';

    switch ($output) {
        case 'object':
            if ($whois->Query['status'] < 0) {
                $winfo = implode($whois->Query['errstr'], "\n<br></br>");
            } else {
                $utils = new utils();
                $winfo = $utils->showObject($result);
            }
            break;

        case 'nice':
            if (!empty($result['rawdata'])) {
                $utils = new utils();
                $winfo = $utils->showHTML($result);
            } else {
                if (isset($whois->Query['errstr'])) {
                    $winfo = implode($whois->Query['errstr'], "\n<br></br>");
                } else {
                    $winfo = 'Unexpected error';
                }
            }
            break;

        case 'proxy':
            if ($allowproxy) {
                exit(serialize($result));
            }

        default:
            if (!empty($result['rawdata'])) {
                $winfo .= '<pre>' . implode($result['rawdata'], "\n") . '</pre>';
            } else {
                $winfo = implode($whois->Query['errstr'], "\n<br></br>");
            }
        }

    //$winfo = utf8_encode($winfo);

    $resout = str_replace('{result}', $winfo, $resout);
} else {
    $resout = '';
}

echo str_replace('{results}', $resout, $out);

//-------------------------------------------------------------------------

function extract_block(&$plantilla, $mark, $retmark = '')
{
    $start = strpos($plantilla, '<!--' . $mark . '-->');
    $final = strpos($plantilla, '<!--/' . $mark . '-->');

    if ($start === false || $final === false) {
        return;
    }

    $ini = $start + 7 + strlen($mark);

    $ret = substr($plantilla, $ini, $final - $ini);

    $final += 8 + strlen($mark);

    if ($retmark === false) {
        $plantilla = substr($plantilla, 0, $start) . substr($plantilla, $final);
    } else {
        if ($retmark == '') {
            $retmark = $mark;
        }
        $plantilla = substr($plantilla, 0, $start) . '{' . $retmark . '}' . substr($plantilla, $final);
    }

    return $ret;
}
