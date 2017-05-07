<?php

/**
 * Count scripts run-time
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
class Script_execution_time
{
    public $total_time;
    public $segment_time;
    public $round_precision = 4;

    public function __construct()
    {
        $this->total_time = $this->microtime_float();
    }

    public function get_total_time()
    {
        $prev_time = $this->total_time;
        $this->total_time = $this->microtime_float();

        return round(($this->total_time - $prev_time), $this->round_precision);
    }

    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
    }

    public function start_current_segment()
    {
        $this->segment_time = $this->microtime_float();
    }

    /**
     * Get script execution time for the last time segment
     *
     * @return integer (seconds)
     */
    public function get_segment_time()
    {
        if (!$this->segment_time) {
            $CI = &get_instance();
            $CI->system_messages->add_error('use_start_current_segment_before_this_method');

            return false;
        } else {
            $prev_time = $this->segment_time;
            $this->segment_time = $this->microtime_float();

            return round(($this->segment_time - $prev_time), $this->round_precision);
        }
    }
}
