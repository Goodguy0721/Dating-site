<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package	 	CodeIgniter
 *
 * @author	  	ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2008, EllisLab, Inc.
 * @license	 	http://codeigniter.com/user_guide/license.html
 *
 * @link		http://codeigniter.com
 * @since	   	Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * oci8 Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package	 	CodeIgniter
 * @subpackage  Drivers
 *
 * @category	Database
 *
 * @author	  	ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/database/
 */

/**
 * oci8 Database Adapter Class
 *
 * This is a modification of the DB_driver class to
 * permit access to oracle databases
 *
 * NOTE: this uses the PHP 4 oci methods
 *
 * @author	  Kelly McArdle
 */
class CI_DB_oci8_driver extends CI_DB
{
    public $dbdriver = 'oci8';

    // The character used for excaping
    public $_escape_char = '"';

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    public $_count_string = "SELECT COUNT(1) AS ";
    public $_random_keyword = ' ASC'; // not currently supported

    // Set "auto commit" by default
    public $_commit = OCI_COMMIT_ON_SUCCESS;

    // need to track statement id and cursor id
    public $stmt_id;
    public $curs_id;

    // if we use a limit, we will add a field that will
    // throw off num_fields later
    public $limit_used;

    /**
     * Non-persistent database connection
     *
     * @return resource
     */
    public function db_connect()
    {
        return @ocilogon($this->username, $this->password, $this->hostname);
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @return resource
     */
    public function db_pconnect()
    {
        return @ociplogon($this->username, $this->password, $this->hostname);
    }

    // --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @return resource
     */
    public function db_select()
    {
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
        return ociserverversion($this->conn_id);
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @param   string  an SQL query
     *
     * @return resource
     */
    public function _execute($sql)
    {
        // oracle must parse the query before it is run. All of the actions with
        // the query are based on the statement id returned by ociparse
        $this->stmt_id = false;
        $this->_set_stmt_id($sql);
        ocisetprefetch($this->stmt_id, 1000);

        return @ociexecute($this->stmt_id, $this->_commit);
    }

    /**
     * Generate a statement ID
     *
     * @param   string  an SQL query
     *
     * @return none
     */
    public function _set_stmt_id($sql)
    {
        if (!is_resource($this->stmt_id)) {
            $this->stmt_id = ociparse($this->conn_id, $this->_prep_query($sql));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @param   string  an SQL query
     *
     * @return string
     */
    public function _prep_query($sql)
    {
        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * getCursor.  Returns a cursor from the datbase
     *
     * @return cursor id
     */
    public function get_cursor()
    {
        $this->curs_id = ocinewcursor($this->conn_id);

        return $this->curs_id;
    }

    // --------------------------------------------------------------------

    /**
     * Stored Procedure.  Executes a stored procedure
     *
     * @param   package	 package stored procedure is in
     * @param   procedure   stored procedure to execute
     * @param   params	  array of parameters
     *
     * @return array
     *
     * params array keys
     *
     * KEY	  OPTIONAL	NOTES
     * name		no		the name of the parameter should be in :<param_name> format
     * value	no		the value of the parameter.  If this is an OUT or IN OUT parameter,
     *					this should be a reference to a variable
     * type		yes		the type of the parameter
     * length	yes		the max size of the parameter
     */
    public function stored_procedure($package, $procedure, $params)
    {
        if ($package == '' or $procedure == '' or !is_array($params)) {
            if ($this->db_debug) {
                log_message('error', 'Invalid query: ' . $package . '.' . $procedure);

                return $this->display_error('db_invalid_query');
            }

            return false;
        }

        // build the query string
        $sql = "begin $package.$procedure(";

        $have_cursor = false;
        foreach ($params as $param) {
            $sql .= $param['name'] . ",";

            if (array_key_exists('type', $param) && ($param['type'] == OCI_B_CURSOR)) {
                $have_cursor = true;
            }
        }
        $sql = trim($sql, ",") . "); end;";

        $this->stmt_id = false;
        $this->_set_stmt_id($sql);
        $this->_bind_params($params);
        $this->query($sql, false, $have_cursor);
    }

    // --------------------------------------------------------------------

    /**
     * Bind parameters
     *
     * @return none
     */
    public function _bind_params($params)
    {
        if (!is_array($params) or !is_resource($this->stmt_id)) {
            return;
        }

        foreach ($params as $param) {
            foreach (array('name', 'value', 'type', 'length') as $val) {
                if (!isset($param[$val])) {
                    $param[$val] = '';
                }
            }

            ocibindbyname($this->stmt_id, $param['name'], $param['value'], $param['length'], $param['type']);
        }
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

        $this->_commit = OCI_DEFAULT;

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

        $ret = OCIcommit($this->conn_id);
        $this->_commit = OCI_COMMIT_ON_SUCCESS;

        return $ret;
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

        $ret = OCIrollback($this->conn_id);
        $this->_commit = OCI_COMMIT_ON_SUCCESS;

        return $ret;
    }

    // --------------------------------------------------------------------

    /**
     * Escape String
     *
     * @param   string
     *
     * @return string
     */
    public function escape_str($str)
    {
        // Access the CI object
        $CI = &get_instance();

        return $CI->_remove_invisible_characters($str);
    }

    // --------------------------------------------------------------------

    /**
     * Affected Rows
     *
     * @return integer
     */
    public function affected_rows()
    {
        return @ocirowcount($this->stmt_id);
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @return integer
     */
    public function insert_id()
    {
        // not supported in oracle
        return $this->display_error('db_unsupported_function');
    }

    // --------------------------------------------------------------------

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @param   string
     *
     * @return string
     */
    public function count_all($table = '')
    {
        if ($table == '') {
            return '0';
        }

        $query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, true, null, false));

        if ($query == false) {
            return 0;
        }

        $row = $query->row();

        return $row->NUMROWS;
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
        $sql = "SELECT TABLE_NAME FROM ALL_TABLES";

        if ($prefix_limit !== false and $this->dbprefix != '') {
            $sql .= " WHERE TABLE_NAME LIKE '" . $this->dbprefix . "%'";
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Show column query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @param   string  the table name
     *
     * @return string
     */
    public function _list_columns($table = '')
    {
        return "SELECT COLUMN_NAME FROM all_tab_columns WHERE table_name = '$table'";
    }

    // --------------------------------------------------------------------

    /**
     * Field data query
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @param   string  the table name
     *
     * @return object
     */
    public function _field_data($table)
    {
        return "SELECT * FROM " . $table . " where rownum = 1";
    }

    // --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @return string
     */
    public function _error_message()
    {
        $error = ocierror($this->conn_id);

        return $error['message'];
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @return integer
     */
    public function _error_number()
    {
        $error = ocierror($this->conn_id);

        return $error['code'];
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
     * @param   string  the table name
     * @param   array   the insert keys
     * @param   array   the insert values
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
     * @param       string  the table name
     * @param       array   the insert keys
     * @param       array   the insert values
     *
     * @return string
     */
    protected function _insert_batch($table, $keys, $values)
    {
        $keys = implode(', ', $keys);
        $sql = "INSERT ALL\n";

        for ($i = 0, $c = count($values); $i < $c; ++$i) {
            $sql .= '	INTO ' . $table . ' (' . $keys . ') VALUES ' . $values[$i] . "\n";
        }

        $sql .= 'SELECT * FROM dual';

        return $sql;
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
        return "TRUNCATE TABLE " . $table;
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
     * @param   string  the sql query string
     * @param   integer the number of rows to limit the query to
     * @param   integer the offset value
     *
     * @return string
     */
    public function _limit($sql, $limit, $offset)
    {
        $limit = $offset + $limit;
        $newsql = "SELECT * FROM (select inner_query.*, rownum rnum FROM ($sql) inner_query WHERE rownum < $limit)";

        if ($offset != 0) {
            $newsql .= " WHERE rnum >= $offset";
        }

        // remember that we used limits
        $this->limit_used = true;

        return $newsql;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @param   resource
     *
     * @return void
     */
    public function _close($conn_id)
    {
        @ocilogoff($conn_id);
    }
}

/* End of file oci8_driver.php */
/* Location: ./system/database/drivers/oci8/oci8_driver.php */
