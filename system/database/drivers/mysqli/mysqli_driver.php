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
 * MySQLi Database Adapter Class - MySQLi only works with PHP 5
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
class CI_DB_mysqli_driver extends CI_DB
{
    public $dbdriver = 'mysqli';

    // The character used for escaping
    public $_escape_char = '`';

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    public $_count_string = "SELECT COUNT(*) AS ";
    public $_random_keyword = ' RAND()'; // database specific random keyword

    /**
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     */
    public $delete_hack = true;

    // --------------------------------------------------------------------

    /**
     * Non-persistent database connection
     *
     * @return resource
     */
    public function db_connect()
    {
        $conn_id = mysqli_connect($this->hostname, $this->username, $this->password, $this->database, (int) $this->port);
        @mysqli_query($conn_id, "SET SESSION sql_mode=''");

        return $conn_id;
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @return resource
     */
    public function db_pconnect()
    {
        return $this->db_connect();
    }

    // --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @return resource
     */
    public function db_select()
    {
        return @mysqli_select_db($this->conn_id, $this->database);
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
    public function _db_set_charset($charset, $collation)
    {
        return @mysqli_query($this->conn_id, "SET NAMES '" . $this->escape_str($charset) . "' COLLATE '" . $this->escape_str($collation) . "'");
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
        $result = @mysqli_query($this->conn_id, $sql);

        return $result;
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
        // "DELETE FROM TABLE" returns 0 affected rows This hack modifies
        // the query so that it returns the number of affected rows
        if ($this->delete_hack === true) {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
                $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
            }
        }

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

        $this->simple_query('SET AUTOCOMMIT=0');
        $this->simple_query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
        return true;
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

        $this->simple_query('COMMIT');
        $this->simple_query('SET AUTOCOMMIT=1');

        return true;
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

        $this->simple_query('ROLLBACK');
        $this->simple_query('SET AUTOCOMMIT=1');

        return true;
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
        if (function_exists('mysqli_real_escape_string') and is_object($this->conn_id)) {
            return mysqli_real_escape_string($this->conn_id, $str);
        } elseif (function_exists('mysql_escape_string')) {
            return mysql_escape_string($str);
        } else {
            return addslashes($str);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Affected Rows
     *
     * @return integer
     */
    public function affected_rows()
    {
        return @mysqli_affected_rows($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @return integer
     */
    public function insert_id()
    {
        return @mysqli_insert_id($this->conn_id);
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
     * List table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @param	boolean
     *
     * @return string
     */
    public function _list_tables($prefix_limit = false)
    {
        $sql = "SHOW TABLES FROM " . $this->_escape_char . $this->database . $this->_escape_char;

        if ($prefix_limit !== false and $this->dbprefix != '') {
            $sql .= " LIKE '" . $this->dbprefix . "%'";
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
        return "SHOW COLUMNS FROM " . $table;
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
        return mysqli_error($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @return integer
     */
    public function _error_number()
    {
        return mysqli_errno($this->conn_id);
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

        return '(' . implode(', ', $tables) . ')';
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
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     *
     * @return string
     */
    public function _insert_batch($table, $keys, $values, $ignore = false)
    {
        $insert_str = $ignore ? 'INSERT IGNORE' : 'INSERT';

        return "{$insert_str} INTO " . $table . " (" . implode(', ', $keys) . ") VALUES " . implode(', ', $values);
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
        @mysqli_close($conn_id);
    }
}

/* End of file mysqli_driver.php */
/* Location: ./system/database/drivers/mysqli/mysqli_driver.php */
