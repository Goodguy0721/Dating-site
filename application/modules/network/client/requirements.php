<?php

$return = array('data' => array(), 'result' => true);
$check_list = array(
    array(
        'func' => function () {
            return (bool) (string) extension_loaded('pcntl');
        },
        'msg' => 'PCNTL extension is loaded',
    ),
    array(
        'func' => function () {
            return (bool) (string) function_exists('pcntl_fork');
        },
        'msg' => 'pcntl_fork() function is available',
    ),
    array(
        'func' => function () {
            return (bool) (string) function_exists('pcntl_signal');
        },
        'msg' => 'pcntl_signal() function is available',
    ),
    array(
        'func' => function () {
            return (bool) (string) function_exists('pcntl_signal_dispatch');
        },
        'msg' => 'pcntl_signal_dispatch() function is available',
    ),
    array(
        'func' => function () {
            return (bool) (string) function_exists('posix_setsid');
        },
        'msg' => 'posix_setsid() function is available',
    ),
    array(
        'func' => function () {
            return (bool) (string) function_exists('posix_kill');
        },
        'msg' => 'posix_kill() function is available',
    ),
);

foreach ($check_list as $ckeck) {
    $suit = $ckeck['func']();
    $return['data'][] = array(
        'name'   => $ckeck['msg'],
        'value'  => $suit ? 'Yes' : 'No',
        'result' => $suit,
    );
    $return['result'] = $return['result'] && $suit;
}

echo json_encode($return);
