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
 * Active Record Class (with parallel queries)
 *
 * This is the platform-independent base Active Record implementation class.
 *
 * Changes by Irina Lebedeva on 2009/04/29 (author of the idea - Christophe Gragnic) are:
 * 		vars now arrays of old vars
 * 		added var $ar_identifier and its setter
 * 		everywhere you had $this->ar_somevar, you now have
 * 		$this->ar_somevar[$this->ar_identifier]
 *
 * Those changes allow us to use Active Record parallel queries.
 * The "identifier" is a simple string (default:'default').
 * Backward compatible, set_identifier('some_identifier') is optional.
 *
 * Sample code:
 * $this->db->set_identifier('my_first_query');
 * $this->db->from('blabla');
 * $this->db->where('blibli');
 *
 * $this->db->set_identifier('my_second_query');
 * $this->db->from('tatata');
 * $this->db->where('tititi');
 *
 * $this->db->set_identifier('my_first_query');
 * $first_query = $this->db->get();
 *
 * $this->db->set_identifier('my_second_query');
 * $second_query = $this->db->get();
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
class CI_DB_active_record extends CI_DB_driver
{
    public $ar_identifier = 'default';

    public $ar_select            = array('default' => array());
    public $ar_distinct            = array('default' => false);
    public $ar_from                = array('default' => array());
    public $ar_join                = array('default' => array());
    public $ar_where            = array('default' => array());
    public $ar_like                = array('default' => array());
    public $ar_groupby            = array('default' => array());
    public $ar_having            = array('default' => array());
    public $ar_keys                = array('default' => array());
    public $ar_limit            = array('default' => false);
    public $ar_offset            = array('default' => false);
    public $ar_order            = array('default' => false);
    public $ar_orderby            = array('default' => array());
    public $ar_set                = array('default' => array());
    public $ar_wherein            = array('default' => array());
    public $ar_aliased_tables    = array('default' => array());
    public $ar_store_array        = array('default' => array());
    public $ar_ignore            = array('default' => false);

    // Active Record Caching variables
    public $ar_caching            = array('default' => false);
    public $ar_cache_exists        = array('default' => array());
    public $ar_cache_select        = array('default' => array());
    public $ar_cache_from        = array('default' => array());
    public $ar_cache_join        = array('default' => array());
    public $ar_cache_where        = array('default' => array());
    public $ar_cache_like        = array('default' => array());
    public $ar_cache_groupby    = array('default' => array());
    public $ar_cache_having        = array('default' => array());
    public $ar_cache_orderby    = array('default' => array());
    public $ar_cache_set        = array('default' => array());

    // --------------------------------------------------------------------

    public function set_identifier($identifier = 'default')
    {
        if (!is_string($identifier)) {
            $identifier = 'default';
        }

        $this->ar_identifier = $identifier;

        // we have to init the values only if it is a new key
        // let's check on the first array
        if (!array_key_exists($identifier, $this->ar_select)) {
            $this->ar_select[$identifier]            = array();
            $this->ar_distinct[$identifier]            = false;
            $this->ar_from[$identifier]                = array();
            $this->ar_join[$identifier]                = array();
            $this->ar_where[$identifier]            = array();
            $this->ar_like[$identifier]                = array();
            $this->ar_groupby[$identifier]            = array();
            $this->ar_having[$identifier]            = array();
            $this->ar_limit[$identifier]            = false;
            $this->ar_offset[$identifier]            = false;
            $this->ar_order[$identifier]            = false;
            $this->ar_orderby[$identifier]            = array();
            $this->ar_set[$identifier]                = array();
            $this->ar_wherein[$identifier]            = array();
            $this->ar_aliased_tables[$identifier]    = array();
            $this->ar_store_array[$identifier]        = array();
            $this->ar_ignore[$identifier]            = false;

            // Active Record Caching variables
            $this->ar_caching[$identifier]            = false;
            $this->ar_cache_exists[$identifier]        = array();
            $this->ar_cache_select[$identifier]        = array();
            $this->ar_cache_from[$identifier]        = array();
            $this->ar_cache_join[$identifier]        = array();
            $this->ar_cache_where[$identifier]        = array();
            $this->ar_cache_like[$identifier]        = array();
            $this->ar_cache_groupby[$identifier]    = array();
            $this->ar_cache_having[$identifier]        = array();
            $this->ar_cache_orderby[$identifier]    = array();
            $this->ar_cache_set[$identifier]        = array();
        }

        return $this;
    }

