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
class Users_listfeatured_widget extends Model
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
    private $gid = 'users_listfeatured_widget';

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
        'count',
        'user_type',
    );

    /**
     * Widget language data
     *
     * @var array
     */
    private $langs_data = array(
        'en' => array(
            'error_empty_users_count'               => 'Please indicate the amount of users',
            'error_empty_users_types'               => 'Please select user type',
            'field_users_count'                     => 'Amount of users',
            'field_users_type'                      => 'User type',
            'users_listfeatured_widget_name'        => 'Featured users widget',
            'users_listfeatured_widget_description' => 'The widget displays featured site users (carousel)',
        ),
        'ru' => array(
            'error_empty_users_count'               => 'Укажите количество пользователей',
            'error_empty_users_types'               => 'Укажите тип пользователя',
            'field_users_count'                     => 'Количество пользователей',
            'field_users_type'                      => 'Тип пользователя',
            'users_listfeatured_widget_name'        => 'Виджет с избранными пользователями',
            'users_listfeatured_widget_description' => 'Виджет выводит фотографии избранных пользователей сайта (карусель)',
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
    }

    /**
     * Generate widget
     *
     * @param array $params widget parameters
     */
    public function generate($params)
    {
        $return = '';
        if (isset($data['user_type']) && !empty($data['user_type'])) {
            $params['user_type'] = $data['user_type'];
        }
        if (isset($data['count']) && !empty($data['count'])) {
            $params['count'] = intval($data['count']);
        }
        if (!empty($params['user_type']) && $params['count']) {
            $params['where']['featured_end_date !='] = '0000-00-00 00:00:00';
            if ($params['user_type']) {
                $params['where_in']['user_type'] = $params['user_type'];
            }
            $order_by['featured_end_date'] = 'DESC';

            $users = $this->CI->Users_model->get_users_list(1, $params['count'], $order_by, $params);

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

        if (isset($data['user_type'])) {
            $return['data']['user_type'] = $data['user_type'];
        }

        if (isset($data['count'])) {
            $return['data']['count'] = intval($data['count']);
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
        $user_types = $this->CI->Properties_model->get_property('user_type');

        $theme = $this->CI->pg_theme->return_active_settings('admin');

        if ($theme['theme'] == 'gentelella') {
          $return     = '
              <div class="form-group">
                  <label class="control-label col-sm-3 col-xs-12">' . l('field_users_type', 'widgets') . ':</label>
                  <div class="col-sm-9 col-xs-12">';
          foreach ($user_types['option'] as $key => $type) {
              $checked = '';
              if (in_array($key, $data['user_type'])) {
                  $checked = 'checked';
              }
              $return .= ' <input type="checkbox" name="data[settings][user_type][]" value="' . $key . '" class="flat" ' . $checked . '> ' . $type;
          }
          $return .= '
                  </div>
              </div>
              <div class="form-group">
                  <label class="control-label col-sm-3 col-xs-12">' . l('field_users_count', 'widgets') . ':</label>
                  <div class="col-sm-9 col-xs-12">
                    <input type="text" name="data[settings][count]" value="' . intval($data['count']) . '" class="form-control">
                  </div>
              </div>';
        } else {
          $return     = '
              <div class="row">
                  <div class="h">' . l('field_users_type', 'widgets') . ':</div>
                  <div class="v">';
          foreach ($user_types['option'] as $key => $type) {
              $checked = '';
              if (in_array($key, $data['user_type'])) {
                  $checked = 'checked';
              }
              $return .= '<input type="checkbox" name="data[settings][user_type][]" value="' . $key . '" ' . $checked . '>' . $type;
          }
          $return .= '
                  </div>
              </div>
              <div class="row">
                  <div class="h">' . l('field_users_count', 'widgets') . ':</div>
                  <div class="v"><input type="text" name="data[settings][count]" value="' . intval($data['count']) . '"></div>
              </div>';
        }

        return $return;
    }

    /**
     * Return widget info
     *
     * @return array
     */
    public function get_widget_info()
    {
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
                'count'     => 1,
                'user_type' => array('0' => 1, '1' => 2),
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
