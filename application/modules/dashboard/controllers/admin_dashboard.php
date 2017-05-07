<?php

namespace Pg\Modules\Dashboard_Wall\Controllers;

use Pg\Libraries\View;

class Admin_Dashboard_Wall extends \Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Dashboard_wall_model");
    }
}
