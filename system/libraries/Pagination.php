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
 * Pagination Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 *
 * @category	Pagination
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/pagination.html
 */
class CI_Pagination
{
    public $base_url                = ''; // The page we are linking to
    public $total_rows            = ''; // Total number of items (database results)
    public $per_page                = 10; // Max number of items you want shown per page
    public $num_links                =  2; // Number of "digit" links to show before/after the currently viewed page
    public $cur_page                =  0; // The current page being viewed
    public $first_link            = '&lsaquo; First';
    public $next_link                = '&gt;';
    public $prev_link                = '&lt;';
    public $last_link                = 'Last &rsaquo;';
    public $uri_segment            = 3;
    public $full_tag_open            = '';
    public $full_tag_close            = '';
    public $first_tag_open            = '';
    public $first_tag_close        = '&nbsp;';
    public $last_tag_open            = '&nbsp;';
    public $last_tag_close            = '';
    public $cur_tag_open            = '&nbsp;<b>';
    public $cur_tag_close            = '</b>';
    public $next_tag_open            = '&nbsp;';
    public $next_tag_close            = '&nbsp;';
    public $prev_tag_open            = '&nbsp;';
    public $prev_tag_close            = '';
    public $num_tag_open            = '&nbsp;';
    public $num_tag_close            = '';
    public $page_query_string        = false;
    public $query_string_segment    = 'per_page';
    public $format                    = true;

    /**
     * Constructor
     *
     * @param	array	initialization parameters
     */
    public function __construct($params = array())
    {
        if (count($params) > 0) {
            $this->initialize($params);
        }

        log_message('debug', "Pagination Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Initialize Preferences
     *
     * @param	array	initialization parameters
     *
     * @return void
     */
    public function initialize($params = array())
    {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                if (isset($this->{$key})) {
                    $this->{$key} = $val;
                }
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Generate the pagination links
     *
     * @return string/array
     */
    public function create_links()
    {
        // If our item count or per-page total is zero there is no need to continue.
        if ($this->total_rows == 0 or $this->per_page == 0) {
            return '';
        }

        // Calculate the total number of pages
        $num_pages = ceil($this->total_rows / $this->per_page);

        // Is there only one page? Hm... nothing more to do here then.
        if ($num_pages == 1) {
            return '';
        }

        // Determine the current page number.
        $CI = &get_instance();

        if ($CI->config->item('enable_query_strings') === true or $this->page_query_string === true) {
            if ($CI->input->get($this->query_string_segment) != 0) {
                $this->cur_page = $CI->input->get($this->query_string_segment);

                // Prep the current page - no funny business!
                $this->cur_page = (int) $this->cur_page;
            }
        } else {
            if ($CI->uri->segment($this->uri_segment) != 0) {
                $this->cur_page = $CI->uri->segment($this->uri_segment);

                // Prep the current page - no funny business!
                $this->cur_page = (int) $this->cur_page;
            }
        }

        $this->num_links = (int) $this->num_links;

        if ($this->num_links < 1) {
            show_error('Your number of links must be a positive number.');
        }

        if (!is_numeric($this->cur_page)) {
            $this->cur_page = 0;
        }

        // Is the page number beyond the result range?
        // If so we show the last page
        if ($this->cur_page > $this->total_rows) {
            $this->cur_page = ($num_pages - 1) * $this->per_page;
        }

        $uri_page_number = $this->cur_page;
        $this->cur_page = floor(($this->cur_page / $this->per_page) + 1);

        // Calculate the start and end numbers. These determine
        // which number to start and end the digit links with
        $start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
        $end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

        // Is pagination being used over GET or POST?  If get, add a per_page query
        // string. If post, add a trailing slash to the base URL if needed
        if ($CI->config->item('enable_query_strings') === true or $this->page_query_string === true) {
            $this->base_url = rtrim($this->base_url) . '&amp;' . $this->query_string_segment . '=';
        } else {
            $this->base_url = rtrim($this->base_url, '/') . '/';
        }

        // And here we go...
        $output = '';
        $return = array('first' => '', 'prev' => '', 'pages' => array(), 'next' => '', 'last' => '');

        // Render the "First" link
        if ($this->cur_page > ($this->num_links + 1)) {
            if ($this->format) {
                $output .= $this->first_tag_open . '<a href="' . $this->base_url . '">' . $this->first_link . '</a>' . $this->first_tag_close;
            } else {
                $return['first'] = '<a href="' . $this->base_url . '">' . $this->first_link . '</a>';
            }
        }

        // Render the "previous" link
        if ($this->cur_page != 1) {
            $i = $uri_page_number - $this->per_page;
            if ($i == 0) {
                $i = '';
            }

            if ($this->format) {
                $output .= $this->prev_tag_open . '<a href="' . $this->base_url . $i . '">' . $this->prev_link . '</a>' . $this->prev_tag_close;
            } else {
                $return['prev'] = '<a href="' . $this->base_url . '">' . $this->first_link . '</a>';
            }
        }

        // Write the digit links
        if ($this->format) {
            for ($loop = $start - 1; $loop <= $end; ++$loop) {
                $i = ($loop * $this->per_page) - $this->per_page;

                if ($i >= 0) {
                    if ($this->cur_page == $loop) {
                        $output .= $this->cur_tag_open . $loop . $this->cur_tag_close; // Current page
                    } else {
                        $n = ($i == 0) ? '' : $i;
                        $output .= $this->num_tag_open . '<a href="' . $this->base_url . $n . '">' . $loop . '</a>' . $this->num_tag_close;
                    }
                }
            }
        } else {
            for ($loop = $start - 1; $loop <= $end; ++$loop) {
                $i = ($loop * $this->per_page) - $this->per_page;

                if ($i >= 0) {
                    if ($this->cur_page == $loop) {
                        // Current page
                        $return['pages'][$loop] = '<span>' . $loop . '</span>';
                    } else {
                        $n = ($i == 0) ? '' : $i;
                        $return['pages'][$loop] = '<a href="' . $this->base_url . $n . '">' . $loop . '</a>';
                    }
                }
            }
        }

        // Render the "next" link
        if ($this->cur_page < $num_pages) {
            if ($this->format) {
                $output .= $this->next_tag_open . '<a href="' . $this->base_url . ($this->cur_page * $this->per_page) . '">' . $this->next_link . '</a>' . $this->next_tag_close;
            } else {
                $return['next'] = '<a href="' . $this->base_url . ($this->cur_page * $this->per_page) . '">' . $this->next_link . '</a>';
            }
        }

        // Render the "Last" link
        if (($this->cur_page + $this->num_links) < $num_pages) {
            $i = (($num_pages * $this->per_page) - $this->per_page);

            if ($this->format) {
                $output .= $this->last_tag_open . '<a href="' . $this->base_url . $i . '">' . $this->last_link . '</a>' . $this->last_tag_close;
            } else {
                $return['last'] = '<a href="' . $this->base_url . $i . '">' . $this->last_link . '</a>';
            }
        }

        if (!$this->format) {
            return $return;
        }

        // Kill double slashes.  Note: Sometimes we can end up with a double slash
        // in the penultimate link so we'll kill all double slashes.
        $output = preg_replace("#([^:])//+#", "\\1/", $output);

        // Add the wrapper HTML if exists
        $output = $this->full_tag_open . $output . $this->full_tag_close;

        return $output;
    }
}
// END Pagination Class

/* End of file Pagination.php */
/* Location: ./system/libraries/Pagination.php */
