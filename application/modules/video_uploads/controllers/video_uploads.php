<?php

namespace Pg\Modules\Video_uploads\Controllers;

/**
 * Video uploads user side controller
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
class Video_Uploads extends \Controller
{
    public function index()
    {
        $this->embed_test();
    }

    public function embed_test()
    {
        $this->load->library('VideoEmbed');

        if ($this->input->post('btn_save')) {
            $data["embed"] = $this->input->post('embed');
            $this->view->assign('data', $data);

            $video_data = $this->videoembed->get_video_data($data["embed"]);
            if ($video_data !== false) {
                $video_data["string_to_save"] = $this->videoembed->get_string_from_video_data($video_data);
                $this->view->assign('video_data', $video_data);

                $embed = $this->videoembed->get_embed_code($video_data);
                $this->view->assign('embed', $embed);
            }
        }
        $services = $this->videoembed->get_services($services);
        $this->view->assign('services', $services);

        $this->view->render('embed_test');
    }
}
