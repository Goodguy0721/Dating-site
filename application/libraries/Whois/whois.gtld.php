<?php

/* gtld.whois   1.1     david@ols.es    2003/02/09 */
/* gtld.whois   1.2     david@ols.es    2003/09/12 */

if (!defined('__GTLD_HANDLER__')) {
    define('__GTLD_HANDLER__', 1);
}

require_once 'whois.parser.php';

class gtld_handler extends WhoisClient
{
    // Deep whois ?
    //var $deep_whois = true;

    public $HANDLER_VERSION = '1.1';

    public $REG_FIELDS = array(
                        'Domain Name:'     => 'regrinfo.domain.name',
                        'Registrar:'       => 'regyinfo.registrar',
                        'Whois Server:'    => 'regyinfo.whois',
                        'Referral URL:'    => 'regyinfo.referrer',
                        'Name Server:'     => 'regrinfo.domain.nserver.',  // identical descriptors
                        'Updated Date:'    => 'regrinfo.domain.changed',
                        'Last Updated On:' => 'regrinfo.domain.changed',
                        'EPP Status:'      => 'regrinfo.domain.epp_status.',
                        'Status:'          => 'regrinfo.domain.status.',
                        'Creation Date:'   => 'regrinfo.domain.created',
                        'Created On:'      => 'regrinfo.domain.created',
                        'Expiration Date:' => 'regrinfo.domain.expires',
                        'Updated Date:'    => 'regrinfo.domain.changed',
                        'No match for '    => 'nodomain',
                         );

    public function parse($data, $query)
    {
        $this->Query = array();
        $this->SUBVERSION = sprintf('%s-%s', $query['handler'], $this->HANDLER_VERSION);
        $this->result = generic_parser_b($data['rawdata'], $this->REG_FIELDS, 'dmy');

        unset($this->result['registered']);

        if (isset($this->result['nodomain'])) {
            unset($this->result['nodomain']);
            $this->result['regrinfo']['registered'] = 'no';

            return $this->result;
        }

        $this->result['regrinfo']['registered'] = 'yes';

        if ($this->deep_whois) {
            $this->result = $this->DeepWhois($query, $this->result);
        }

        return $this->result;
    }
}
