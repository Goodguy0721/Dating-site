<?php

namespace Pg\Modules\Geomap\Models;

/**
 * Google maps driver model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Googlemapsv3_model extends \Model
{
    private $CI;
    private $geocode_url = 'http://maps.google.com/maps/api/geocode/json?';

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
        $amenities = $this->pg_language->ds->get_reference(
            'geomap', 'amenities_googlemapsv3', $this->pg_language->current_lang_id
        );
        $settings['amenities_names'] = $amenities['option'];
        $this->CI->view->assign('map_reg_key', $key);
        $this->CI->view->assign('settings', $settings);
        $this->CI->view->assign('view_settings', $view_settings);
        $this->CI->view->assign('markers', $markers);
        $this->CI->view->assign('map_id', $map_id);
        $this->CI->view->assign('rand', rand(100000, 999999));

        return $this->CI->view->fetch('googlemapsv3_html', null, 'geomap');
    }

    public function update_html($map_id, $markers = array(), $settings)
    {
        $this->CI->view->assign('map_id', $map_id);
        $this->CI->view->assign('markers', $markers);
        $this->CI->view->assign('view_settings', $settings);

        return $this->CI->view->fetch('googlemapsv3_update', null, 'geomap');
    }

    public function create_geocoder($key)
    {
        return $this->CI->view->fetch('googlemapsv3_geocoder', null, 'geomap');
    }

    public function get_coordinates($location, $key)
    {
        $data = array(
            'sensor'  => false,
            'address' => $location,
        );

        $url = $this->geocode_url . http_build_query($data);
        $response = json_decode(file_get_contents($url), true);

        if (isset($response['results'][0]['geometry']['location'])) {
            $coordinates['lat'] = $response['results'][0]['geometry']['location']['lat'];
            $coordinates['lon'] = $response['results'][0]['geometry']['location']['lng'];

            return $coordinates;
        }
    }
}
