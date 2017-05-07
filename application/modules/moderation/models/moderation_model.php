<?php

namespace Pg\Modules\Moderation\Models;

use Pg\Libraries\EventDispatcher;
use Pg\Modules\Moderation\Models\Events\EventModeration;

/**
 * Moderation Model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 * */
/*
 *
 * модель никогда не меняет статуса уже активного элемента
 * каждый вызов add_moderation_item добавляет запись в соотв с типом мод.объекта
 *
 */

define('MODERATION_ITEMS_TABLE', DB_PREFIX . 'moderation_items');

class Moderation_model extends \Model
{
    const MODULE_GID           = 'moderation';
    const EVENT_OBJECT_CHANGED = 'moderation_object_changed';
    const MODULE_TABLE         = MODERATION_ITEMS_TABLE;
    const SORT_DEFAULT         = 'date_add DESC';
    const TYPE_MODERATION_ITEM = 'moderation_item';
    const STATUS_ADDED         = 'added';
    const STATUS_APPROVED      = 'approved';
    const STATUS_DECLINED      = 'declined';
    const STATUS_DELETED       = 'deleted';

    public $ci;
    public $types;
    protected $fields        = array(
        self::MODULE_TABLE => array(
            'id',
            'id_type',
            'id_object',
            'date_add',
        ),
    );
    public $dashboard_events = [
        self::EVENT_OBJECT_CHANGED,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->ci = &get_instance();
        $this->ci->load->model('moderation/models/Moderation_type_model');
        $this->ci->load->model('moderation/models/Moderation_badwords_model');
    }

    public function get_moderation_type($type_name)
    {
        if (!isset($this->types[$type_name])) {
            $type_data = $this->ci->Moderation_type_model->get_type_by_name($type_name);
            if (!is_array($type_data) || !count($type_data)) {
                return false;
            }
            $this->types[$type_data["id"]] = $type_data;
            $this->types[$type_name]       = $type_data;
        }

        return $this->types[$type_name];
    }

    public function get_moderation_type_by_id($type_id)
    {
        if (!isset($this->types[$type_id])) {
            $type_data = $this->ci->Moderation_type_model->get_type_by_id($type_id);
            if (!is_array($type_data) || !count($type_data)) {
                return false;
            }
            $this->types[$type_id]           = $type_data;
            $this->types[$type_data["name"]] = $type_data;
        }

        return $this->types[$type_id];
    }

    public function get_moderation_type_status($type_name)
    {
        $type_data = $this->get_moderation_type($type_name);
        switch ($type_data["mtype"]) {
            case "0": $status = 1;
                break;
            case "1": $status = 1;
                break;
            case "2": $status = 0;
                break;
        }

        return $status;
    }

    public function approve($item_id)
    {
        $item_data = $this->get_moderation_item($item_id);
        $type_data = $this->get_moderation_type_by_id($item_data["id_type"]);

        if ($type_data["model"] && $type_data["module"] && $type_data["method_set_status"]) {
            $model_name = ucfirst($type_data["model"]);
            $model_path = strtolower($type_data["module"] . "/models/") . $model_name;
            $this->ci->load->model($model_path);
            $this->ci->{$model_name}->{$type_data["method_set_status"]}($item_data["id_object"],
                1);
        }

        $this->delete_moderation_item_by_id($item_id);

        $this->sendEvent(self::EVENT_OBJECT_CHANGED,
            [
            'id' => $item_id,
            'type' => self::TYPE_MODERATION_ITEM,
            'status' => self::STATUS_APPROVED,
        ]);

        $this->ci->load->model('menu/models/Indicators_model');
        $this->ci->Indicators_model->delete('new_moderation_item',
            $item_data["id_object"], true);
    }

    public function decline($item_id)
    {
        $item_data = $this->get_moderation_item($item_id);
        $type_data = $this->get_moderation_type_by_id($item_data["id_type"]);

        if ($type_data["model"] && $type_data["module"] && $type_data["method_set_status"] && $type_data["allow_to_decline"]) {
            $model_name = ucfirst($type_data["model"]);
            $model_path = strtolower($type_data["module"] . "/models/") . $model_name;
            $this->ci->load->model($model_path);
            $this->ci->{$model_name}->{$type_data["method_set_status"]}($item_data["id_object"],
                0);
        }

        $this->delete_moderation_item_by_id($item_id);

        $this->sendEvent(self::EVENT_OBJECT_CHANGED,
            [
            'id' => $item_id,
            'type' => self::TYPE_MODERATION_ITEM,
            'status' => self::STATUS_DECLINED,
        ]);

        $this->ci->load->model('menu/models/Indicators_model');
        $this->ci->Indicators_model->delete('new_moderation_item',
            $item_data["id_object"], true);
    }

