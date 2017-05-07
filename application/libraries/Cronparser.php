<?php

/**
 * Cronparser library
 * This class is based on the concept in the CronParser class written by Mick Sear http://www.ecreate.co.uk
 * The following functions are direct copies from or based on the original class:
 * getLastRan(), getDebug(), debug(), expand_ranges()
 *
 * Who can use this class?
 * This class is idea for people who can not use the traditional Unix cron through shell.
 * One way of using is embedding the calling script in a web page which is often visited.
 * The script will work out the last due time, by comparing with run log timestamp. The scrip
 * will envoke any scripts needed to run, be it deleting older table records, or updating prices.
 * It can parse the same cron string used by Unix.
 *
 *
 *	Usage example:

 $cron_str0 = "0,12,30-51 3,21-23,10 1-25 9-12,1 0,3-7";
 require_once("CronParser.php");
 $cron = new CronParser();
 $cron->calcLastRan($cron_str0);
 // $cron->getLastRanUnix() returns an Unix timestamp
 echo "Cron '$cron_str0' last due at: " . date('r', $cron->getLastRanUnix()) . "<p>";
 // $cron->getLastRan() returns last due time in an array
 print_r($cron->getLastRan());
 echo "Debug:<br>" . nl2br($cron->getDebug());
 *
 * @package PG_Core
 * @subpackage application
 *
 * @category	libraries
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Mikhail Makeev
 *
 * @version $Revision: 2 $ $Date: 2009-12-02 15:07:07 +0300 (Ср, 02 дек 2009) $ $Author: irina $
 **/
class CronParser
{
    /**
     * exploded String like 0 1 * * *
     *
     * @var array
     */
    public $bits = array();

    /**
     * Array of cron-style entries for time()
     *
     * @var array
     */
    public $now = array();

    /**
     * Timestamp of last ran time
     *
     * @var array
     */
    public $lastRan;

    public $taken;

    public $debug;

    public $year;

    public $month;

    public $day;

    public $hour;

    public $minute;

    /**
     * Turn on if error
     *
     * @var boolean
     */
    public $is_error = false;

    /**
     * minutes array based on cron string
     *
     * @var array
     */
    public $minutes_arr = array();

    /**
     * hours array based on cron string
     *
     * @var array
     */
    public $hours_arr = array();

    /**
     * months array based on cron string
     *
     * @var array
     */
    public $months_arr = array();

    public function __construct($cronString = '')
    {
        if (!empty($cronString)) {
            $this->calcLastRan($cronString);
        }
    }

    /**
     * Get the values for now in a format we can use
     *
     * @return array
     */
    public function getLastRan()
    {
        if ($this->is_error) {
            return false;
        }

        return explode(",", strftime("%M,%H,%d,%m,%w,%Y", $this->lastRan));
    }

