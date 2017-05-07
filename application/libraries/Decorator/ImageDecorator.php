<?php

namespace Pg\Libraries\Decorator;

use Pg\Libraries\Decorator;

class ImageDecorator extends Decorator
{
    public static function profileLogo($src, $alt = '', $title = '', $class = '')
    {
        if (empty($title)) {
            $title = $alt;
        }

        if (empty($alt)) {
            $alt = $title;
        }

        $ci = &get_instance();
        $ci->view->assign('src', $src);
        $ci->view->assign('alt', $alt);
        $ci->view->assign('title', $title);
        $ci->view->assign('class', $class);

        return $ci->view->fetch('decorator_user_logo', 'user', 'birthdays');
    }
}
