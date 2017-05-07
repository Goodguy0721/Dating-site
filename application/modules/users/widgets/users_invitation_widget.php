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
class Users_invitation_widget extends Model
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
    private $gid = 'users_invitation_widget';

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
            'users_invitation_widget_text'        => 'would like to chat with you',
            'users_invitation_widget_name'        => 'Chat invitation widget',
            'users_invitation_widget_description' => 'The widget randomly displays users selected by the site admin, in a pop-up window',
            'users_invitation_widget_start_chat'  => 'Start chat',
        ),
        'ru' => array(
            'users_invitation_widget_text'        => 'хочет пообщаться с тобой!',
            'users_invitation_widget_name'        => 'Виджет с приглашением в чат',
            'users_invitation_widget_description' => 'Виджет выводит фотографии пользователей, выбранных вручную администратором, по одному во всплывающем окне',
            'users_invitation_widget_start_chat'  => 'Начать разговор',
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
        $id     = array_rand($params['id_user']);
        $return = '';

        if (isset($id)) {
            $user    = $this->CI->Users_model->get_user_by_id($params['id_user'][$id], true);
            $seolink = rewrite_link('users', 'view', $user);

            $return .= '
			<style>
				.users-block{overflow: auto;}
				.users-block img{outline: none; background: #FFFFFF; width: 60px; height: 60px; float:left; padding-right: 10px;}
				.users-block .item{padding: 4px; font-size: 12px;}
				.users-block .item:last-child{border: none;}
				.users-block .item .image{float:left;}
				.users-block .item .image a{position: relative; font-size: 0.1px; font-size: 0; display: inline-block; font-size: 12px;}
				.users-block .item .body{
					font-size: 12px;
					font-family: arial;
					font-weight: bold;
				}
				.users-block .item p.headline{clear: both; font-style: italic; margin-bottom: 0px;}
				.users-block .body{position: relative; overflow: hidden; margin-left: 0;}


				a.button15 {
				  display: inline-block;
				  font-family: arial,sans-serif;
				  font-weight: bold;
				  text-decoration: none;
				  user-select: none;
				  padding: .2em 1.2em;
				  outline: none;
				  border: 1px solid rgba(0,0,0,.1);
				  border-radius: 2px;
				  background: rgb(245,245,245) linear-gradient(#f4f4f4, #f1f1f1);
				  transition: all .218s ease 0s;
				  max-width: 150px;
				  width: 80%;
				  text-align: center;
				  margin-top: 5px;
				}
				a.button15:hover {
				  color: rgb(24,24,24);
				  border: 1px solid rgb(198,198,198);
				  background: #f7f7f7 linear-gradient(#f7f7f7, #f1f1f1);
				  box-shadow: 0 1px 2px rgba(0,0,0,.1);
				}
				a.button15:active {
				  color: rgb(51,51,51);
				  border: 1px solid rgb(204,204,204);
				  background: rgb(238,238,238) linear-gradient(rgb(238,238,238), rgb(224,224,224));
				  box-shadow: 0 1px 2px rgba(0,0,0,.1) inset;
				}
				</style>

			<div class="users-block" id="users-block">';

            $return .= '
			<div class="user-gallery">
				<div class="item" >
					<div class="user">
						<div class="image">
							<a href="' . $seolink . '" target="_blank"><img alt="" src="' . $user['media']['user_logo']['thumbs']['middle'] . '" /></a>
						</div>
						<div class="body">
							<div class="text-overflow"><a href="' . $seolink . '" target="_blank" title="' . $user['output_name'] . '">' . $user['output_name'] . '</a> ' . l('users_invitation_widget_text', 'widgets') . '</div>
						</div>
						<a href="' . $seolink . '" target="_blank" class="button15">' . l('users_invitation_widget_start_chat', 'widgets') . '</a>
					</div>
				</div></div>';

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