    public function add_moderation_item($type_name, $obj_id)
    {
        $type_data = $this->get_moderation_type($type_name);
        if ($type_data["mtype"] == 0) {
            return false;
        }
        $type_id = intval($type_data["id"]);

        $item_id = $this->getModerationItemId($type_name, $obj_id);
        if ($item_id > 0) {
            $attrs["date_add"] = date("Y-m-d H:i:s");
            $this->ci->db->where('id', $item_id);
            $this->ci->db->update(MODERATION_ITEMS_TABLE, $attrs);
        } else {
            $attrs["date_add"]  = date("Y-m-d H:i:s");
            $attrs["id_type"]   = $type_id;
            $attrs["id_object"] = $obj_id;
            $this->ci->db->insert(MODERATION_ITEMS_TABLE, $attrs);
            $item_id            = $this->ci->db->insert_id();
        }

        $this->sendEvent(self::EVENT_OBJECT_CHANGED,
            [
            'id' => $item_id,
            'type' => self::TYPE_MODERATION_ITEM,
            'status' => self::STATUS_ADDED,
        ]);

        return true;
    }

    public function sendEvent($event_gid, $event_data)
    {
        $event_data['module'] = self::MODULE_GID;
        $event_data['action'] = $event_gid;

        $event = new EventModeration();
        $event->setData($event_data);

        $event_handler = EventDispatcher::getInstance();
        $event_handler->dispatch($event_gid, $event);
    }

    public function isset_moderation_item($type_name, $obj_id)
    {
        $type_data = $this->get_moderation_type($type_name);
        $type_id   = intval($type_data["id"]);

        $this->ci->db->select('COUNT(*) AS cnt')
            ->from(MODERATION_ITEMS_TABLE)
            ->where('id_type', $type_id)
            ->where('id_object', $obj_id);
        $result = $this->ci->db->get()->result();
        if (!empty($result) && intval($result[0]->cnt)) {
            return true;
        } else {
            return false;
        }
    }

    private function getModerationItemId($type_name, $obj_id)
    {
        $type_data = $this->get_moderation_type($type_name);
        $type_id   = intval($type_data["id"]);

        $this->ci->db->select('id')
            ->from(MODERATION_ITEMS_TABLE)
            ->where('id_type', $type_id)
            ->where('id_object', $obj_id);
        $result = $this->ci->db->get()->result();
        if (!empty($result)) {
            return intval($result[0]->id);
        } else {
            return false;
        }
    }

    public function get_moderation_item($id)
    {
        $id = intval($id);
        if (!$id) {
            return false;
        }
        $this->ci->db->select('id, id_type, id_object, date_add')->from(MODERATION_ITEMS_TABLE)->where("id",
            $id);
        $result = $this->ci->db->get()->result();
        if (!empty($result)) {
            return get_object_vars($result[0]);
        } else {
            return false;
        }
    }

    public function delete_moderation_item_by_id($id)
    {
        $id = intval($id);
        if (!$id) {
            return false;
        }

        $this->ci->db->delete(MODERATION_ITEMS_TABLE, array('id' => $id));

        $this->sendEvent(self::EVENT_OBJECT_CHANGED,
            [
            'id' => $id,
            'type' => self::TYPE_MODERATION_ITEM,
            'status' => self::STATUS_DELETED,
        ]);

        return;
    }

    public function delete_moderation_items_by_type_id($type_id)
    {
        if (!intval($type_id)) {
            return false;
        }

        $results = $this->ci->db->select('id')
            ->from(MODERATION_ITEMS_TABLE)
            ->where('id_type', $type_id)
            ->get()
            ->result_array();

        $ids = [];

        if (!empty($results) && is_array($results)) {
            foreach ($results as $result) {
                $ids[] = $result['id'];
            }
            unset($results);
        }

        $this->ci->db->where('id_type', $type_id)
            ->delete(MODERATION_ITEMS_TABLE);

        if (!empty($ids)) {
            $this->sendEvent(self::EVENT_OBJECT_CHANGED,
                [
                'id' => $ids,
                'type' => self::TYPE_MODERATION_ITEM,
                'status' => self::STATUS_DELETED,
            ]);
        }

        return;
    }

