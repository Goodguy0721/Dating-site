<?php

namespace Pg\Modules\Geomap\Models;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Yandexmapsv2_model extends \Model
{
    private $CI;
    private $geocode_url = 'http://geocode-maps.yandex.ru/1.x/?';

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

        return $this->CI->view->fetch('yandexmapsv2_html', null, 'geomap');
    }

    public function update_html($map_id, $markers = array())
    {
        $this->CI->view->assign('map_id', $map_id);
        $this->CI->view->assign('markers', $markers);

        return $this->CI->view->fetch('yandexmapsv2_update', null, 'geomap');
    }

    public function create_geocoder($key)
    {
        return $this->CI->view->fetch('yandexmapsv2_geocoder', null, 'geomap');
    }

    public function get_coordinates($location, $key)
    {
        $data = array(
            'geocode' => $location,
            'format'  => 'json',
            'results' => 1,
        );

        $url = $this->geocode_url . http_build_query($data);
        $response = json_decode(file_get_contents($url));

        if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
            $coord_array = explode(' ', $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
            $coordinates['lat'] = $coord_array[1];
            $coordinates['lon'] = $coord_array[0];

            return $coordinates;
        }
    }
}
