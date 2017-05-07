<?php

/* denic.whois        0.4 by Oliver Notka <notka@ettel-gmbh.de> */
/* Fixed error when domain doesnt exist */

/* denic.whois        0.3 by David Saez <david@ols.es> */
/* denic.whois        0.2 by Elmar K. Bins <elmi@4ever.de> */
/* based upon brnic.whois by Marcelo Sanches <msanches@sitebox.com.br> */
/* and        atnic.whois by Martin Pircher <martin@pircher.net> */

/* this version does not yet deliver contact data, but handles only */

if (!defined('__DE_HANDLER__')) {
    define('__DE_HANDLER__', 1);
}

require_once 'whois.parser.php';

class de_handler
{
    public function parse($data_str, $query)
    {
        $items = array(
            'domain.name'      => 'Domain:',
            'domain.nserver.'  => 'Nserver:',
            'domain.nserver.#' => 'Nsentry:',
            'domain.status'    => 'Status:',
            'domain.changed'   => 'Changed:',
            'domain.desc.'     => 'Descr:',
            'owner'            => '[Holder]',
            'admin'            => '[Admin-C]',
            'tech'             => '[Tech-C]',
            'zone'             => '[Zone-C]',
                    );

        $extra = array(
            'address:'      => 'address.street',
            'city:'         => 'address.city',
            'pcode:'        => 'address.pcode',
            'country:'      => 'address.country',
            'organisation:' => 'organization',
            'name:'         => 'name',
            'remarks:'      => '',
            'type:'         => '',
                    );

        $r['regrinfo'] = easy_parser($data_str['rawdata'], $items, 'ymd', $extra);

        /*
        if (isset($r['regrinfo']['domain']['desc']))
            {
            if (!isset($r['regrinfo']['owner']['name']))
                $r['regrinfo']['owner']['name'] = $r['regrinfo']['domain']['desc'][0];

            if (!isset($r['regrinfo']['owner']['address']))
                for ($i=1; $i<count($r['regrinfo']['domain']['desc']); $i++)
                    $r['regrinfo']['owner']['address'][] = $r['regrinfo']['domain']['desc'][$i];

            unset($r['regrinfo']['domain']['desc']);
            }
        */

        $r['regyinfo'] = array(
                  'registrar' => 'DENIC eG',
                  'referrer'  => 'http://www.denic.de/',
                  );

        if (isset($r['regrinfo']['domain'])) {
            $r['regrinfo']['domain']['changed'] = substr($r['regrinfo']['domain']['changed'], 0, 10);
            $r['regrinfo']['registered'] = 'yes';
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        return $r;
    }
}
