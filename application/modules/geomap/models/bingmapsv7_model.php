<?php

namespace Pg\Modules\Geomap\Models;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Bingmapsv7_model extends \Model
{
    private $CI;
    private $geocode_url = 'http://dev.virtualearth.net/REST/v1/Locations?';

    /**
     * Constructor
     *
     * @return
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
    }

    public function create_html($key, $settings, $view_settings, $markers = array(), $map_id = false)
    {
        $this->CI->view->assign('map_reg_key', $key);
        $this->CI->view->assign('settings', $settings);
        $this->CI->view->assign('view_settings', $view_settings);
        $this->CI->view->assign('markers', $markers);
        $this->CI->view->assign('map_id', $map_id);
        $this->CI->view->assign('rand', rand(100000, 999999));

        return $this->CI->view->fetch('bingmapsv7_html', null, 'geomap');
    }

    public function update_html($map_id, $markers = array())
    {
        $this->CI->view->assign('map_id', $map_id);
        $this->CI->view->assign('markers', $markers);

        return $this->CI->view->fetch('bingmapsv7_update', null, 'geomap');
    }

    public function create_geocoder($key)
    {
        return $this->CI->view->fetch('bingmapsv7_geocoder', null, 'geomap');
    }

    public function get_coordinates($location, $key)
    {
        $url = $this->geocode_url . 'query=' . urlencode($location) . '&maxResults=1&output=json&jsonp=?&key=' . $key;
        $response = json_decode(file_get_contents($url));

        if (isset($response['resourceSets'][0]['resources'][0]['point']['coordinates'])) {
            $coordinates['lat'] = $response['resourceSets'][0]['resources'][0]['point']['coordinates'][0];
            $coordinates['lon'] = $response['resourceSets'][0]['resources'][0]['point']['coordinates'][1];

            return $coordinates;
        }
    }
}
