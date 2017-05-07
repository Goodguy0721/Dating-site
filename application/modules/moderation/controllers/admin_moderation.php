<?php

namespace Pg\Modules\Moderation\Controllers;

use Pg\Modules\Moderation\Models\Moderation_model;
use Pg\Libraries\View;

/**
 * Moderation admin side controller
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
 **/
class Admin_Moderation extends \Controller
{
    /**
     * Constructor
     *
     * @return Admin_Moderation
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Moderation_model');
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'moderation_menu_item');
    }

    /**
     *
     */
    public function index($type_name = "all", $page = 1)
    {
        $page = intval($page) ? intval($page) : 1;
        $type_name = (empty($type_name)) ? "all" : $type_name;
        $moder_type_name = (!empty($type_name) && $type_name != 'all') ? $type_name : "";

        $count = $this->Moderation_model->get_moderation_list_count($moder_type_name);
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $count, $items_on_page);

        $_SESSION["mobjects_list"]["type_name"] = $type_name;
        $_SESSION["mobjects_list"]["page"] = $page;

        if ($count > 0) {
            // get tags
            $list = $this->Moderation_model->get_moderation_list($moder_type_name, $page, $items_on_page);
            $this->view->assign('list', $list);
        }

        $this->load->helper("navigation");
        $page_data = get_admin_pages_data(site_url() . "admin/moderation/index/" . $type_name . "/", $count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('type_name', $type_name);

        $moder_types = $this->Moderation_type_model->get_types();
        if (is_array($moder_types) && count($moder_types)) {
            foreach ($moder_types as $key => $mtype) {
                $moder_types[$key]["count"] = $this->Moderation_model->get_moderation_list_count($mtype["name"]);
            }
        }
        $this->view->assign('moder_types', $moder_types);

        $this->view->setHeader(l('admin_header_moderation_managment', 'moderation'));
        $this->view->render('admin_moder_list');
    }

    /*
    * Удаляет объект по записи в таблице модераций
    *
    */
    public function delete_object($id_entry)
    {
        if (intval($id_entry)) {
            $item_data = $this->Moderation_model->get_moderation_item($id_entry);
            $object_id = $item_data["id_object"];
            $type_data = $this->Moderation_model->get_moderation_type_by_id($item_data["id_type"]);

            if ($object_id && $type_data["model"] && $type_data["module"] && $type_data["method_delete_object"]) {
                /// подключаем модель
                $model_name = ucfirst($type_data["model"]);
                $model_path = strtolower($type_data["module"] . "/models/") . $model_name;
                $this->load->model($model_path);

                /// удаляем объект
                $this->{$model_name}->{$type_data["method_delete_object"]}($object_id);

                //// удаляем запись в модерации (вдруг осталась после делейта объекта)
                $this->Moderation_model->delete_moderation_item_by_id($id_entry);
            }
        }
        redirect(site_url() . "admin/moderation/index/" . $_SESSION["mobjects_list"]["type_name"] . "/" . $_SESSION["mobjects_list"]["page"]);

        return;
    }

    /*
    * Помечает контент только для взрослых
    *
    */
    public function mark_adult_object($id_entry)
    {
        if (intval($id_entry)) {
            $item_data = $this->Moderation_model->get_moderation_item($id_entry);
            $object_id = $item_data["id_object"];
            $type_data = $this->Moderation_model->get_moderation_type_by_id($item_data["id_type"]);

            if ($object_id && $type_data["model"] && $type_data["module"] && $type_data["method_mark_adult"]) {
                /// подключаем модель
                $model_name = ucfirst($type_data["model"]);
                $model_path = strtolower($type_data["module"] . "/models/") . $model_name;
                $this->load->model($model_path);

                /// удаляем объект
                $this->{$model_name}->{$type_data["method_mark_adult"]}($object_id);

                $this->load->model('menu/models/Indicators_model');
                $this->Indicators_model->delete('new_moderation_item', $object_id, true);
            }
        }
        $this->system_messages->addMessage(View::MSG_SUCCESS, l("file_adulted", "moderation"));
        $this->approve($id_entry);

        return;
    }

