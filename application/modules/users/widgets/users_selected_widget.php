<?php

/**
 * Users List Widget Model
 *
 * @package PG_DatingPro
 * @subpackage Widgets
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Users_selected_widget extends Model
{
    /**
     * link to CodeIgniter object
     *
     * @var object
     */
    protected $CI;

    /**
     * Widget guid
     *
     * @var string
     */
    private $gid = 'users_selected_widget';

    /**
     * Module guid
     *
     * @var string
     */
    private $module = 'users';

    /**
     * Widget settings
     *
     * @var array
     */
    protected $settings = array(
        'id_user',
    );

    /**
     * Widget language data
     *
     * @var array
     */
    private $langs_data = array(
        'en' => array(
            'users_selected_widget_name'        => 'Selected users widget',
            'users_selected_widget_description' => 'The widget displays site users manually selected by the site admin',
        ),
        'ru' => array(
            'users_selected_widget_name'        => 'Виджет с назначенными пользователями',
            'users_selected_widget_description' => 'Виджет выводит фотографии пользователей, выбранных вручную администратором сайта',
        ),
    );

    /**
     * Constructor
     *
     * return Wish_list_model object
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('Users_model');
        $this->CI->load->model('Properties_model');
        $this->CI->load->helper('seo');
        $this->CI->load->helper('users');
    }

    /**
     * Generate widget
     *
     * @param array $params widget parameters
     */
    public function generate($params)
    {
        $return = '';

        if (!empty($params['id_user'])) {
            $users = $this->CI->Users_model->get_users_list(1, count($params['id_user']), '', '', $params['id_user']);

            $return .= '
			<style>
				.users-block{overflow: auto;}
				.users-block img{outline: none; background: #FFFFFF; width: 60px; height: 60px; float:left; padding-right: 10px;}
				.users-block .item{padding: 4px; font-size: 11px;}
				.users-block .item:last-child{border: none;}
				.users-block .item .image{float:left;}
				.users-block .item .image a{position: relative; font-size: 0.1px; font-size: 0; display: inline-block; font-size: 11px;}
				.users-block .item .body{
					font-size: 12px;
					font-family: arial;
					font-weight: bold;
				}
				.users-block .item p.headline{clear: both; font-style: italic; margin-bottom: 0px;}
				.users-block .body{position: relative; overflow: hidden; margin-left: 0;}
			</style>
			<div class="users-block" id="users-block">';

            foreach ($users as $user) {
                $seolink = rewrite_link('users', 'view', $user);
                $return .= '
				<div class="user-gallery">
					<div class="item" style="border-bottom: 1px solid #eeeeee;">
						<div class="user">
							<div class="body">
								<div class="image">
									<a href="' . $seolink . '" target="_blank"><img alt="" src="' . $user['media']['user_logo']['thumbs']['middle'] . '" /></a>
								</div>
								<div class="text-overflow"><a href="' . $seolink . '" target="_blank" title="' . $user['output_name'] . '">' . $user['output_name'] . '</a>, ' . $user['age'] . '</div>
								<div class="text-overflow" title="' . $user['location'] . '">' . $user['location'] . '</div>
							</div>
						</div>
					</div></div>';
            }
            $return .= '</div>';
        }

        return $return;
    }

    /**
     * Validate settings
     *
     * @param array $data settings data
     *
     * @return array
     */
    public function validate_settings($data)
    {
        $return = array('errors' => array(), 'data' => array());

        if (isset($data['id_user'])) {
            $return['data']['id_user'] = $data['id_user'];
        }

        return $return;
    }

    /**
     * Return settings form
     *
     * @param array $data settings data
     *
     * @return string
     */
    public function get_settings_form($data)
    {
      $theme = $this->CI->pg_theme->return_active_settings('admin');

      if ($theme['theme'] == 'gentelella') {
          return '
          <div class="form-group">
            <label class="control-label col-sm-3 col-xs-12"></label>
            <div class="col-sm-9 col-xs-12">' . user_select($data['id_user'], '', 'data[settings][id_user]') . '</div>
          </div>';
      } else {
          return user_select($data['id_user'], '', 'data[settings][id_user]');
      }
    }

    /**
     * Return widget info
     *
     * @return array
     */
    public function get_widget_info()
    {
        $data   = array('ajax_get_users_data', 'ajax_get_selected_users');
        $access = $this->CI->pg_module->get_module_method_access('users', 'users', 'ajax_get_users_data');
        if ($access == 2) {
            $change = array('access' => 1);
            $this->CI->db->where_in('method', $data);
            $this->CI->db->where('controller', 'users');
            $this->CI->db->update(MODULES_METHODS_TABLE, $change);
        }

        return array(
            'gid'      => $this->gid,
            'module'   => $this->module,
            'size'     => '300',
            'colors'   => array(
                'background' => 'FFFFFF',
                'border'     => '000000',
                'text'       => '4C4C4C',
                'link'       => '0066FF',
                'block'      => '000000',
            ),
            'settings' => array(
                'id_user' => array(),
            ),
        );
    }

    /**
     * Return widget languages
     *
     * @return array
     */
    public function get_widget_langs($lang_code = null)
    {
        if (is_null($lang_code)) {
            return $this->langs_data;
        }
        if (!isset($this->langs_data[$lang_code])) {
            $lang_code = key($this->langs_data);
        }

        return $this->langs_data[$lang_code];
    }
}
