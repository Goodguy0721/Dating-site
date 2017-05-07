<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 *
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 *
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * MS SQL Utility Class
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_mssql_utility extends CI_DB_utility
{
    /**
     * List databases
     *
     * @return bool
     */
    public function _list_databases()
    {
        return "EXEC sp_helpdb"; // Can also be: EXEC sp_databases
    }

    // --------------------------------------------------------------------

    /**
     * Optimize table query
     *
     * Generates a platform-specific query so that a table can be optimized
     *
     * @param	string	the table name
     *
     * @return object
     */
    public function _optimize_table($table)
    {
        return false; // Is this supported in MS SQL?
    }

    // --------------------------------------------------------------------

    /**
     * Repair table query
     *
     * Generates a platform-specific query so that a table can be repaired
     *
     * @param	string	the table name
     *
     * @return object
     */
    public function _repair_table($table)
    {
        return false; // Is this supported in MS SQL?
    }

    // --------------------------------------------------------------------

    /**
     * MSSQL Export
     *
     * @param	array	Preferences
     *
     * @return mixed
     */
    public function _backup($params = array())
    {
        // Currently unsupported
        return $this->db->display_error('db_unsuported_feature');
    }

    /**
     * The functions below have been deprecated as of 1.6, and are only here for backwards
     * compatibility.  They now reside in dbforge().  The use of dbutils for database manipulation
     * is STRONGLY discouraged in favour if using dbforge.
     */

    /**
     * Create database
     *
     * @param	string	the database name
     *
     * @return bool
     */
    public function _create_database($name)
    {
        return "CREATE DATABASE " . $name;
    }

    // --------------------------------------------------------------------

    /**
     * Drop database
     *
     * @param	string	the database name
     *
     * @return bool
     */
    public function _drop_database($name)
    {
        return "DROP DATABASE " . $name;
    }
}

/* End of file mssql_utility.php */
/* Location: ./system/database/drivers/mssql/mssql_utility.php */
