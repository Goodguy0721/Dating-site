<?php

namespace Pg\Modules\Fast_navigation\Models;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!defined('FAST_NAVIGATION_TABLE')) {
    define('FAST_NAVIGATION_TABLE', DB_PREFIX . 'fast_navigation');
}
if (!defined('MENU_ITEMS_TABLE')) {
    define('MENU_ITEMS_TABLE', DB_PREFIX . 'menu_items');
}

class Fast_navigation_model extends \Model
{
    const MODULE_GID = 'fast_navigation';
    const DICTS      = 'vendor/nqxcode/phpmorphy/dicts/';

    protected $ci;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->db = &$this->ci->db;
    }

    public function getActiveMenuLinks()
    {
        return $this->db->select()
                ->distinct("link")
                ->from(MENU_ITEMS_TABLE)
                ->where("status", 1)
                ->like("link", "admin")
                ->order_by('sorter ASC')
                ->get()->result_array();
    }

    public function validNavigationData($data)
    {
        $return                   = ["errors" => [], "data" => []];
        $return['data']['module'] = trim(strip_tags($data['module']));
        $return['data']['url']    = trim(strip_tags($data['url']));

        if (empty($return['data']['url'])) {
            $return['errors']['url'] = l('not_found', self::MODULE_GID);
            ;
        }

        $module                      = explode('/', $return['data']['url']);
        $return['data']['module']    = $module[1];
        $return['data']['keywords']  = trim(strip_tags($data['keywords']));
        $return['data']['lang_code'] = trim(strip_tags($data['lang_code']));

        return $return;
    }

    public function saveNavigationData($data)
    {
        $attrs = [
            'id' => '',
            'module' => $data['module'],
            'url' => $data['url'],
            'keywords' => $data['keywords'],
            'lang_code' => $data['lang_code'],
        ];

        $this->db->insert(FAST_NAVIGATION_TABLE, $attrs);
        return $attrs['url'];
    }

    public function searchWordInKeywords($where)
    {
        return $this->db->distinct()
                ->select()
                ->from(FAST_NAVIGATION_TABLE)
                ->where('lang_code', $this->ci->pg_language->languages[$this->ci->pg_language->current_lang_id]['code'])
                ->where($where, null, false)
                ->group_by('url')
                ->get()->result_array();
    }

    public function validSearchData($data)
    {
        $return    = ['lang', 'data' => [], 'errors' => []];
        $data      = mb_strtoupper(trim(strip_tags($data)), 'UTF-8');
        $lang_code = $this->ci->pg_language->languages[$this->ci->pg_language->current_lang_id]['code'];
        switch ($lang_code) {
            case 'ru': {
                    $return['lang'] = 'ru_RU';
                    $data           = iconv("UTF-8", "Windows-1251", $data);
                    $data           = preg_split('/[^а-яА-Я]+/', $data, -1,
                        PREG_SPLIT_NO_EMPTY);
                    $data           = $this->iconvArray("Windows-1251", "UTF-8",
                        $data);
                    break;
                }
            case 'en': {
                    $return['lang'] = 'en_EN';
                    $data           = preg_split('/[^a-zA-Z]+/', $data, -1,
                        PREG_SPLIT_NO_EMPTY);
                    break;
                }
            default: {
                    $return['lang'] = $lang_code . '_' . mb_strtoupper($lang_code);
                    $data           = preg_split("/[\s,]+/", $data,
                        PREG_SPLIT_NO_EMPTY);
                    if (!file_exists(SITE_PHYSICAL_PATH . self::DICTS . $return['lang'])) {
                        $replace_lang     = [
                            'lang' => $this->ci->pg_language->languages[$this->ci->pg_language->current_lang_id]['name'],
                            'path' => SITE_PHYSICAL_PATH . self::DICTS . $return['lang'],
                        ];
                        $return['errors'] = str_replace(['[lang]', '[path]'],
                            $replace_lang,
                            l('error_lang_pack', self::MODULE_GID));
                    }
                    break;
                }
        }
        if (empty($data)) {
            $return['errors'] = l('not_found', self::MODULE_GID);
        }
        $return['data'] = $data;
        return $return;
    }

    public function getRootWord($word, $lang)
    {
        $dir  = SITE_PHYSICAL_PATH . self::DICTS . $lang;
        $opts = ['storage' => PHPMORPHY_STORAGE_FILE];

        try {
            $morphy = new \phpMorphy($dir, $lang, $opts);
        } catch (phpMorphy_Exception $e) {
            die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
        }

        if ($lang == 'ru_RU') {
            $word = iconv("UTF-8", "CP1251", $word);
        }
        $return = $morphy->getBaseForm($word);
        if ((strlen($word) < 4) or ( !$return)) {
            $return = [$word];
        }

        if ($lang == 'ru_RU') {
            $return = $this->iconvArray("CP1251", "UTF-8", $return);
        }

        return $return;
    }

    public function iconvArray($incode, $outcode, $data_array)
    {
        $return = [];
        foreach ($data_array as $data) {
            $return[] = iconv($incode, $outcode, $data);
        }
        return $return;
    }

    public function getSearchResult($search_data)
    {
        $where = '';
        foreach ($search_data as $rootword) {
            if (!empty($where)) {
                $where .= "OR ";
            }
            $where .= " (module LIKE " . $this->db->escape("%" . $rootword . "%") . " OR keywords LIKE " . $this->db->escape("%" . $rootword . "%") . ")";
        }
        $search = $this->searchWordInKeywords($where);
        if (!empty($search)) {
            return $this->formatSearchResult($search);
        }

        return [];
    }

    public function formatSearchResult(array $data)
    {
        $return = [];
        foreach ($data as $words) {
               $return[$words['module']][] = $words;
        }
        return $return;
    }

    public function getRootWords(array $search_data)
    {
        $return = [];
        foreach ($search_data['data'] as $word) {
            $return = array_merge($return,
                $this->getRootWord($word, $search_data['lang']));
        }
        return $return;
    }

    /**
     * Collection of keywords across all modules
     *
     * @return void
     */
    public function dataСollection()
    {
        $this->db->truncate(FAST_NAVIGATION_TABLE);
        $result = $this->getActiveMenuLinks();
        foreach ($this->ci->pg_language->languages as $lang) {
            foreach ($result as $res) {
                $attrs = [
                    'url' => $res['link'],
                    'keywords' => $this->ci->pg_language->get_string('menu_lang_' . $res['menu_id'],
                    'menu_item_' . $res["id"], $lang['id']),
                    'lang_code' => $lang['code']
                ];
                $valid_data = $this->validNavigationData($attrs);
                if (empty($valid_data['errors'])) {
                    $this->saveNavigationData($valid_data['data']);
                }
            }
        }
    }

    /**
     * Cron updater table
     *
     * @return type
     */
    public function updateFastNavigationCron()
    {
        return $this->dataСollection();
    }

}