    public function delete_moderation_item_by_obj($type_name, $obj_id)
    {
        $type_data = $this->get_moderation_type($type_name);
        $type_id   = intval($type_data["id"]);

        if (is_array($obj_id) && count($obj_id)) {
            $obj_id_arr = $obj_id;
        } elseif (is_numeric($obj_id) && $obj_id > 0) {
            $obj_id_arr[] = intval($obj_id);
        } else {
            return false;
        }

        $results = $this->ci->db->select('id')
            ->from(MODERATION_ITEMS_TABLE)
            ->where('id_type', $type_id)
            ->where_in('id_object', $obj_id_arr)
            ->get()
            ->result_array();

        $ids = [];

        if (!empty($results) && is_array($results)) {
            foreach ($results as $result) {
                $ids[] = $result['id'];
            }
            unset($results);
        }

        $this->ci->db->where('id_type', $type_id)
            ->where_in('id_object', $obj_id_arr)
            ->delete(MODERATION_ITEMS_TABLE);

        if (!empty($ids)) {
            $this->sendEvent(self::EVENT_OBJECT_CHANGED,
                [
                'id' => $ids,
                'type' => self::TYPE_MODERATION_ITEM,
                'status' => self::STATUS_DELETED,
            ]);
        }

        return;
    }

    public function get_moderation_list_count($type_name = "")
    {
        if ($type_name) {
            $type_data = $this->get_moderation_type($type_name);
            $this->ci->db->where('id_type', $type_data["id"]);
        }
        $this->ci->db->select('COUNT(*) AS cnt')->from(MODERATION_ITEMS_TABLE);
        $result = $this->ci->db->get()->result();
        if (!empty($result)) {
            return intval($result[0]->cnt);
        } else {
            return 0;
        }
    }