    /*
    * Удаляет запись в таблице модераций
    *
    */
    public function delete($id_entry)
    {
        if (intval($id_entry)) {
            $this->Moderation_model->delete_moderation_item_by_id($id_entry);
        }
        redirect(site_url() . "admin/moderation/index/" . $_SESSION["mobjects_list"]["type_name"] . "/" . $_SESSION["mobjects_list"]["page"]);

        return;
    }

    public function approve($id_entry)
    {
        if ((int)$id_entry == 0) {
            show_404();
        }
            
        $this->Moderation_model->approve($id_entry);
            
        $this->system_messages->addMessage(View::MSG_SUCCESS, l("status_approved", "moderation"));
        redirect(site_url() . "admin/moderation/index/" . $_SESSION["mobjects_list"]["type_name"] . "/" . $_SESSION["mobjects_list"]["page"]);
    }

    public function decline($id_entry)
    {
        if ((int)$id_entry == 0) {
            show_404();
        }
           
        $this->Moderation_model->decline($id_entry);
            
        $this->system_messages->addMessage(View::MSG_SUCCESS, l("status_declined", "moderation"));
        redirect(site_url() . "admin/moderation/index/" . $_SESSION["mobjects_list"]["type_name"] . "/" . $_SESSION["mobjects_list"]["page"]);
    }

    public function settings()
    {
        $moder_types = $this->Moderation_type_model->get_types();
        if (is_array($moder_types) && count($moder_types)) {
            foreach ($moder_types as $key => $mtype) {
                $moder_types[$key]["count"] = $this->Moderation_model->get_moderation_list_count($mtype["name"]);
            }
        }
        $this->view->assign('moder_types', $moder_types);
        $this->view->assign('form_save_link', site_url() . "admin/moderation/settings_save");

        $this->view->setHeader(l('admin_header_moderation_settings_managment', 'moderation'));
        $this->view->render('admin_moder_settings');
    }

    public function settings_save()
    {
        $mtype = $this->input->post("mtype");
        $types_id = $this->input->post("type_id");
        $check_badwords = $this->input->post("check_badwords");
        if (is_array($mtype) && count($mtype) > 0) {
            foreach ($types_id as $type_id) {
                $type_id = intval($type_id);
                if ($type_id) {
                    $attrs["mtype"] = isset($mtype[$type_id]) ? strval(intval($mtype[$type_id])) : -1;
                    $attrs["check_badwords"] = isset($check_badwords[$type_id]) ? intval($check_badwords[$type_id]) : 0;
                    $this->Moderation_type_model->save_type($type_id, $attrs);
                }
            }
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_save_settings', 'moderation'));
        }
        redirect(site_url() . "admin/moderation/settings");
    }

    public function badwords($type = "")
    {
        if ($type == "add" && isset($_POST) && !empty($_POST)) {
            $word = $this->input->post("word", true);
            $errors = $this->Moderation_badwords_model->set_badword($word);
            if (count($errors)) {
                $this->system_messages->addMessage(View::MSG_ERROR, $errors);
            }
        }

        if ($type == "check_text" && isset($_POST) && !empty($_POST)) {
            $check_data["text"] = $this->input->post("text");
            $check_data["modified"] = $this->Moderation_badwords_model->search_in_text($check_data["text"]);
            $this->view->assign('check_data', $check_data);
        }
        $this->view->assign('type', $type);

        $badwords = $this->Moderation_badwords_model->get_badwords();
        $this->view->assign('badwords', $badwords);

        $this->view->setHeader(l('admin_header_moderation_badwords_managment', 'moderation'));
        $this->view->render('admin_moder_badwords');
    }

    public function delete_badword($id)
    {
        $id = intval($id);
        if ($id) {
            $this->Moderation_badwords_model->delete_badword($id);
        }
        redirect(site_url() . "admin/moderation/badwords");
    }
}
