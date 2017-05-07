<?php

if(!function_exists('blog_button')) {

	function blog_button($params) {
		$ci = &get_instance();
		$tpl = &$ci->view;

		$user_id = $ci->session->userdata('user_id');
		if ($params['user_id']!=$user_id){
			$ci->load->model('Blogs_model');
			$params['where']['user_id'] = $params['user_id'];
			$blog = $ci->Blogs_model->get_blogs_list(null, null, array(), $params);
			if (empty($blog)) return '';
			
			$tpl->assign('blog_id', $blog[0]['id']);
			
		}
		return $tpl->fetch('helper_blog_button', 'user', 'blogs');
	}

}