    public function get_moderation_list($type_name = "", $page = null, $list_per_page
    = null, $parse_html = true)
    {
        $this->ci->db->select('id, id_type, id_object, date_add')->from(MODERATION_ITEMS_TABLE);
        if ($type_name) {
            $type_data = $this->get_moderation_type($type_name);
            $this->ci->db->where('id_type', $type_data["id"]);
        }

        $this->ci->db->order_by("date_add DESC");
        if (!is_null($page) && !is_null($list_per_page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->ci->db->limit($list_per_page, $list_per_page * ($page - 1));
        }

        $result = $this->ci->db->get()->result();
        if (empty($result)) {
            return false;
        }

        foreach ($result as $item) {
            $type                           = $this->get_moderation_type_by_id($item->id_type);
            $item->type_name                = $type["name"];
            $item->type                     = $type;
            $object_ids[$item->type_name][] = $item->id_object;
            if (strlen($type["view_link"])) {
                $item->view_link = site_url() . $type["view_link"] . $item->id_object;
            }
            if (strlen($type["edit_link"])) {
                $item->edit_link = site_url() . $type["edit_link"] . $item->id_object;
            }
            if (strlen($type["method_delete_object"])) {
                $item->avail_delete = true;
            }
            if (strlen($type["method_mark_adult"])) {
                $item->mark_adult = true;
            }
            if (strlen($type["method_set_status"]) && intval($type["allow_to_decline"])) {
                $item->avail_decline = true;
            }
            $list[] = get_object_vars($item);
        }

        if ($parse_html && isset($object_ids) && is_array($object_ids)) {
            foreach ($object_ids as $type_name => $ids) {

                /// получем параметры типа
                $type = $this->types[$type_name];

                /// подключаем модель
                $model_name = ucfirst($type["model"]);
                $model_path = strtolower($type["module"] . "/models/") . $model_name;
                $this->ci->load->model($model_path);

                /// получаем данные обектов по ids (возвращается массив: id_object => object_data)
                $objects_data[$type_name] = $this->ci->{$model_name}->{$type["method_get_list"]}($ids);
            }

            foreach ($list as $key => $item) {
                if (isset($objects_data[$item["type_name"]][$item["id_object"]])) {
                    /// assign в шаблон, складываем html в переменную
                    $this->ci->view->assign('data',
                        $objects_data[$item["type_name"]][$item["id_object"]]);
                    $list[$key]["html"] = $this->ci->view->fetch($item["type"]["template_list_row"],
                        'admin', $item["type"]["module"]);
                }
            }
        }

        return $list;
    }

    public function getModerationByIds($ids)
    {
        return
                $this->ci->db->select(implode(',',
                        $this->fields[self::MODULE_TABLE]))
                ->from(self::MODULE_TABLE)
                ->where_in('id', $ids)
                ->order_by(self::SORT_DEFAULT)
                ->get()
                ->result_array();
    }

    public function formatModerationObjects($data, $is_generate_html = true, $template
    = '')
    {
        $types = $this->ci->Moderation_type_model->get_types();
        foreach ($types as $type) {
            $this->types[$type['id']]   = $type;
            $this->types[$type['name']] = $type;
        }

        $group_object_ids_by_type = [];

        foreach ($data as $key => $item) {
            if (!isset($this->types[$item['id_type']])) {
                $data[$key] = [];
                continue;
            }

            $item['type'] = $this->types[$item['id_type']];

            $item['type_name'] = $item['type']['name'];

            if (strlen($item['type']['view_link'])) {
                $item['view_link'] = site_url() . $item['type']['view_link'] . $item['id_object'];
            }

            if (strlen($item['type']['edit_link'])) {
                $item['edit_link'] = site_url() . $item['type']['edit_link'] . $item['id_object'];
            }

            if (strlen($item['type']['method_delete_object'])) {
                $item['avail_delete'] = true;
            }

            if (strlen($item['type']['method_mark_adult'])) {
                $item['mark_adult'] = true;
            }

            if (strlen($item['type']['method_set_status']) && intval($item['type']['allow_to_decline'])) {
                $item['avail_decline'] = true;
            }

            $group_object_ids_by_type[$item['type_name']][] = $item['id_object'];

            $data[$key] = $item;
        }

        if ($is_generate_html && !empty($group_object_ids_by_type)) {
            $objects_data = [];

            foreach ($group_object_ids_by_type as $type_name => $ids) {
                $type = $this->types[$type_name];

                $model_name = ucfirst($type["model"]);
                $model_path = strtolower($type["module"] . "/models/") . $model_name;
                $this->ci->load->model($model_path);

                $objects_data[$type_name] = $this->ci->{$model_name}->{$type["method_get_list"]}($ids);
               
            }

            foreach ($data as $key => $item) {
                if (isset($objects_data[$item["type_name"]][$item["id_object"]])) {
                    $this->ci->view->assign('template', $template);
                    $objects_data[$item["type_name"]][$item["id_object"]]['dashboard_status'] = $item["dashboard_status"];
                    
                    $this->ci->view->assign('data', $objects_data[$item["type_name"]][$item["id_object"]]);
                    if (isset($objects_data[$item["type_name"]][$item["id_object"]]['admin_link'])) {
                        $data[$key]["admin_link"] = $objects_data[$item["type_name"]][$item["id_object"]]['admin_link'];
                    }
                    
                    $data[$key]["html"] = $this->ci->view->fetch($item["type"]["template_list_row"], 'admin', $item["type"]["module"]);
                } else {
                    $data[$key]["html"] = '';
                }
            }
        }

        return $data;
    }

    public function convertToListByIds($data)
    {
        $data_by_ids = [];

        foreach ($data as $value) {
            $data_by_ids[$value['id']] = $value;
        }

        return $data_by_ids;
    }

    public function formatDashboardRecords($data)
    {
        $data = $this->formatModerationObjects($data, true, 'dashboard');

        foreach ($data as $key => $value) {
            $this->ci->view->assign('data', $value);
            $data[$key]['content'] = $this->ci->view->fetch('dashboard',
                'admin', 'moderation');
        }

        return $data;
    }

    public function getDashboardData($object_id, $status)
    {
        if ($status != self::STATUS_ADDED) {
            return false;
        }
        $object = $this->get_moderation_item($object_id);
        $type   = $this->get_moderation_type_by_id($object['id_type']);
        if (ucfirst($type['module']) . '_model' == $type['model']) {
            $model_path = $type['model'];
        } else {
            $model_path = $type['module'] . '/models/' . $type['model'];
        }
        $this->ci->load->model($model_path);
        if (method_exists($this->ci->{$type['model']}, 'getDashboardOptions')) {
            $object = array_merge($object,
                $this->ci->{$type['model']}->getDashboardOptions($object['id_object']));
        } else {
            $object['dashboard_header']      = 'header_moderation_object';
            $object['dashboard_action_link'] = 'admin/moderation';
        }
        return $object;
    }
}