    public function getLastRanUnix()
    {
        return $this->lastRan;
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function debug($str)
    {
        if (is_array($str)) {
            $this->debug .= "\nArray: ";
            foreach ($str as $k => $v) {
                $this->debug .= "$k=>$v, ";
            }
        } else {
            $this->debug .= "\n$str";
        }
    }

    /**
     * Assumes that value is not *, and creates an array of valid numbers that
     * the string represents.
     *
     * @return array
     */
    public function expand_ranges($str)
    {
        if (strstr($str,  ",")) {
            $arParts = explode(',', $str);
            foreach ($arParts as $part) {
                if (strstr($part, '-')) {
                    $arRange = explode('-', $part);
                    for ($i = $arRange[0]; $i <= $arRange[1]; ++$i) {
                        $ret[] = $i;
                    }
                } else {
                    $ret[] = $part;
                }
            }
        } elseif (strstr($str,  '-')) {
            $arRange = explode('-', $str);
            if ($arRange[0] == '') {
                $this->is_error = true;
            }
            for ($i = $arRange[0]; $i <= $arRange[1]; ++$i) {
                $ret[] = $i;
            }
        } else {
            $ret[] = $str;
        }
        $ret = array_unique($ret);
        sort($ret);

        return $ret;
    }

    public function expand_every($str, $type = 'min')
    {
        if (preg_match('/\*\/([0-9]+)/i', $str, $match)) {
            $count = intval($match[1]);

            if (!$count) {
                return $str;
            }

            switch ($type) {
                case "min": $round = floor(59 / $count); $start = 0; break;
                case "hour": $round = floor(23 / $count); $start = 0; break;
                case "day": $round = floor(31 / $count); $start = 1; break;
                case "month": $round = floor(12 / $count); $start = 1; break;
                case "wday":$round = floor(6 / $count); $start = 0;break;
            }

            for ($i = $start; $i < $round; ++$i) {
                $arr[] = $i * $count;
            }

            return implode(",", $arr);
        }

        return $str;
    }

    public function daysinmonth($month, $year)
    {
        return date('t', mktime(0, 0, 0, $month, 1, $year));
    }

    /**
     * Calculate the last due time before this moment
     *
     * @param $string cron string
     *
     * @return array/false
     */
    public function calcLastRan($string)
    {
        $tstart = microtime();
        $this->debug = "";
        $this->lastRan = 0;
        $this->year = null;
        $this->month = null;
        $this->day = null;
        $this->hour = null;
        $this->minute = null;
        $this->hours_arr = array();
        $this->minutes_arr = array();
        $this->months_arr = array();

        $string = preg_replace('/[\s]{2,}/', ' ', $string);

        if (preg_match('/[^-,* \\d\/]/', $string) !== 0) {
            $this->debug("Cron String contains invalid character");

            return false;
        }

        $this->debug("<b>Working on cron schedule: $string</b>");
        $this->bits = explode(" ", $string);

        if (count($this->bits) != 5) {
            $this->debug("Cron string is invalid. Too many or too little sections after explode");

            return false;
        }

        $this->bits[0] = $this->expand_every($this->bits[0], "min");
        $this->bits[1] = $this->expand_every($this->bits[1], "hour");
        $this->bits[2] = $this->expand_every($this->bits[2], "day");
        $this->bits[3] = $this->expand_every($this->bits[3], "month");
        $this->bits[4] = $this->expand_every($this->bits[4], "wday");

        //put the current time into an array
        $t = strftime("%M,%H,%d,%m,%w,%Y", time());
        $this->now = explode(",", $t);

        $this->year = $this->now[5];

        $arMonths = $this->_getMonthsArray();

        do {
            $this->month = array_pop($arMonths);
        } while ($this->month > $this->now[3]);

        if ($this->month === null) {
            $this->year = $this->year - 1;
            $this->debug("Not due within this year. So checking the previous year " . $this->year);
            $arMonths = $this->_getMonthsArray();
            $this->_prevMonth($arMonths);
        } elseif ($this->month == $this->now[3]) {
            //now Sep, month = array(7,9,12)
            $this->debug("Cron is due this month, getting days array.");
            $arDays = $this->_getDaysArray($this->month, $this->year);

            do {
                $this->day = array_pop($arDays);
            } while ($this->day > $this->now[2]);

            if ($this->day === null) {
                $this->debug("Smallest day is even greater than today");
                $this->_prevMonth($arMonths);
            } elseif ($this->day == $this->now[2]) {
                $this->debug("Due to run today");
                $arHours = $this->_getHoursArray();

                do {
                    $this->hour = array_pop($arHours);
                } while ($this->hour > $this->now[1]);

                if ($this->hour === null) {
                    // now =2, arHours = array(3,5,7)
                    $this->debug("Not due this hour and some earlier hours, so go for previous day");
                    $this->_prevDay($arDays, $arMonths);
                } elseif ($this->hour < $this->now[1]) {
                    //now =2, arHours = array(1,3,5)
                    $this->minute = $this->_getLastMinute();
                } else {
                    // now =2, arHours = array(1,2,5)
                    $this->debug("Due this hour");
                    $arMinutes = $this->_getMinutesArray();
                    do {
                        $this->minute = array_pop($arMinutes);
                    } while ($this->minute > $this->now[0]);

                    if ($this->minute === null) {
                        $this->debug("Not due this minute, so go for previous hour.");
                        $this->_prevHour($arHours, $arDays, $arMonths);
                    } else {
                        $this->debug("Due this very minute or some earlier minutes before this moment within this hour.");
                    }
                }
            } else {
                $this->debug("Cron was due on " . $this->day . " of this month");
                $this->hour = $this->_getLastHour();
                $this->minute = $this->_getLastMinute();
            }
        } else {
            //now Sep, arrMonths=array(7, 10)
            $this->debug("Cron was due before this month. Previous month is: " . $this->year . '-' . $this->month);
            $this->day = $this->_getLastDay($this->month, $this->year);
            if ($this->day === null) {
                //No scheduled date within this month. So we will try the previous month in the month array
                $this->_prevMonth($arMonths);
            } else {
                $this->hour = $this->_getLastHour();
                $this->minute = $this->_getLastMinute();
            }
        }

        $tend = microtime();
        $this->taken = $tend - $tstart;
        $this->debug("Parsing $string taken " . $this->taken . " seconds");

        //if the last due is beyond 1970
        if ($this->minute === null) {
            $this->debug("Error calculating last due time");

            return false;
        } else {
            $this->debug("LAST DUE: " . $this->hour . ":" . $this->minute . " on " . $this->day . "/" . $this->month . "/" . $this->year);
            $this->lastRan = mktime($this->hour, $this->minute, 0, $this->month, $this->day, $this->year);

            return true;
        }
    }

    /**
     * Get the due time before current month
     *
     * @param array $arMonths months
     *
     * @return void
     */
    public function _prevMonth($arMonths)
    {
        $this->month = array_pop($arMonths);
        if ($this->month === null) {
            $this->year = $this->year - 1;
            if ($this->year <= 1970) {
                $this->debug("Can not calculate last due time. At least not before 1970..");
            } else {
                $this->debug("Have to go for previous year " . $this->year);
                $arMonths = $this->_getMonthsArray();
                $this->_prevMonth($arMonths);
            }
        } else {
            $this->debug("Getting the last day for previous month: " . $this->year . '-' . $this->month);
            $this->day = $this->_getLastDay($this->month, $this->year);

            if ($this->day === null) {
                //no available date schedule in this month
                $this->_prevMonth($arMonths);
            } else {
                $this->hour = $this->_getLastHour();
                $this->minute = $this->_getLastMinute();
            }
        }
    }

    /**
     * Get the due time before current day
     *
     * @param array $arDays   days
     * @param array $arMonths months
     *
     * @return void
     */
    public function _prevDay($arDays, $arMonths)
    {
        $this->debug("Go for the previous day");
        $this->day = array_pop($arDays);
        if ($this->day === null) {
            $this->debug("Have to go for previous month");
            $this->_prevMonth($arMonths);
        } else {
            $this->hour = $this->_getLastHour();
            $this->minute = $this->_getLastMinute();
        }
    }

    /**
     * Get the due time before current hour
     *
     * @param array $arHours  hours
     * @param array $arDays   days
     * @param array $arMonths months
     *
     * @return void
     */
    public function _prevHour($arHours, $arDays, $arMonths)
    {
        $this->debug("Going for previous hour");
        $this->hour = array_pop($arHours);
        if ($this->hour === null) {
            $this->debug("Have to go for previous day");
            $this->_prevDay($arDays, $arMonths);
        } else {
            $this->minute = $this->_getLastMinute();
        }
    }

    /**
     * Not used at the moment
     *
     * @return array
     */
    public function _getLastMonth()
    {
        $months = $this->_getMonthsArray();
        $month = array_pop($months);

        return $month;
    }

    public function _getLastDay($month, $year)
    {
        //put the available days for that month into an array
        $days = $this->_getDaysArray($month, $year);
        $day = array_pop($days);

        return $day;
    }

    public function _getLastHour()
    {
        $hours = $this->_getHoursArray();
        $hour = array_pop($hours);

        return $hour;
    }

    public function _getLastMinute()
    {
        $minutes = $this->_getMinutesArray();
        $minute = array_pop($minutes);

        return $minute;
    }

    /**
     * Remove the out of range array elements. $arr should be sorted
     * already and does not contain duplicates
     *
     * @param array   $arr  array
     * @param integer $low  bottom edge
     * @param integer $high top edge
     *
     * @return array
     */
    public function _sanitize($arr, $low, $high)
    {
        $count = count($arr);
        for ($i = 0; $i <= ($count - 1); ++$i) {
            if ($arr[$i] < $low) {
                $this->debug("Remove out of range element. {$arr[$i]} is outside $low - $high");
                unset($arr[$i]);
            } else {
                break;
            }
        }

        for ($i = ($count - 1); $i >= 0; --$i) {
            if ($arr[$i] > $high) {
                $this->debug("Remove out of range element. {$arr[$i]} is outside $low - $high");
                unset($arr[$i]);
            } else {
                break;
            }
        }

        //re-assign keys
        sort($arr);

        return $arr;
    }

    /**
     * Given a month/year, list all the days within that month fell into the week days list.
     *
     * @param integer $month month
     * @param integer $year  year
     *
     * @return array
     */
    public function _getDaysArray($month, $year = 0)
    {
        if ($year == 0) {
            $year = $this->year;
        }

        $days = array();

        //return everyday of the month if both bit[2] and bit[4] are '*'
        if ($this->bits[2] == '*' and $this->bits[4] == '*') {
            $days = $this->getDays($month, $year);
        } else {
            //create an array for the weekdays
            if ($this->bits[4] == '*') {
                for ($i = 0; $i <= 6; ++$i) {
                    $arWeekdays[] = $i;
                }
            } else {
                $arWeekdays = $this->expand_ranges($this->bits[4]);
                $arWeekdays = $this->_sanitize($arWeekdays, 0, 7);

                //map 7 to 0, both represents Sunday. Array is sorted already!
                if (in_array(7, $arWeekdays)) {
                    if (in_array(0, $arWeekdays)) {
                        array_pop($arWeekdays);
                    } else {
                        $tmp[] = 0;
                        array_pop($arWeekdays);
                        $arWeekdays = array_merge($tmp, $arWeekdays);
                    }
                }
            }
            $this->debug("Array for the weekdays");
            $this->debug($arWeekdays);

            if ($this->bits[2] == '*') {
                $daysmonth = $this->getDays($month, $year);
            } else {
                $daysmonth = $this->expand_ranges($this->bits[2]);
                // so that we do not end up with 31 of Feb
                $daysinmonth = $this->daysinmonth($month, $year);
                $daysmonth = $this->_sanitize($daysmonth, 1, $daysinmonth);
            }

            //Now match these days with weekdays
            foreach ($daysmonth as $day) {
                $wkday = date('w', mktime(0, 0, 0, $month, $day, $year));
                if (in_array($wkday, $arWeekdays)) {
                    $days[] = $day;
                }
            }
        }
        $this->debug("Days array matching weekdays for $year-$month");
        $this->debug($days);

        return $days;
    }

    /**
     * Given a month/year, return an array containing all the days in that month
     *
     * @param integer $month month
     * @param integer $year  year
     *
     * @return array
     */
    public function getDays($month, $year)
    {
        $daysinmonth = $this->daysinmonth($month, $year);
        $this->debug("Number of days in $year-$month : $daysinmonth");
        $days = array();
        for ($i = 1; $i <= $daysinmonth; ++$i) {
            $days[] = $i;
        }

        return $days;
    }

    public function _getHoursArray()
    {
        if (empty($this->hours_arr)) {
            $hours = array();

            if ($this->bits[1] == '*') {
                for ($i = 0; $i <= 23; ++$i) {
                    $hours[] = $i;
                }
            } else {
                $hours = $this->expand_ranges($this->bits[1]);
                $hours = $this->_sanitize($hours, 0, 23);
            }

            $this->debug("Hour array");
            $this->debug($hours);
            $this->hours_arr = $hours;
        }

        return $this->hours_arr;
    }

    public function _getMinutesArray()
    {
        if (empty($this->minutes_arr)) {
            $minutes = array();

            if ($this->bits[0] == '*') {
                for ($i = 0; $i <= 60; ++$i) {
                    $minutes[] = $i;
                }
            } else {
                $minutes = $this->expand_ranges($this->bits[0]);
                $minutes = $this->_sanitize($minutes, 0, 59);
            }
            $this->debug("Minutes array");
            $this->debug($minutes);
            $this->minutes_arr = $minutes;
        }

        return $this->minutes_arr;
    }

    public function _getMonthsArray()
    {
        if (empty($this->months_arr)) {
            $months = array();
            if ($this->bits[3] == '*') {
                for ($i = 1; $i <= 12; ++$i) {
                    $months[] = $i;
                }
            } else {
                $months = $this->expand_ranges($this->bits[3]);
                $months = $this->_sanitize($months, 1, 12);
            }
            $this->debug("Months array");
            $this->debug($months);
            $this->months_arr = $months;
        }

        return $this->months_arr;
    }
}
