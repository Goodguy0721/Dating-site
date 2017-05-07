<?php

namespace Pg\Modules\Users\Models;

define('SAVED_SEARCHES_TABLE', DB_PREFIX . 'saved_searches');

class Saved_search_model extends \Model
{
    protected $ci;

    protected $_fields = array(
        'id',
        'id_user',
        'search_data',
        'date_search',
    );

    /**
     * Parameters of saved search
     *
     * @var array
     */
    protected $saved_params = array(
        'looking_user_type',
        'user_type',
        'age_min',
        'age_max',
        'id_country',
        'id_region',
        'id_city',
        'distance',
        'living_with',
        'with_photo',
        'height_min',
        'height_max',
        'relationship_status',
        'looking_for',
        'ethnicity',
        'body_type',
        'religion',
        'education',
        'smoking',
        'drinking',
        'children',
        'keyword',
    );

    /**
     * Separator of search criteria
     *
     * @var string
     */
    protected $_separator = '; ';

    /**
     * Constructor
     *
     * @return Listings_search_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    /**
     * Return data of saved search by identifier
     *
     * @param integer $search_id saved search identifier
     * @return array
     */
    public function getSearchById($search_id)
    {
        $this->ci->db->select(implode(', ', $this->_fields));
        $this->ci->db->from(SAVED_SEARCHES_TABLE);
        $this->ci->db->where('id', $search_id);
        $results = $this->ci->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            $data = $this->formatSearch($results[0]);
            return $data;
        }
    }

    /**
     * Filter saved searches by user
     *
     * @param integer $user_id user identifier
     * @return array
     */
    protected function getSearchesByUser($user_id)
    {
        $params = array();
        if (!$user_id) {
            return array();
        }
        $params['where']['id_user'] = $user_id;
        return $params;
    }

    /**
     * Return saved search objects from data source as array
     *
     * @param integer $page page of results
     * @param integer $items_on_page items per page
     * @param array $order_by sorting data
     * @param array $params sql criteria
     * @return array
     */
    protected function _getSearchesList($page = null, $items_on_page = null, $order_by = array(), $params = array())
    {
        $this->ci->db->from(SAVED_SEARCHES_TABLE)
                ->select('id, id_user, search_data, date_search');

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->ci->db->where($value);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                $this->ci->db->order_by($field . ' ' . $dir);
            }
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->ci->db->limit($items_on_page, $items_on_page * ($page - 1));
        }
        $results = $this->ci->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return $this->formatSearches($results);
        }
        return array();
    }

    /**
     * Return number of saved search objects in data source
     *
     * @param array $params sql criteria
     * @return integer
     */
    protected function _getSearchesCount($params)
    {
        $this->ci->db->select('COUNT(id) AS cnt')
                ->from(SAVED_SEARCHES_TABLE);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->ci->db->where($value);
            }
        }

        $result = $this->ci->db->get()->result();
        if (!empty($result)) {
            return intval($result[0]->cnt);
        } else {
            return 0;
        }
    }

    /**
     * Return saved search objects as array
     *
     * @param array $filters filters data
     * @param integer $page page of results
     * @param integer $items_on_page items per page
     * @param array $order_by sorting data
     * @return array
     */
    public function getSearchesList($filters = array(), $page = null, $items_on_page = null, $order_by = array())
    {
        $params = array();
        foreach ($filters as $filter => $value) {
            $params = array_merge($params, $this->{'getSearchesBy' . ucFirst($filter)}($value));
        }
        return $this->_getSearchesList($page, $items_on_page, $order_by, $params);
    }

    /**
     * Return number of saved search objects
     *
     * @param array $filters filters data
     * @return integer
     */
    public function getSearchesCount($filters = array())
    {
        $params = array();
        foreach ($filters as $filter => $value) {
            $params = array_merge($params, $this->{'getSearchesBy' . ucFirst($filter)}($value));
        }
        return $this->_getSearchesCount($params);
    }

    /**
     * Format data of saved search object
     *
     * @param array $data saved search data
     * @return array
     */
    public function formatSearch($data)
    {
        return array_shift($this->formatSearches(array($data)));
    }

    /**
     * Format data of saved searches objects
     *
     * @param array $data data of saved searches objects
     * @return array
     */
    public function formatSearches($data)
    {
        $this->ci->load->model('Users_model');

        $this->load->helper('date_format');

        foreach ($data as $key => $search) {
            $search_data = $search['search_data'] = $search['search_data'] ?
                (array) unserialize($search['search_data']) : array();

            if (!empty($search_data['id_country'])) {
                $this->ci->load->helper('countries');
                $location_data = array();
                $location_data['country'] = $search_data['id_country'];
                $exclude[] = 'id_country';

                if (!empty($search_data['id_region'])) {
                    $location_data['region'] = $search_data['id_region'];
                    $exclude[] = 'id_region';
                    if (!empty($search_data['id_city'])) {
                        $location_data['city'] = $search_data['id_city'];
                        $exclude[] = 'id_city';
                        if (!empty($search_data['id_district'])) {
                            $location_data['district'] = $search_data['id_district'];
                            $exclude[] = 'id_district';
                            $location = districts_output_format(array($location_data));
                            if (!empty($location)) {
                                $search['name'] .= $this->_separator . $location[0];
                            }
                        } else {
                            $location = cities_output_format(array($location_data));
                            if (!empty($location)) {
                                $search['name'] .= $this->_separator . $location[0];
                            }
                        }
                    } else {
                        $location = regions_output_format(array($location_data));
                        if (!empty($location)) {
                            $search['name'] .= $this->_separator . $location[0];
                        }
                    }
                } else {
                    $location = countries_output_format(array($location_data));
                    if (!empty($location)) {
                        $search['name'] .= $this->_separator . $location[0];
                    }
                }
            }

            $data[$key] = $search;
        }
        return $data;
    }

    /**
     * Validate data of saved search for saving to data source
     *
     * @param integer $search_id saved search identifier
     * @param array $data saved search data
     * @return array
     */
    public function validateSearch($search_id, $data, $check_uniqueness = false)
    {
        $return = array('errors' => array(), 'data' => array());

        if (isset($data['id_user'])) {
            $return['data']['id_user'] = intval($data['id_user']);
        }

        if (isset($data['search_data'])) {
            $search_data = array();
            $this->ci->load->model('Users_model');

            foreach ($this->saved_params as $f) {
                if (!empty($data['search_data'][$f])) {
                    $search_data[$f] = $data['search_data'][$f];
                }
            }

            if (!empty($search_data)) {
                $return['data']['search_data'] = serialize($search_data);
            } else {
                $return['errors'][] = l('error_empty_save_search_data', 'listings');
            }
            if ($check_uniqueness && empty($return['errors']) &&
                    !$this->isSearchUnique($return['data']['search_data'])) {
                $return['errors'][] = l('error_search_data_not_unique', 'listings');
            }
        }
        return $return;
    }

    public function isSearchUnique($search_data)
    {
        return 0 === $this->_getSearchesCount(['where' => ['hash' => md5($search_data)]]);
    }

    /**
     * Save search object to data source
     *
     * @param integer $search_id search identifier
     * @param array $data search data
     * @return integer
     */
    public function saveSearch($search_id, $data)
    {
        if (!empty($data['search_data'])) {
            $data['hash'] = md5($data['search_data']);
        }
        if (empty($search_id)) {
            $data['date_search'] = date('Y-m-d H:i:s');
            $this->ci->db->insert(SAVED_SEARCHES_TABLE, $data);
            $search_id = $this->ci->db->insert_id();
        } else {
            $this->ci->db->where('id', $search_id);
            $this->ci->db->update(SAVED_SEARCHES_TABLE, $data);
        }
        return $search_id;
    }

    /**
     * Remove search object from data source by identifier
     *
     * @param integer $search_id search identifier
     * @retun void
     */
    public function deleteSearch($search_id)
    {
        $this->ci->db->where('id', $search_id);
        $this->ci->db->delete(SAVED_SEARCHES_TABLE);
    }
}
