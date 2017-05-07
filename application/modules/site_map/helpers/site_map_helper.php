<?php

if (!function_exists('getSitemap')) {
    function getSitemap()
    {
        $ci = &get_instance();
        $ci->load->model('Site_map_model');
        $url_blocks = $ci->Site_map_model->get_sitemap_links();
        $ci->view->assign("blocks", $url_blocks);

        return $ci->view->fetch('helper_sitemap', null, 'site_map');
    }
}
