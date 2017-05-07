<?php

namespace Pg\Modules\Install\Models;

/**
 * Install module
 *
 * @package 	PG_Core
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Install Libraries Model
 *
 * @package 	PG_Core
 * @subpackage 	Install
 *
 * @category 	models
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Libraries_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    private $ci;

    /**
     * Class constructor
     *
     * @return Libraries_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->ci->load->model("Install_model");
    }

    public function get_library_config($gid)
    {
        $library_config = LIBPATH . $gid . '/_library.php';
        if (file_exists($library_config)) {
            unset($library);
            require $library_config;

            return $library;
        }

        return false;
    }

    public function get_enabled_libraries()
    {
        $installed_libraries = $this->get_installed_libraries();
        $enable_libraries = array();

        $dir_path = LIBPATH;
        $d = dir($dir_path);
        while (false !== ($entry = $d->read())) {
            if (substr($entry, 0, 1) == '.' || !is_dir(LIBPATH . $entry)) {
                continue;
            }
            if (isset($installed_libraries[$entry]) && !empty($installed_libraries[$entry])) {
                continue;
            }
            $library_data = $this->get_library_config($entry);
            if (empty($library_data)) {
                continue;
            }
            $enable_libraries[$entry] = $library_data;
        }
        $d->close();

        return $enable_libraries;
    }

    public function get_installed_libraries()
    {
        $libraries = array();
        $this->ci->load->library('pg_library');
        $libraries_by_id = $this->ci->pg_library->return_libraries();
        if (!empty($libraries_by_id)) {
            foreach ($libraries_by_id as $library) {
                $library_data = $this->get_library_config($library["gid"]);
                $library = array_merge($library, $library_data);
                $libraries[$library["gid"]] = $library;
            }
        }

        return $libraries;
    }

    public function get_installed_library($gid)
    {
        $this->ci->load->library('pg_library');
        $library = $this->ci->pg_library->get_library_by_gid($gid);
        if (!empty($library)) {
            $library_data = $this->get_library_config($gid);
            $library = array_merge($library, $library_data);

            return $library;
        } else {
            return array();
        }
    }

    public function get_libraries_update_info()
    {
        $this->ci->config->load('libraries', true);
        $check_updates = $this->ci->config->item('check_updates', 'libraries');
        if (!$check_updates) {
            return false;
        }

        $check_updates_period = $this->ci->config->item('check_updates_period', 'libraries');
        $temp_file = TEMPPATH . "trash/libraries_updates.txt";

        $content = "";
        if (file_exists($temp_file)) {
            $stat = stat($temp_file);
            if ($stat["mtime"] + $check_updates_period > time()) {
                $content = file_get_contents($temp_file);
            }
        }

        if (empty($content)) {
            $this->ci->load->library('Snoopy');
            $check_updates_url = $this->ci->config->item('check_updates_url', 'libraries');

            $libraries_by_id = $this->ci->pg_library->return_libraries();
            if (!empty($libraries_by_id)) {
                foreach ($libraries_by_id as $library) {
                    $libraries[] = $library["gid"];
                }
            } else {
                return false;
            }

            @$this->ci->snoopy->submit($check_updates_url, array("libraries" => $libraries));
            $txt = @$this->ci->snoopy->results;
            $cod = @$this->ci->snoopy->response_code;
            $out = @$this->ci->snoopy->timed_out;

            if (!$out && (!empty($txt)) && preg_match("/200/", $cod)) {
                $content = $txt;
                $h = fopen($temp_file, "w");
                fwrite($h, $content);
                fclose($h);
            } else {
                return false;
            }
        }

        $xml = simplexml_load_string($content);
        foreach ($xml->errors->errors as $key => $data) {
            $return["errors"][] = strval($data);
        }
        foreach ($xml->libraries->library as $key => $data) {
            $lib = strval($data["name"]);
            $return["libraries"][$lib] = array(
                "version" => strval($data->version),
                "file"    => strval($data->file),
            );
        }

        return $return;
    }

    public function upload_remote_archive($url)
    {
        $content = file_get_contents($url);

        if ($content) {
            $file_name = basename($url);
            $file_path = TEMPPATH . "trash/" . $file_name;
            $h = fopen($file_path, "w");
            if ($h) {
                fwrite($h, $content);
                fclose($h);
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }
}
