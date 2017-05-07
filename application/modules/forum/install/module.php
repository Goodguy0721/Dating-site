<?php

$module['module'] = 'forum';
$module['install_name'] = 'Forum management';
$module['install_descr'] = 'Managing forum';
$module['version'] = '1.01';

$module['files'] = array(
	array('file', 'read', "application/modules/forum/helpers/forum_helper.php"),
	array('file', 'read', "application/modules/forum/controllers/admin_forum.php"),
	array('file', 'read', "application/modules/forum/controllers/api_forum.php"),
	array('file', 'read', "application/modules/forum/controllers/forum.php"),
	array('file', 'read', "application/modules/forum/install/module.php"),
	array('file', 'read', "application/modules/forum/install/permissions.php"),
	array('file', 'read', "application/modules/forum/install/settings.php"),
	array('file', 'read', "application/modules/forum/install/structure_deinstall.sql"),
	array('file', 'read', "application/modules/forum/install/structure_install.sql"),
	array('file', 'read', "application/modules/forum/models/forum_categories_model.php"),
	array('file', 'read', "application/modules/forum/models/forum_install_model.php"),
	array('file', 'read', "application/modules/forum/models/forum_messages_model.php"),
	array('file', 'read', "application/modules/forum/models/forum_subcategories_model.php"),
	array('file', 'read', "application/modules/forum/views/admin/edit_category.tpl"),
	array('file', 'read', "application/modules/forum/views/admin/edit_message.tpl"),
	array('file', 'read', "application/modules/forum/views/admin/edit_subcategory.tpl"),
	array('file', 'read', "application/modules/forum/views/admin/helper_admin_group_discussions.tpl"),
	array('file', 'read', "application/modules/forum/views/admin/helper_group_discussions.tpl"),
	array('file', 'read', "application/modules/forum/views/admin/list_categories.tpl"),
	array('file', 'read', "application/modules/forum/views/admin/list_messages.tpl"),
	array('file', 'read', "application/modules/forum/views/admin/list_subcategories.tpl"),
	array('file', 'read', "application/modules/forum/views/admin/sorting_categories.tpl"),
	array('file', 'read', "application/modules/forum/views/default/ajax_category_form.tpl"),
	array('file', 'read', "application/modules/forum/views/default/edit_message.tpl"),
	array('file', 'read', "application/modules/forum/views/default/edit_subcategory.tpl"),
	array('file', 'read', "application/modules/forum/views/default/helper_group_discussions.tpl"),
	array('file', 'read', "application/modules/forum/views/default/list_categories.tpl"),
	array('file', 'read', "application/modules/forum/views/default/list_messages.tpl"),
	array('file', 'read', "application/modules/forum/views/default/list_subcategories.tpl"),
	array('dir', 'read', "application/modules/forum/langs")
);

$module['dependencies'] = array(
	'start' => array('version' => '1.03'),
	'menu' => array('version' => '2.03'),
	'moderation' => array('version'=>'1.01'),
	'users' => array('version' => '3.01')
);

$module['linked_modules'] = array(
	'install' => array(
		'menu'			=> 'install_menu',
		'moderation'	=> 'install_moderation',
		'site_map'		=> 'install_site_map',
		'moderators'		=> 'install_moderators'
	),
	'deinstall' => array(
		'menu'			=> 'deinstall_menu',
		'moderation'	=> 'deinstall_moderation',
		'site_map'		=> 'deinstall_site_map',
		'moderators'		=> 'deinstall_moderators'
	)
);