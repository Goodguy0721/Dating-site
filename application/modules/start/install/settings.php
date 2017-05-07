<?php

$install_settings["admin_items_per_page"] = 50;
$install_settings["index_items_per_page"] = 30;

$install_settings["date_format_date_numeric"] = "[year_2]-[month_with_zero]-[day_with_zero]";
$install_settings["date_format_date_literal"] = "[day_with_zero] [month_full] [year_4]";
$install_settings["date_format_date_time_numeric"] = "[year_4]-[month_with_zero]-[day_with_zero] [hours_24_with_zero]:[minutes_with_zero]:[seconds_with_zero]";
$install_settings["date_format_date_time_literal"] = "[day_with_zero] [month_full] [year_4], [hours_24_with_zero]:[minutes_with_zero]";
$install_settings["date_format_time_numeric"] = "[hours_24_with_zero]:[minutes_with_zero]:[seconds_with_zero]";
$install_settings["date_format_time_literal"] = "[hours_24_with_zero]:[minutes_with_zero]";

if (SOCIAL_MODE) {
    $install_settings["product_version_code"] = '3';
    $install_settings["product_version_name"] = '2016.3';
} else {
    $install_settings["product_version_code"] = '8';
    $install_settings["product_version_name"] = 'Honey';
}

$install_settings["product_version_code_update"] = '';
$install_settings["product_version_name_update"] = '';

$install_settings["product_version_last_update"] = '';

$install_settings["product_order_key"] = '';

$install_settings["fixed_index_page"] = 1;
