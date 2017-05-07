<?php

$module['module'] = 'blogs';
$module['install_name'] = 'Blogs management';
$module['install_descr'] = 'Managing blogs';
$module['version'] = '1.01';

$module['files'] = array(
	array('file', 'read', "application/modules/blogs/helpers/blogs_helper.php"),
	array('file', 'read', "application/modules/blogs/controllers/admin_blogs.php"),
	array('file', 'read', "application/modules/blogs/controllers/api_blogs.php"),
	array('file', 'read', "application/modules/blogs/controllers/blogs.php"),
	array('file', 'read', "application/modules/blogs/install/module.php"),
	array('file', 'read', "application/modules/blogs/install/permissions.php"),
	array('file', 'read', "application/modules/blogs/install/settings.php"),
	array('file', 'read', "application/modules/blogs/install/structure_deinstall.sql"),
	array('file', 'read', "application/modules/blogs/install/structure_install.sql"),
	array('file', 'read', "application/modules/blogs/models/blogs_install_model.php"),
	array('file', 'read', "application/modules/blogs/models/blogs_model.php"),
	array('file', 'read', "application/modules/blogs/views/admin/edit_categories.tpl"),
	array('file', 'read', "application/modules/blogs/views/admin/edit_comment.tpl"),
	array('file', 'read', "application/modules/blogs/views/admin/edit_post.tpl"),
	array('file', 'read', "application/modules/blogs/views/admin/list_blogs.tpl"),
	array('file', 'read', "application/modules/blogs/views/admin/list_categories.tpl"),
	array('file', 'read', "application/modules/blogs/views/admin/list_comments.tpl"),
	array('file', 'read', "application/modules/blogs/views/admin/list_posts.tpl"),
	array('file', 'read', "application/modules/blogs/views/admin/view_post.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/add_blog_form.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/block_blogs_list.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/block_posts_list.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/blog_menu.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/calendar.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/categories.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/edit_blog.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/edit_post.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/friends_blog.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/helper_blog_button.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/my_blog.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/tag_search_result.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/view_blog.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/view_category.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/view_post.tpl"),
	array('file', 'read', "application/modules/blogs/views/default/wall_events_posts.tpl"),
	array('dir', 'read', "application/modules/blogs/langs")
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