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
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * PDO Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_pdo_driver extends CI_DB
{

    public $dbdriver = 'pdo';
    // The character used for escaping
    public $_escape_char = '`';

    /**
     * Whether to use the pdo "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     */
    public $delete_hack = false;

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    public $_count_string = 'SELECT COUNT(*) AS ';
    public $_random_keyword = ' RAND()'; // database specific random keyword

    /**
     * Non-persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */

    public function db_connect()
    {
        if ($this->port != '') {
            $this->hostname .= ':' . $this->port;
        }
        try {
            $conn_id = new PDO("mysql:host=" . $this->hostname . ";dbname=" . $this->database . "", $this->username,
                               $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;'));
            log_message('debug', "connecting mysql:host=" . $this->hostname . ";dbname=" . $this->database);
        } catch (PDOException $e) {
            log_message('debug', 'merde');
            log_message('error', $e->getMessage());
            if ($this->db_debug) {
                $this->display_error($e->getMessage(), '', TRUE);
            }
            $conn_id = null;
        }
        if ($conn_id) {
            log_message('debug', print_r($conn_id, true));
            log_message('debug', 'connection ok');
            $conn_id->query("SET SESSION sql_mode=''");
        }
        return $conn_id;
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */
    public function db_pconnect()
    {
        if ($this->port != '') {
            $this->hostname .= ':' . $this->port;
        }

        try {
            $conn_id = new PDO(
                    "mysql:host=" . $this->hostname . ";dbname=" . $this->database . "", 
                    $this->username,
                    $this->password,
                    array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;'));
        } catch (PDOException $e) {
            log_message('error', $e->getMessage());
            if ($this->db_debug) {
                $this->display_error($e->getMessage(), '', TRUE);
            }
        }
        $conn_id->query("SET SESSION sql_mode=''");
        return $conn_id;
    }

    // --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @access	private called by the base class
     * @return	resource
     */
    public function db_select()
    {
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	resource
     */
    public function _db_set_charset($charset, $collation)
    {
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Version number query string
     *
     * @access	public
     * @return	string
     */
    public function _version()
    {
        return $this->conn_id->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access	public
     * @param	resource
     * @return	void
     */
    public function destroy($conn_id)
    {
        $conn_id = null;
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @access	private called by the base class
     * @param	string	an SQL query
     * @return	resource
     */
    public function _execute($sql)
    {
        //$sql = $this->_prep_query($sql);
        log_message('debug', 'SQL : ' . $sql);
        $query = $this->conn_id->query($sql);
        if (is_object($query)) {
            $this->affect_rows = $query->rowCount();
        }
        return $query;
    }

    // --------------------------------------------------------------------

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @access	private called by execute()
     * @param	string	an SQL query
     * @return	string
     */
    /* public function _prep_query($sql) {
      // "DELETE FROM TABLE" returns 0 affected rows This hack modifies
      // the query so that it returns the number of affected rows
      if ($this->delete_hack === TRUE) {
      if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
      $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
      }
      }
      return $sql;
      } */

    // --------------------------------------------------------------------

    /**
     * Begin Transaction
     *
     * @access	public
     * @return	bool
     */
    public function trans_begin($test_mode = FALSE)
    {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        // Reset the transaction failure flag.
        // If the $test_mode flag is set to TRUE transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;

        $this->simple_query('SET AUTOCOMMIT=0');
        $this->simple_query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Commit Transaction
     *
     * @access	public
     * @return	bool
     */
    public function trans_commit()
    {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $this->simple_query('COMMIT');
        $this->simple_query('SET AUTOCOMMIT=1');
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Rollback Transaction
     *
     * @access	public
     * @return	bool
     */
    public function trans_rollback()
    {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $this->simple_query('ROLLBACK');
        $this->simple_query('SET AUTOCOMMIT=1');
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Escape String
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public function escape_str($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escape_str($val);
            }

            return $str;
        }

        if (get_magic_quotes_gpc()) {
            $str = stripslashes($str);
        }
        return $this->conn_id->quote($str);
    }

    public function escape($str)
    {
        switch (gettype($str)) {
            case 'string' : $str = $this->escape_str($str);
                break;
            case 'boolean' : $str = ($str === FALSE) ? 0 : 1;
                break;
            default : $str = ($str === NULL) ? 'NULL' : $str;
                break;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Affected Rows
     *
     * @access	public
     * @return	integer
     */
    public function affected_rows()
    {
        return $this->affect_rows;
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @access	public
     * @return	integer
     */
    public function insert_id()
    {
        return $this->conn_id->lastInsertId();
    }

    // --------------------------------------------------------------------

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public function count_all($table = '')
    {
        if ($table == '') {
            return '0';
        }

        $query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table,
                                                                                                                                     TRUE,
                                                                                                                                     NULL,
                                                                                                                                     FALSE));

        if ($query->num_rows() == 0) {
            return '0';
        }

        $row = $query->row();
        return (int) $row->numrows;
    }

    // --------------------------------------------------------------------

    /**
     * List table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @access	private
     * @param	boolean
     * @return	string
     */
    public function _list_tables($prefix_limit = FALSE)
    {
        $sql = "SHOW TABLES FROM " . $this->_escape_char . $this->database . $this->_escape_char;

        if ($prefix_limit !== FALSE AND $this->dbprefix != '') {
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
     * @access	public
     * @param	string	the table name
     * @return	string
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
     * @access	public
     * @param	string	the table name
     * @return	object
     */
    public function _field_data($table)
    {
        return "SELECT * FROM " . $table . " LIMIT 1";
    }

    //	public function _field_data($table)
    //	{
    //		$sql = "SELECT * FROM ".$this->escape_table($table)." LIMIT 1";
    //		$query = $this->query($sql);
    //		return $query->field_data();
    //	}
    // --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @access	private
     * @return	string
     */
    public function _error_message()
    {
        $infos = $this->conn_id->errorInfo();
        return $infos[2];
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access	private
     * @return	integer
     */
    public function _error_number()
    {
        $infos = $this->conn_id->errorInfo();
        return $infos[1];
    }

    // --------------------------------------------------------------------

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     *
     * @access	private
     * @param	string
     * @return	string
     */
    public function _escape_identifiers($item)
    {
        if ($this->_escape_char == '') {
            return $item;
        }

        if (strpos($item, '.') !== FALSE) {
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
     * @access	public
     * @param	type
     * @return	type
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
     * @access	public
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     * @return	string
     */
    public function _insert($table, $keys, $values)
    {
        $sql = (!$this->ar_ignore[$this->ar_identifier]) ? 'INSERT' : 'INSERT IGNORE';
        return $sql . " INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

    // --------------------------------------------------------------------

    /**
     * Insert_batch statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access  public
     * @param   string  the table name
     * @param   array   the insert keys
     * @param   array   the insert values
     * @return  string
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
     * @access	public
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @param	array	the orderby clause
     * @param	array	the limit clause
     * @return	string
     */
    public function _update($table, $values, $where, $orderby = array(), $limit = FALSE, $join = array())
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

        $sql .= ($where != '' AND count($where) >= 1) ? " WHERE " . implode(" ", $where) : '';

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
     * @access	public
     * @param	string	the table name
     * @return	string
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
     * @access	public
     * @param	string	the table name
     * @param	array	the where clause
     * @param	string	the limit clause
     * @return	string
     */
    public function _delete($table, $where = array(), $like = array(), $limit = FALSE)
    {
        $conditions = '';

        if (count($where) > 0 OR count($like) > 0) {
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
     * @access	public
     * @param	string	the sql query string
     * @param	integer	the number of rows to limit the query to
     * @param	integer	the offset value
     * @return	string
     */
    public function _limit($sql, $limit, $offset)
    {
        if ($offset == 0) {
            $offset = '';
        } else {
            $offset .= ", ";
        }

        return $sql . "LIMIT " . $offset . $limit;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access	public
     * @param	resource
     * @return	void
     */
    public function _close($conn_id)
    {
        // Do nothing since PDO don't have close
    }

}

/* End of file pdo_driver.php */
/* Location: ./system/database/drivers/pdo/pdo_driver.php */