    /**
     * Select
     *
     * Generates the SELECT portion of the query
     *
     * @param	string
     *
     * @return object
     */
    public function select($select = '*', $escape = null)
    {
        // Set the global value if this was sepecified
        if (is_bool($escape)) {
            $this->_protect_identifiers = $escape;
        }

        if (is_string($select)) {
            $select = explode(',', $select);
        }

        foreach ($select as $val) {
            $val = trim($val);

            if ($val != '') {
                $this->ar_select[$this->ar_identifier][] = $val;

                if ($this->ar_caching[$this->ar_identifier] === true) {
                    $this->ar_cache_select[$this->ar_identifier][] = $val;
                    $this->ar_cache_exists[$this->ar_identifier][] = 'select';
                }
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Select Max
     *
     * Generates a SELECT MAX(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     *
     * @return object
     */
    public function select_max($select = '', $alias = '')
    {
        return $this->_max_min_avg_sum($select, $alias, 'MAX');
    }

    // --------------------------------------------------------------------

    /**
     * Select Min
     *
     * Generates a SELECT MIN(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     *
     * @return object
     */
    public function select_min($select = '', $alias = '')
    {
        return $this->_max_min_avg_sum($select, $alias, 'MIN');
    }

    // --------------------------------------------------------------------

    /**
     * Select Average
     *
     * Generates a SELECT AVG(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     *
     * @return object
     */
    public function select_avg($select = '', $alias = '')
    {
        return $this->_max_min_avg_sum($select, $alias, 'AVG');
    }

    // --------------------------------------------------------------------

    /**
     * Select Sum
     *
     * Generates a SELECT SUM(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     *
     * @return object
     */
    public function select_sum($select = '', $alias = '')
    {
        return $this->_max_min_avg_sum($select, $alias, 'SUM');
    }

    // --------------------------------------------------------------------

    /**
     * Processing Function for the four functions above:
     *
     *	select_max()
     *	select_min()
     *	select_avg()
     *  select_sum()
     *
     * @param	string	the field
     * @param	string	an alias
     *
     * @return object
     */
    public function _max_min_avg_sum($select = '', $alias = '', $type = 'MAX')
    {
        if (!is_string($select) or $select == '') {
            $this->display_error('db_invalid_query');
        }

        $type = strtoupper($type);

        if (!in_array($type, array('MAX', 'MIN', 'AVG', 'SUM'))) {
            show_error('Invalid function type: ' . $type);
        }

        if ($alias == '') {
            $alias = $this->_create_alias_from_table(trim($select));
        }

        $sql = $type . '(' . $this->_protect_identifiers(trim($select)) . ') AS ' . $alias;

        $this->ar_select[$this->ar_identifier][] = $sql;

        if ($this->ar_caching[$this->ar_identifier] === true) {
            $this->ar_cache_select[$this->ar_identifier][] = $sql;
            $this->ar_cache_exists[$this->ar_identifier][] = 'select';
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Determines the alias name based on the table
     *
     * @param	string
     *
     * @return string
     */
    public function _create_alias_from_table($item)
    {
        if (strpos($item, '.') !== false) {
            return end(explode('.', $item));
        }

        return $item;
    }

    // --------------------------------------------------------------------

    /**
     * DISTINCT
     *
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @param	bool
     *
     * @return object
     */
    public function distinct($val = true)
    {
        $this->ar_distinct[$this->ar_identifier] = (is_bool($val)) ? $val : true;

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * From
     *
     * Generates the FROM portion of the query
     *
     * @param	mixed	can be a string or array
     *
     * @return object
     */
    public function from($from)
    {
        foreach ((array) $from as $val) {
            // Extract any aliases that might exist.  We use this information
            // in the _protect_identifiers to know whether to add a table prefix
            $this->_track_aliases($val);

            $this->ar_from[$this->ar_identifier][] = $this->_protect_identifiers($val, true, null, false);

            if ($this->ar_caching[$this->ar_identifier] === true) {
                $this->ar_cache_from[$this->ar_identifier][] = $this->_protect_identifiers($val, true, null, false);
                $this->ar_cache_exists[$this->ar_identifier][] = 'from';
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Join
     *
     * Generates the JOIN portion of the query
     *
     * @param	string
     * @param	string	the join condition
     * @param	string	the type of join
     *
     * @return object
     */
    public function join($table, $cond, $type = '')
    {
        if ($type != '') {
            $type = strtoupper(trim($type));

            if (!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }

        // Extract any aliases that might exist.  We use this information
        // in the _protect_identifiers to know whether to add a table prefix
        $this->_track_aliases($table);

        // Strip apart the condition and protect the identifiers
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {
            $match[1] = $this->_protect_identifiers($match[1]);
            $match[3] = $this->_protect_identifiers($match[3]);

            $cond = $match[1] . $match[2] . $match[3];
        }

        // Assemble the JOIN statement
        $join = $type . 'JOIN ' . $this->_protect_identifiers($table, true, null, false) . ' ON ' . $cond;

        $this->ar_join[$this->ar_identifier][] = $join;
        if ($this->ar_caching[$this->ar_identifier] === true) {
            $this->ar_cache_join[$this->ar_identifier][] = $join;
            $this->ar_cache_exists[$this->ar_identifier][] = 'join';
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     *
     * @return object
     */
    public function where($key, $value = null, $escape = true)
    {
        return $this->_where($key, $value, 'AND ', $escape);
    }

    // --------------------------------------------------------------------

    /**
     * OR Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     *
     * @return object
     */
    public function or_where($key, $value = null, $escape = true)
    {
        return $this->_where($key, $value, 'OR ', $escape);
    }

    // --------------------------------------------------------------------

    /**
     * orwhere() is an alias of or_where()
     * this function is here for backwards compatibility, as
     * orwhere() has been deprecated
     */
    public function orwhere($key, $value = null, $escape = true)
    {
        return $this->or_where($key, $value, $escape);
    }

    // --------------------------------------------------------------------

    /**
     * Where
     *
     * Called by where() or orwhere()
     *
     * @param	mixed
     * @param	mixed
     * @param	string
     *
     * @return object
     */
    public function _where($key, $value = null, $type = 'AND ', $escape = null)
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        // If the escape value was not set will will base it on the global setting
        if (!is_bool($escape)) {
            $escape = $this->_protect_identifiers;
        }

        foreach ($key as $k => $v) {
            $prefix = (count($this->ar_where[$this->ar_identifier]) == 0 and count($this->ar_cache_where[$this->ar_identifier]) == 0) ? '' : $type;
            if (is_null($v) && !$this->_has_operator($k)) {
                // value appears not to have been set, assign the test to IS NULL
                $k .= ' IS NULL';
            }

            if (!is_null($v)) {
                if ($escape === true) {
                    $k = $this->_protect_identifiers($k, false, $escape);

                    $v = ' ' . $this->escape($v);
                }

                if (!$this->_has_operator($k)) {
                    $k .= ' =';
                }
            } else {
                $k = $this->_protect_identifiers($k, false, $escape);
            }

            $this->ar_where[$this->ar_identifier][] = $prefix . $k . $v;

            if ($this->ar_caching[$this->ar_identifier] === true) {
                $this->ar_cache_where[$this->ar_identifier][] = $prefix . $k . $v;
                $this->ar_cache_exists[$this->ar_identifier][] = 'where';
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Where_in
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * AND if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     *
     * @return object
     */
    public function where_in($key = null, $values = null)
    {
        return $this->_where_in($key, $values);
    }

    // --------------------------------------------------------------------

    /**
     * Where_in_or
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * OR if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     *
     * @return object
     */
    public function or_where_in($key = null, $values = null)
    {
        return $this->_where_in($key, $values, false, 'OR ');
    }

    // --------------------------------------------------------------------

    /**
     * Where_not_in
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with AND if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     *
     * @return object
     */
    public function where_not_in($key = null, $values = null)
    {
        return $this->_where_in($key, $values, true);
    }

    // --------------------------------------------------------------------

    /**
     * Where_not_in_or
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with OR if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     *
     * @return object
     */
    public function or_where_not_in($key = null, $values = null)
    {
        return $this->_where_in($key, $values, true, 'OR ');
    }

    // --------------------------------------------------------------------

    /**
     * Where_in
     *
     * Called by where_in, where_in_or, where_not_in, where_not_in_or
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @param	boolean	If the statement would be IN or NOT IN
     * @param	string
     *
     * @return object
     */
    public function _where_in($key = null, $values = null, $not = false, $type = 'AND ')
    {
        if ($key === null or $values === null) {
            return;
        }

        if (!is_array($values)) {
            $values = array($values);
        }

        $not = ($not) ? ' NOT' : '';

        foreach ($values as $value) {
            $this->ar_wherein[$this->ar_identifier][] = $this->escape($value);
        }

        $prefix = (count($this->ar_where[$this->ar_identifier]) == 0) ? '' : $type;

        $where_in = $prefix . $this->_protect_identifiers($key) . $not . " IN (" . implode(", ", $this->ar_wherein[$this->ar_identifier]) . ") ";

        $this->ar_where[$this->ar_identifier][] = $where_in;
        if ($this->ar_caching[$this->ar_identifier] === true) {
            $this->ar_cache_where[$this->ar_identifier][] = $where_in;
            $this->ar_cache_exists[$this->ar_identifier][] = 'where';
        }

        // reset the array for multiple calls
        $this->ar_wherein[$this->ar_identifier] = array();

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     *
     * @return object
     */
    public function like($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'AND ', $side);
    }

    // --------------------------------------------------------------------

    /**
     * Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     *
     * @return object
     */
    public function not_like($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'AND ', $side, 'NOT');
    }

    // --------------------------------------------------------------------

    /**
     * OR Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     *
     * @return object
     */
    public function or_like($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'OR ', $side);
    }

    // --------------------------------------------------------------------

    /**
     * OR Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     *
     * @return object
     */
    public function or_not_like($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'OR ', $side, 'NOT');
    }

    // --------------------------------------------------------------------

    /**
     * orlike() is an alias of or_like()
     * this function is here for backwards compatibility, as
     * orlike() has been deprecated
     */
    public function orlike($field, $match = '', $side = 'both')
    {
        return $this->or_like($field, $match, $side);
    }

    // --------------------------------------------------------------------

    /**
     * Like
     *
     * Called by like() or orlike()
     *
     * @param	mixed
     * @param	mixed
     * @param	string
     *
     * @return object
     */
    public function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '')
    {
        if (!is_array($field)) {
            $field = array($field => $match);
        }

        foreach ($field as $k => $v) {
            $k = $this->_protect_identifiers($k);

            $prefix = (count($this->ar_like[$this->ar_identifier]) == 0) ? '' : $type;

            $v = addslashes($v);

            if ($side == 'before') {
                $like_statement = $prefix . " $k $not LIKE '%{$v}'";
            } elseif ($side == 'after') {
                $like_statement = $prefix . " $k $not LIKE '{$v}%'";
            } else {
                $like_statement = $prefix . " $k $not LIKE '%{$v}%'";
            }

            $this->ar_like[$this->ar_identifier][] = $like_statement;
            if ($this->ar_caching[$this->ar_identifier] === true) {
                $this->ar_cache_like[$this->ar_identifier][] = $like_statement;
                $this->ar_cache_exists[$this->ar_identifier][] = 'like';
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * GROUP BY
     *
     * @param	string
     *
     * @return object
     */
    public function group_by($by)
    {
        if (is_string($by)) {
            $by = explode(',', $by);
        }

        foreach ($by as $val) {
            $val = trim($val);

            if ($val != '') {
                $this->ar_groupby[$this->ar_identifier][] = $this->_protect_identifiers($val);

                if ($this->ar_caching[$this->ar_identifier] === true) {
                    $this->ar_cache_groupby[$this->ar_identifier][] = $this->_protect_identifiers($val);
                    $this->ar_cache_exists[$this->ar_identifier][] = 'groupby';
                }
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * groupby() is an alias of group_by()
     * this function is here for backwards compatibility, as
     * groupby() has been deprecated
     */
    public function groupby($by)
    {
        return $this->group_by($by);
    }

    // --------------------------------------------------------------------

    /**
     * Sets the HAVING value
     *
     * Separates multiple calls with AND
     *
     * @param	string
     * @param	string
     *
     * @return object
     */
    public function having($key, $value = '', $escape = true)
    {
        return $this->_having($key, $value, 'AND ', $escape);
    }

    // --------------------------------------------------------------------

    /**
     * orhaving() is an alias of or_having()
     * this function is here for backwards compatibility, as
     * orhaving() has been deprecated
     */
    public function orhaving($key, $value = '', $escape = true)
    {
        return $this->or_having($key, $value, $escape);
    }
    // --------------------------------------------------------------------

    /**
     * Sets the OR HAVING value
     *
     * Separates multiple calls with OR
     *
     * @param	string
     * @param	string
     *
     * @return object
     */
    public function or_having($key, $value = '', $escape = true)
    {
        return $this->_having($key, $value, 'OR ', $escape);
    }

    // --------------------------------------------------------------------

    /**
     * Sets the HAVING values
     *
     * Called by having() or or_having()
     *
     * @param	string
     * @param	string
     *
     * @return object
     */
    public function _having($key, $value = '', $type = 'AND ', $escape = true)
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            $prefix = (count($this->ar_having[$this->ar_identifier]) == 0) ? '' : $type;

            if ($escape === true) {
                $k = $this->_protect_identifiers($k);
            }

            if (!$this->_has_operator($k)) {
                $k .= ' = ';
            }

            if ($v != '') {
                $v = ' ' . $this->escape_str($v);
            }

            $this->ar_having[$this->ar_identifier][] = $prefix . $k . $v;
            if ($this->ar_caching[$this->ar_identifier] === true) {
                $this->ar_cache_having[$this->ar_identifier][] = $prefix . $k . $v;
                $this->ar_cache_exists[$this->ar_identifier][] = 'having';
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Sets the ORDER BY value
     *
     * @param	string
     * @param	string	direction: asc or desc
     *
     * @return object
     */
    public function order_by($orderby, $direction = '')
    {
        if (strtolower($direction) == 'random') {
            $orderby = ''; // Random results want or don't need a field name
            $direction = $this->_random_keyword;
        } elseif (trim($direction) != '') {
            $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), true)) ? ' ' . $direction : ' ASC';
        }

        $orderby_statement = $this->_protect_identifiers($orderby) . $direction;

        $this->ar_orderby[$this->ar_identifier][] = $orderby_statement;
        if ($this->ar_caching[$this->ar_identifier] === true) {
            $this->ar_cache_orderby[$this->ar_identifier][] = $orderby_statement;
            $this->ar_cache_exists[$this->ar_identifier][] = 'orderby';
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * orderby() is an alias of order_by()
     * this function is here for backwards compatibility, as
     * orderby() has been deprecated
     */
    public function orderby($orderby, $direction = '')
    {
        return $this->order_by($orderby, $direction);
    }

    // --------------------------------------------------------------------

    /**
     * Sets the LIMIT value
     *
     * @param	integer	the limit value
     * @param	integer	the offset value
     *
     * @return object
     */
    public function limit($value, $offset = '')
    {
        $this->ar_limit[$this->ar_identifier] = $value;

        if ($offset != '') {
            $this->ar_offset[$this->ar_identifier] = $offset;
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * (PG) Sets the IGNORE value
     *
     * @param bool the ignore value
     *
     * @return \CI_DB_active_record
     */
    public function ignore($value = true)
    {
        $this->ar_ignore[$this->ar_identifier] = (is_bool($value)) ? $value : true;

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Sets the OFFSET value
     *
     * @param	integer	the offset value
     *
     * @return object
     */
    public function offset($offset)
    {
        $this->ar_offset[$this->ar_identifier] = $offset;

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * The "set" function.  Allows key/value pairs to be set for inserting or updating
     *
     * @param	mixed
     * @param	string
     * @param	boolean
     *
     * @return object
     */
    public function set($key, $value = '', $escape = true)
    {
        $key = $this->_object_to_array($key);

        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            if ($escape === false) {
                $this->ar_set[$this->ar_identifier][$this->_protect_identifiers($k)] = $v;
            } else {
                $this->ar_set[$this->ar_identifier][$this->_protect_identifiers($k)] = $this->escape($v);
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Get
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param	string	the table
     * @param	string	the limit clause
     * @param	string	the offset clause
     *
     * @return object
     */
    public function get($table = '', $limit = null, $offset = null)
    {
        if ($table != '') {
            $this->_track_aliases($table);
            $this->from($table);
        }

        if (!is_null($limit)) {
            $this->limit($limit, $offset);
        }

        $sql = $this->_compile_select();

        $result = $this->query($sql, false, true, $this->_cache_key($table));
        $this->_reset_select();

        return $result;
    }

    /**
     * "Count All Results" query
     *
     * Generates a platform-specific query string that counts all records
     * returned by an Active Record query.
     *
     * @param	string
     *
     * @return string
     */
    public function count_all_results($table = '')
    {
        if ($table != '') {
            $this->_track_aliases($table);
            $this->from($table);
        }

        $sql = $this->_compile_select($this->_count_string . $this->_protect_identifiers('numrows'));

        $query = $this->query($sql, false, true, $this->_cache_key($table));
        $this->_reset_select();

        if ($query->num_rows() == 0) {
            return '0';
        }

        $row = $query->row();

        return $row->numrows;
    }

    // --------------------------------------------------------------------

    /**
     * Get_Where
     *
     * Allows the where clause, limit and offset to be added directly
     *
     * @param	string	the where clause
     * @param	string	the limit clause
     * @param	string	the offset clause
     *
     * @return object
     */
    public function get_where($table = '', $where = null, $limit = null, $offset = null)
    {
        if ($table != '') {
            $this->from($table);
        }

        if (!is_null($where)) {
            $this->where($where);
        }

        if (!is_null($limit)) {
            $this->limit($limit, $offset);
        }

        $sql = $this->_compile_select();

        $result = $this->query($sql, false, true, $this->_cache_key($table));
        $this->_reset_select();

        return $result;
    }

    // --------------------------------------------------------------------

    /**
     * getwhere() is an alias of get_where()
     * this function is here for backwards compatibility, as
     * getwhere() has been deprecated
     */
    public function getwhere($table = '', $where = null, $limit = null, $offset = null)
    {
        return $this->get_where($table, $where, $limit, $offset);
    }

    // --------------------------------------------------------------------

    /**
     * Insert_Batch
     *
     * Compiles batch insert strings and runs the queries
     *
     * @param	string	the table to retrieve the results from
     * @param	array	an associative array of insert values
     *
     * @return object
     */
    public function insert_batch($table = '', $set = null, $ignore = false)
    {
        if (!is_null($set)) {
            $this->set_insert_batch($set);
        }

        if (count($this->ar_set[$this->ar_identifier]) == 0) {
            if ($this->db_debug) {
                //No valid data array.  Folds in cases where keys and values did not match up
                return $this->display_error('db_must_use_set');
            }

            return false;
        }

        if ($table == '') {
            if (!isset($this->ar_from[$this->ar_identifier][0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }

                return false;
            }

            $table = $this->ar_from[$this->ar_identifier][0];
        }

        // Batch this baby
        for ($i = 0, $total = count($this->ar_set[$this->ar_identifier]); $i < $total; $i = $i + 100) {
            $sql = $this->_insert_batch($this->_protect_identifiers($table, true, null, false), $this->ar_keys[$this->ar_identifier], array_slice($this->ar_set[$this->ar_identifier], $i, 100), $ignore);
            $this->query($sql, false, true, $this->_cache_key($table));
        }

        $this->_reset_write();

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * The "set_insert_batch" function.  Allows key/value pairs to be set for batch inserts
     *
     * @param	mixed
     * @param	string
     * @param	boolean
     *
     * @return object
     */
    public function set_insert_batch($key, $value = '', $escape = true)
    {
        $key = $this->_object_to_array_batch($key);

        if (!is_array($key)) {
            $key = array($key => $value);
        }

        $keys = array_keys(current($key));
        sort($keys);

        foreach ($key as $row) {
            if (count(array_diff($keys, array_keys($row))) > 0 or count(array_diff(array_keys($row), $keys)) > 0) {
                // batch function above returns an error on an empty array
                $this->ar_set[$this->ar_identifier][] = array();

                return;
            }

            ksort($row); // puts $row in the same order as our keys

            if ($escape === false) {
                $this->ar_set[$this->ar_identifier][] =  '(' . implode(',', $row) . ')';
            } else {
                $clean = array();

                foreach ($row as $value) {
                    $clean[] = $this->escape($value);
                }

                $this->ar_set[$this->ar_identifier][] =  '(' . implode(',', $clean) . ')';
            }
        }

        foreach ($keys as $k) {
            $this->ar_keys[$this->ar_identifier][] = $this->_protect_identifiers($k);
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Insert
     *
     * Compiles an insert string and runs the query
     *
     * @param	string	the table to retrieve the results from
     * @param	array	an associative array of insert values
     *
     * @return object
     */
    public function insert($table = '', $set = null)
    {
        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set[$this->ar_identifier]) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }

            return false;
        }

        if ($table == '') {
            if (!isset($this->ar_from[$this->ar_identifier][0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }

                return false;
            }

            $table = $this->ar_from[$this->ar_identifier][0];
        }

        $sql = $this->_insert($this->_protect_identifiers($table, true, null, false), array_keys($this->ar_set[$this->ar_identifier]), array_values($this->ar_set[$this->ar_identifier]));

        $this->_reset_write();

        return $this->query($sql, false, true, $this->_cache_key($table));
    }

    // --------------------------------------------------------------------

    /**
     * Update
     *
     * Compiles an update string and runs the query
     *
     * @param	string	the table to retrieve the results from
     * @param	array	an associative array of update values
     * @param	mixed	the where clause
     *
     * @return object
     */
    public function update($table = '', $set = null, $where = null, $limit = null)
    {
        // Combine any cached components with the current statements
        $this->_merge_cache();

        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set[$this->ar_identifier]) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }

            return false;
        }

        if ($table == '') {
            if (!isset($this->ar_from[$this->ar_identifier][0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }

                return false;
            }

            $table = $this->ar_from[$this->ar_identifier][0];
        }

        if ($where != null) {
            $this->where($where);
        }

        if ($limit != null) {
            $this->limit($limit);
        }

        $sql = $this->_update(
                            $this->_protect_identifiers($table, true, null, false),
                            $this->ar_set[$this->ar_identifier],
                            $this->ar_where[$this->ar_identifier],
                            $this->ar_orderby[$this->ar_identifier],
                            $this->ar_limit[$this->ar_identifier],
                            $this->ar_join[$this->ar_identifier]
                            );

        $this->_reset_write();
        $this->_reset_update();

        return $this->query($sql, false, true, $this->_cache_key($table));
    }

    public function _reset_update()
    {
        $ar_reset_items = array(
                                'ar_set'              => array(),
                                'ar_where'            => array(),
                                'ar_orderby'          => array(),
                                'ar_limit'            => array(),
                                'ar_join'             => array(),
                            );

        $this->_reset_run($ar_reset_items);
    }
    // --------------------------------------------------------------------

    /**
     * Empty Table
     *
     * Compiles a delete string and runs "DELETE FROM table"
     *
     * @param	string	the table to empty
     *
     * @return object
     */
    public function empty_table($table = '')
    {
        if ($table == '') {
            if (!isset($this->ar_from[$this->ar_identifier][0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }

                return false;
            }

            $table = $this->ar_from[$this->ar_identifier][0];
        } else {
            $table = $this->_protect_identifiers($table, true, null, false);
        }

        $sql = $this->_delete($table);

        $this->_reset_write();

        return $this->query($sql, false, true, $this->_cache_key($table));
    }

    // --------------------------------------------------------------------

    /**
     * Truncate
     *
     * Compiles a truncate string and runs the query
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @param	string	the table to truncate
     *
     * @return object
     */
    public function truncate($table = '')
    {
        if ($table == '') {
            if (!isset($this->ar_from[$this->ar_identifier][0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }

                return false;
            }

            $table = $this->ar_from[$this->ar_identifier][0];
        } else {
            $table = $this->_protect_identifiers($table, true, null, false);
        }

        $sql = $this->_truncate($table);

        $this->_reset_write();

        return $this->query($sql, false, true, $this->_cache_key($table));
    }

    // --------------------------------------------------------------------

    /**
     * Delete
     *
     * Compiles a delete string and runs the query
     *
     * @param	mixed	the table(s) to delete from. String or array
     * @param	mixed	the where clause
     * @param	mixed	the limit clause
     * @param	boolean
     *
     * @return object
     */
    public function delete($table = '', $where = '', $limit = null, $reset_data = true)
    {
        // Combine any cached components with the current statements
        $this->_merge_cache();

        if ($table == '') {
            if (!isset($this->ar_from[$this->ar_identifier][0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }

                return false;
            }

            $table = $this->ar_from[$this->ar_identifier][0];
        } elseif (is_array($table)) {
            foreach ($table as $single_table) {
                $this->delete($single_table, $where, $limit, false);
            }

            $this->_reset_write();

            return;
        } else {
            $table = $this->_protect_identifiers($table, true, null, false);
        }

        if ($where != '') {
            $this->where($where);
        }

        if ($limit != null) {
            $this->limit($limit);
        }

        if (count($this->ar_where[$this->ar_identifier]) == 0 && count($this->ar_like[$this->ar_identifier]) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_del_must_use_where');
            }

            return false;
        }

        $sql = $this->_delete($table, $this->ar_where[$this->ar_identifier], $this->ar_like[$this->ar_identifier], $this->ar_limit[$this->ar_identifier]);

        if ($reset_data) {
            $this->_reset_write();
        }

        return $this->query($sql, false, true, $this->_cache_key($table));
    }

    // --------------------------------------------------------------------

    /**
     * DB Prefix
     *
     * Prepends a database prefix if one exists in configuration
     *
     * @param	string	the table
     *
     * @return string
     */
    public function dbprefix($table = '')
    {
        if ($table == '') {
            $this->display_error('db_table_name_required');
        }

        return $this->dbprefix . $table;
    }

    // --------------------------------------------------------------------

    /**
     * Track Aliases
     *
     * Used to track SQL statements written with aliased tables.
     *
     * @param	string	The table to inspect
     *
     * @return string
     */
    public function _track_aliases($table)
    {
        if (is_array($table)) {
            foreach ($table as $t) {
                $this->_track_aliases($t);
            }

            return;
        }

        // Does the string contain a comma?  If so, we need to separate
        // the string into discreet statements
        if (strpos($table, ',') !== false) {
            return $this->_track_aliases(explode(',', $table));
        }

        // if a table alias is used we can recognize it by a space
        if (strpos($table, " ") !== false) {
            // if the alias is written with the AS keyword, remove it
            $table = preg_replace('/ AS /i', ' ', $table);

            // Grab the alias
            $table = trim(strrchr($table, " "));

            // Store the alias, if it doesn't already exist
            if (!in_array($table, $this->ar_aliased_tables[$this->ar_identifier])) {
                $this->ar_aliased_tables[$this->ar_identifier][] = $table;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Compile the SELECT statement
     *
     * Generates a query string based on which functions were used.
     * Should not be called directly.  The get() function calls it.
     *
     * @return string
     */
    public function _compile_select($select_override = false)
    {
        // Combine any cached components with the current statements
        $this->_merge_cache();

        // ----------------------------------------------------------------

        // Write the "select" portion of the query

        if ($select_override !== false) {
            $sql = $select_override;
        } else {
            $sql = (!$this->ar_distinct[$this->ar_identifier]) ? 'SELECT ' : 'SELECT DISTINCT ';

            if (count($this->ar_select[$this->ar_identifier]) == 0) {
                $sql .= '*';
            } else {
                // Cycle through the "select" portion of the query and prep each column name.
                // The reason we protect identifiers here rather then in the select() function
                // is because until the user calls the from() function we don't know if there are aliases
                foreach ($this->ar_select[$this->ar_identifier] as $key => $val) {
                    $this->ar_select[$this->ar_identifier][$key] = $this->_protect_identifiers($val);
                }

                $sql .= implode(', ', $this->ar_select[$this->ar_identifier]);
            }
        }

        // ----------------------------------------------------------------

        // Write the "FROM" portion of the query

        if (count($this->ar_from[$this->ar_identifier]) > 0) {
            $sql .= "\nFROM ";

            $sql .= $this->_from_tables($this->ar_from[$this->ar_identifier]);
        }

        // ----------------------------------------------------------------

        // Write the "JOIN" portion of the query

        if (count($this->ar_join[$this->ar_identifier]) > 0) {
            $sql .= "\n";

            $sql .= implode("\n", $this->ar_join[$this->ar_identifier]);
        }

        // ----------------------------------------------------------------

        // Write the "WHERE" portion of the query

        if (count($this->ar_where[$this->ar_identifier]) > 0 or count($this->ar_like[$this->ar_identifier]) > 0) {
            $sql .= "\n";

            $sql .= "WHERE ";
        }

        $sql .= implode("\n", $this->ar_where[$this->ar_identifier]);

        // ----------------------------------------------------------------

        // Write the "LIKE" portion of the query

        if (count($this->ar_like[$this->ar_identifier]) > 0) {
            if (count($this->ar_where[$this->ar_identifier]) > 0) {
                $sql .= "\nAND ";
            }

            $sql .= implode("\n", $this->ar_like[$this->ar_identifier]);
        }

        // ----------------------------------------------------------------

        // Write the "GROUP BY" portion of the query

        if (count($this->ar_groupby[$this->ar_identifier]) > 0) {
            $sql .= "\nGROUP BY ";

            $sql .= implode(', ', $this->ar_groupby[$this->ar_identifier]);
        }

        // ----------------------------------------------------------------

        // Write the "HAVING" portion of the query

        if (count($this->ar_having[$this->ar_identifier]) > 0) {
            $sql .= "\nHAVING ";
            $sql .= implode("\n", $this->ar_having[$this->ar_identifier]);
        }

        // ----------------------------------------------------------------

        // Write the "ORDER BY" portion of the query

        if (count($this->ar_orderby[$this->ar_identifier]) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $this->ar_orderby[$this->ar_identifier]);

            if ($this->ar_order[$this->ar_identifier] !== false) {
                $sql .= ($this->ar_order[$this->ar_identifier] == 'desc') ? ' DESC' : ' ASC';
            }
        }

        // ----------------------------------------------------------------

        // Write the "LIMIT" portion of the query

        if (is_numeric($this->ar_limit[$this->ar_identifier])) {
            $sql .= "\n";
            $sql = $this->_limit($sql, $this->ar_limit[$this->ar_identifier], $this->ar_offset[$this->ar_identifier]);
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param	object
     *
     * @return array
     */
    public function _object_to_array($object)
    {
        if (!is_object($object)) {
            return $object;
        }

        $array = array();
        foreach (get_object_vars($object) as $key => $val) {
            // There are some built in keys we need to ignore for this conversion
            if (!is_object($val) && !is_array($val) && $key != '_parent_name' && $key != '_ci_scaffolding' && $key != '_ci_scaff_table') {
                $array[$key] = $val;
            }
        }

        return $array;
    }

    // --------------------------------------------------------------------

    /**
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param	object
     *
     * @return array
     */
    public function _object_to_array_batch($object)
    {
        if (!is_object($object)) {
            return $object;
        }

        $array = array();
        $out = get_object_vars($object);
        $fields = array_keys($out);

        foreach ($fields as $val) {
            // There are some built in keys we need to ignore for this conversion
            if ($val != '_parent_name') {
                $i = 0;
                foreach ($out[$val] as $data) {
                    $array[$i][$val] = $data;
                    ++$i;
                }
            }
        }

        return $array;
    }

    // --------------------------------------------------------------------

    /**
     * Start Cache
     *
     * Starts AR caching
     *
     * @return void
     */
    public function start_cache()
    {
        $this->ar_caching[$this->ar_identifier] = true;
    }

    // --------------------------------------------------------------------

    /**
     * Stop Cache
     *
     * Stops AR caching
     *
     * @return void
     */
    public function stop_cache()
    {
        $this->ar_caching[$this->ar_identifier] = false;
    }

    // --------------------------------------------------------------------

    /**
     * Flush Cache
     *
     * Empties the AR cache
     *
     * @return void
     */
    public function flush_cache()
    {
        $this->_reset_run(
                            array(
                                    'ar_cache_select'      => array(),
                                    'ar_cache_from'        => array(),
                                    'ar_cache_join'        => array(),
                                    'ar_cache_where'       => array(),
                                    'ar_cache_like'        => array(),
                                    'ar_cache_groupby'     => array(),
                                    'ar_cache_having'      => array(),
                                    'ar_cache_orderby'     => array(),
                                    'ar_cache_set'         => array(),
                                    'ar_cache_exists'      => array(),
                                )
                            );
    }

    // --------------------------------------------------------------------

    /**
     * Merge Cache
     *
     * When called, this function merges any cached AR arrays with
     * locally called ones.
     *
     * @return void
     */
    public function _merge_cache()
    {
        if (count($this->ar_cache_exists[$this->ar_identifier]) == 0) {
            return;
        }

        foreach ($this->ar_cache_exists[$this->ar_identifier] as $val) {
            $ar_variable    = 'ar_' . $val;
            $ar_cache_var    = 'ar_cache_' . $val;

            if (count($this->{$ar_cache_var}) == 0) {
                continue;
            }
            $this->{$ar_variable}[$this->ar_identifier] = array_unique(array_merge($this->{$ar_cache_var}[$this->ar_identifier], $this->{$ar_variable}[$this->ar_identifier]));
        }

        // If we are "protecting identifiers" we need to examine the "from"
        // portion of the query to determine if there are any aliases
        if ($this->_protect_identifiers === true and count($this->ar_cache_from[$this->ar_identifier]) > 0) {
            $this->_track_aliases($this->ar_from[$this->ar_identifier]);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @param	array	An array of fields to reset
     *
     * @return void
     */
    public function _reset_run($ar_reset_items)
    {
        foreach ($ar_reset_items as $item => $default_value) {
            if (!in_array($item, $this->ar_store_array[$this->ar_identifier])) {
                $this->{$item}[$this->ar_identifier] = $default_value;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @return void
     */
    public function _reset_select()
    {
        $ar_reset_items = array(
                                'ar_select'            => array(),
                                'ar_from'              => array(),
                                'ar_join'              => array(),
                                'ar_where'             => array(),
                                'ar_like'              => array(),
                                'ar_groupby'           => array(),
                                'ar_having'            => array(),
                                'ar_orderby'           => array(),
                                'ar_wherein'           => array(),
                                'ar_aliased_tables'    => array(),
                                'ar_distinct'          => false,
                                'ar_limit'             => false,
                                'ar_offset'            => false,
                                'ar_order'             => false,
                                'ar_ignore'            => false,
                            );

        $this->_reset_run($ar_reset_items);
    }

    // --------------------------------------------------------------------

    /**
     * Resets the active record "write" values.
     *
     * Called by the insert() update() and delete() functions
     *
     * @return void
     */
    public function _reset_write()
    {
        $ar_reset_items = array(
                                'ar_set'          => array(),
                                'ar_from'         => array(),
                                'ar_where'        => array(),
                                'ar_like'         => array(),
                                'ar_orderby'      => array(),
                                'ar_keys'         => array(),
                                'ar_limit'        => false,
                                'ar_order'        => false,
                                );

        $this->_reset_run($ar_reset_items);
    }

    // --------------------------------------------------------------------

    /**
     * Validate table name for using in memcache
     *
     * @param string $key
     *
     * @return var
     */
    private function _cache_key($key)
    {
        if (count($this->ar_from[$this->ar_identifier]) === 1) {
            $key = $this->ar_from[$this->ar_identifier][0];
        }
        if (!empty($this->mc_anytable)) {
            if ($this->mc_anytable_once) {
                $this->mc_anytable = false;
                $this->mc_anytable_once = true;
            }

            return $key;
        } else {
            return $this->in_memcache_tables($key);
        }
    }
}

/* End of file DB_active_rec.php */
/* Location: ./system/database/DB_active_rec.php */
