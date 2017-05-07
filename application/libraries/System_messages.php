<?php

/**
 * System messages collector
 *
 * @package PG_Core
 * @subpackage application
 *
 * @category	libraries
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Irina Lebedeva <irina@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2009-12-02 15:07:07 +0300 (Ср, 02 дек 2009) $ $Author: irina $
 **/
class System_messages
{
    public $CI;

    public $not_session_data = array();

    /**
     * Constructor
     *
     * @return System_messages
     */
    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * Check if system errors were occured
     *
     * @return boolean
     */
    public function is_message_exists($message_type)
    {
        if (isset($_SESSION["messages"][$message_type]) &&
            !empty($_SESSION["messages"][$message_type])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Clean all system messages
     */
    public function clean_messages($message_type = '')
    {
        if ($message_type != '') {
            if (isset($_SESSION["messages"][$message_type])) {
                unset($_SESSION["messages"][$message_type]);
            }
        } else {
            unset($_SESSION["messages"]);
        }
    }

    public function add_message($message_type, $message)
    {
        if (is_array($message)) {
            foreach ($message as $name => $str) {
                $_SESSION["messages"][$message_type][] = array(
                    'name'        => $name,
                    'text'        => $str,
                );
            }
        } else {
            $_SESSION["messages"][$message_type][] = array(
                'text' => $message,
            );
        }
        if ($this->CI->router->is_api_class) {
            $this->CI->set_api_content('system_messages', $_SESSION["messages"]);
        }
    }

    public function get_messages($message_type)
    {
        $return = array();

        if (isset($_SESSION["messages"][$message_type]) && !empty($_SESSION["messages"][$message_type])) {
            foreach ($_SESSION["messages"][$message_type] as $str) {
                $return[] = $str;
            }
            $this->clean_messages($message_type);
        }

        return $return;
    }

    public function set_data($gid, $value)
    {
        $this->not_session_data[$gid] = $value;
    }

    public function set_data_array($gid, $id, $value)
    {
        $this->not_session_data[$gid][$id] = $value;
    }

    public function get_data($gid)
    {
        if (isset($this->not_session_data[$gid])) {
            return $this->not_session_data[$gid];
        } else {
            return false;
        }
    }

    public function addMessage($message_type, $message)
    {
        $this->add_message($message_type, $message);
    }
}
