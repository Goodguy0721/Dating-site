<?php

/**
 * Extension for the CodeIgniter Input library
 *
 * @package PG_Core
 * @subpackage application
 *
 * @category	libraries
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Irina Lebedeva <irina@pilotgroup.net>
 *
 * @version $Revision: 395 $ $Date: 2010-09-28 12:19:44 +0400 (Вт, 28 сен 2010) $ $Author: kkashkova $
 **/
class PG_Input extends CI_Input
{
    public $user_os            = false;
    /**
     * Fetch an item from the POST array, and if item is empty and $def_value
     * is set - return $def_value
     *
     * @param string                 $index
     * @param mixed (boolean/string) $def_value
     * @param bool                   $xss_clean
     *
     * @return string
     */
    public function post_def($index = '', $def_value = false, $xss_clean = false)
    {
        $result = $this->_fetch_from_array($_POST, $index, $xss_clean);

        return (empty($result) && $def_value !== false) ? $def_value : $result;
    }

    /**
     * Fetch an item from the GET array, and if item is empty and $def_value
     * is set - return $def_value
     *
     * @param string                 $index
     * @param mixed (boolean/string) $def_value
     * @param bool                   $xss_clean
     *
     * @return string
     */
    public function get_def($index = '', $def_value = false, $xss_clean = false)
    {
        $result = $this->_fetch_from_array($_GET, $index, $xss_clean);

        return (empty($result) && $def_value !== false) ? $def_value : $result;
    }

    /**
     * Fetch an item from the REQUEST array, and if item is empty and $def_value
     * is set - return $def_value
     *
     * @param string                 $index
     * @param mixed (boolean/string) $def_value
     * @param bool                   $xss_clean
     *
     * @return string
     */
    public function request_def($index = '', $def_value = false, $xss_clean = false)
    {
        $result = $this->_fetch_from_array($_REQUEST, $index, $xss_clean);

        return (empty($result) && $def_value !== false) ? $def_value : $result;
    }

    public function get_os()
    {
        if ($this->user_os) {
            return $this->user_os;
        }

        $OSList = array(
            'Windows 3.11'        => 'Win16',
            'Windows 95'          => '(Windows 95)|(Win95)|(Windows_95)',
            'Windows 98'          => '(Windows 98)|(Win98)',
            'Windows 2000'        => '(Windows NT 5.0)|(Windows 2000)',
            'Windows XP'          => '(Windows NT 5.1)|(Windows XP)',
            'Windows Server 2003' => '(Windows NT 5.2)',
            'Windows Vista'       => '(Windows NT 6.0)',
            'Windows 7'           => '(Windows NT 7.0)',
            'Windows NT 4.0'      => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
            'Windows ME'          => 'Windows ME',
            'Open BSD'            => 'OpenBSD',
            'Sun OS'              => 'SunOS',
            'Linux'               => '(Linux)|(X11)',
            'Mac OS'              => '(Mac_PowerPC)|(Macintosh)',
            'QNX'                 => 'QNX',
            'BeOS'                => 'BeOS',
            'OS/2'                => 'OS/2',
            'Search Bot'          => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves/Teoma)|(ia_archiver)',
        );

        foreach ($OSList as $CurrOS => $Match) {
            if (preg_match("/" . $Match . "/i", $this->user_agent())) {
                break;
            }
        }
        $this->user_os = $CurrOS;

        return $this->user_os;
    }

    /**
     * Fetch an item from the REQUEST array
     *
     * @param	string
     * @param	bool
     *
     * @return string
     */
    public function request($index = '', $xss_clean = false)
    {
        return $this->_fetch_from_array($_REQUEST, $index, $xss_clean);
    }
}
