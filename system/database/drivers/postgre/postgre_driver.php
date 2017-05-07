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
 * Postgre Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 *
 * @category	Database
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_postgre_driver extends CI_DB
{
    public $dbdriver = 'postgre';

    public $_escape_char = '"';

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    public $_count_string = "SELECT COUNT(*) AS ";
    public $_random_keyword = ' RANDOM()'; // database specific random keyword

    /**
     * Connection String
     *
     * @return string
     */
    public function _connect_string()
    {
        $components = array(
                                'hostname'    => 'host',
                                'port'        => 'port',
                                'database'    => 'dbname',
                                'username'    => 'user',
                                'password'    => 'password',
                            );

        $connect_string = "";
        foreach ($components as $key => $val) {
            if (isset($this->{$key}) && $this->{$key} != '') {
                $connect_string .= " $val=" . $this->{$key};
            }
        }

        return trim($connect_string);
    }

    // --------------------------------------------------------------------

    /**
     * Non-persistent database connection
     *
     * @return resource
     */
    public function db_connect()
    {
        return @pg_connect($this->_connect_string());
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @return resource
     */
    public function db_pconnect()
    {
        return @pg_pconnect($this->_connect_string());
    }

    // --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @return resource
     */
    public function db_select()
    {
        // Not needed for Postgre so we'll return TRUE
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @param	string
     * @param	string
     *
     * @return resource
     */
    public function db_set_charset($charset, $collation)
    {
        // @todo - add support if needed
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Version number query string
     *
     * @return string
     */
    public function _version()
    {
        return "SELECT version() AS ver";
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @param	string	an SQL query
     *
     * @return resource
     */
    public function _execute($sql)
    {
        $sql = $this->_prep_query($sql);

        return @pg_query($this->conn_id, $sql);
    }

    // --------------------------------------------------------------------

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @param	string	an SQL query
     *
     * @return string
     */
    public function _prep_query($sql)
    {
        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Begin Transaction
     *
     * @return bool
     */
    public function trans_begin($test_mode = false)
    {
        if (!$this->trans_enabled) {
            return true;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return true;
        }

        // Reset the transaction failure flag.
        // If the $test_mode flag is set to TRUE transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_trans_failure = ($test_mode === true) ? true : false;

        return @pg_exec($this->conn_id, "begin");
    }

    // --------------------------------------------------------------------

    /**
     * Commit Transaction
     *
     * @return bool
     */
    public function trans_commit()
    {
        if (!$this->trans_enabled) {
            return true;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return true;
        }

        return @pg_exec($this->conn_id, "commit");
    }

    // --------------------------------------------------------------------

    /**
     * Rollback Transaction
     *
     * @return bool
     */
    public function trans_rollback()
    {
        if (!$this->trans_enabled) {
            return true;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return true;
        }

        return @pg_exec($this->conn_id, "rollback");
    }

    // --------------------------------------------------------------------

    /**
     * Escape String
     *
     * @param	string
     *
     * @return string
     */
    public function escape_str($str)
    {
        return pg_escape_string($str);
    }

    // --------------------------------------------------------------------

    /**
     * Affected Rows
     *
     * @return integer
     */
    public function affected_rows()
    {
        return @pg_affected_rows($this->result_id);
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @return integer
     */
    public function insert_id()
    {
        $v = $this->_version();
        $v = $v['server'];

        $table    = func_num_args() > 0 ? func_get_arg(0) : null;
        $column    = func_num_args() > 1 ? func_get_arg(1) : null;

        if ($table == null && $v >= '8.1') {
            $sql = 'SELECT LASTVAL() as ins_id';
        } elseif ($table != null && $column != null && $v >= '8.0') {
            $sql = sprintf("SELECT pg_get_serial_sequence('%s','%s') as seq", $table, $column);
            $query = $this->query($sql);
            $row = $query->row();
            $sql = sprintf("SELECT CURRVAL('%s') as ins_id", $row->seq);
        } elseif ($table != null) {
            // seq_name passed in table parameter
            $sql = sprintf("SELECT CURRVAL('%s') as ins_id", $table);
        } else {
            return pg_last_oid($this->result_id);
        }
        $query = $this->query($sql);
        $row = $query->row();

        return $row->ins_id;
    }

    // --------------------------------------------------------------------

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @param	string
     *
     * @return string
     */
    public function count_all($table = '')
    {
        if ($table == '') {
            return '0';
        }

        $query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, true, null, false));

        if ($query->num_rows() == 0) {
            return '0';
        }

        $row = $query->row();

        return $row->numrows;
    }

    // --------------------------------------------------------------------

    /**
     * Show table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @param	boolean
     *
     * @return string
     */
    public function _list_tables($prefix_limit = false)
    {
        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";

        if ($prefix_limit !== false and $this->dbprefix != '') {
            $sql .= " AND table_name LIKE '" . $this->dbprefix . "%'";
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Show column query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @param	string	the table name
     *
     * @return string
     */
    public function _list_columns($table = '')
    {
        return "SELECT column_name FROM information_schema.columns WHERE table_name ='" . $table . "'";
    }

    // --------------------------------------------------------------------

    /**
     * Field data query
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @param	string	the table name
     *
     * @return object
     */
    public function _field_data($table)
    {
        return "SELECT * FROM " . $table . " LIMIT 1";
    }

    // --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @return string
     */
    public function _error_message()
    {
        return pg_last_error($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @return integer
     */
    public function _error_number()
    {
        return '';
    }

    // --------------------------------------------------------------------

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     *
     * @param	string
     *
     * @return string
     */
    public function _escape_identifiers($item)
    {
        if ($this->_escape_char == '') {
            return $item;
        }

        if (strpos($item, '.') !== false) {
            $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.' . $this->_escape_char, $item) . $this->_escape_char;
        } else {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }

        // remove duplicates if the user already included the escape
        return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
    }

    // --------------------------------------------------------------------

    /**
     * From Tables
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @param	type
     *
     * @return type
     */
    public function _from_tables($tables)
    {
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        return implode(', ', $tables);
    }

    // --------------------------------------------------------------------

    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     *
     * @return string
     */
    public function _insert($table, $keys, $values)
    {
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

    // --------------------------------------------------------------------

    /**
     * Insert_batch statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @param   string  the table name
     * @param   array   the insert keys
     * @param   array   the insert values
     *
     * @return string
     */
    public function _insert_batch($table, $keys, $values)
    {
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES " . implode(', ', $values);
    }

    // --------------------------------------------------------------------

    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @param	array	the orderby clause
     * @param	array	the limit clause
     *
     * @return string
     */
    public function _update($table, $values, $where, $orderby = array(), $limit = false, $join = array())
    {
        foreach ($values as $key => $val) {
            $valstr[] = $key . " = " . $val;
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        $orderby = (count($orderby) >= 1) ? ' ORDER BY ' . implode(", ", $orderby) : '';

        $sql = "UPDATE " . $table . " ";

        if (!empty($join)) {
            foreach ($join as $value) {
                $sql .= $value . " ";
            }
        }

        $sql .= " SET " . implode(', ', $valstr);

        $sql .= ($where != '' and count($where) >= 1) ? " WHERE " . implode(" ", $where) : '';

        $sql .= $orderby . $limit;

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Truncate statement
     *
     * Generates a platform-specific truncate string from the supplied data
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @param	string	the table name
     *
     * @return string
     */
    public function _truncate($table)
    {
        return "TRUNCATE " . $table;
    }

    // --------------------------------------------------------------------

    /**
     * Delete statement
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @param	string	the table name
     * @param	array	the where clause
     * @param	string	the limit clause
     *
     * @return string
     */
    public function _delete($table, $where = array(), $like = array(), $limit = false)
    {
        $conditions = '';

        if (count($where) > 0 or count($like) > 0) {
            $conditions = "\nWHERE ";
            $conditions .= implode("\n", $this->ar_where[$this->ar_identifier]);

            if (count($where) > 0 && count($like) > 0) {
                $conditions .= " AND ";
            }
            $conditions .= implode("\n", $like);
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        return "DELETE FROM " . $table . $conditions . $limit;
    }

    // --------------------------------------------------------------------
    /**
     * Limit string
     *
     * Generates a platform-specific LIMIT clause
     *
     * @param	string	the sql query string
     * @param	integer	the number of rows to limit the query to
     * @param	integer	the offset value
     *
     * @return string
     */
    public function _limit($sql, $limit, $offset)
    {
        $sql .= "LIMIT " . $limit;

        if ($offset > 0) {
            $sql .= " OFFSET " . $offset;
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @param	resource
     *
     * @return void
     */
    public function _close($conn_id)
    {
        @pg_close($conn_id);
    }
}

/* End of file postgre_driver.php */
/* Location: ./system/database/drivers/postgre/postgre_driver.php */
