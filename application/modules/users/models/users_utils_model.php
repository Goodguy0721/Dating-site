<?php

namespace Pg\Modules\Users\Models;
    
/**
 * Users utils model
 * 
 * Provides methods to manipulate user data
 * 
 * @return boolean
 */
class Users_utils_model extends \Model
{    
    private $current_user_data = null;
    
    public function isActived() 
    {
        if ($this->session->userdata('auth_type') != 'user') {
            return false;
        }
        
        return $this->session->userdata['activity'] == 1;
    }
    
    public function getCurrentUserData() 
    {
        if (is_null($this->current_user_data)) {
            $this->load->model('Users_model');
            $this->current_user_data = $this->Users_model->get_user_by_id(
                $this->session->userdata('user_id'), false, true);
        }
        
        return $this->current_user_data;
    }
}
