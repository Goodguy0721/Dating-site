<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config["login_settings"] = array(
    'admin' => array(
        'login'    => "admin",
        'password' => "admin1",
    ),
    'user' => array(
		'login' => "will@mail.com",
        'password' => "123456",
    ),
);

if(SOCIAL_MODE) {
    $config['copyright'] = '&copy;&nbsp;2000-' . date('Y') . '&nbsp;<a href="http://www.pilotgroup.net">PilotGroup.NET</a> Powered by <a href="http://www.socialbiz.pro/">PG SocialBiz</a>';
} else {
    $config['copyright'] = '&copy;&nbsp;2000-' . date('Y') . '&nbsp;<a href="http://www.pilotgroup.net">PilotGroup.NET</a> Powered by <a href="http://www.datingpro.com/">PG Dating Pro</a>';
}

