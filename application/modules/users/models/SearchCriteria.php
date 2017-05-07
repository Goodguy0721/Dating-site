<?php

namespace Pg\Modules\Users\Models;

/**
 * Users search model
 *
 * Provides methods to search user data
 *
 * @return boolean
 */
class SearchCriteria extends \Model
{
    private $ci;

    private $user_id = 0;

    private $criteria = [];

    private $exclude_ids = [];

    private $operations = [];

    public function __construct()
    {
        parent::__construct();

        $this->ci = get_instance();

        $search_data['full_criteria'] = array();

        if ($this->ci->session->userdata('auth_type') == 'user') {
            $this->user_id = intval($this->ci->session->userdata('user_id'));

            if ($this->ci->pg_module->is_module_installed('perfect_match')) {
                $this->ci->load->model('Perfect_match_model');
                $search_data = $this->ci->Perfect_match_model->getUserParams($this->user_id);
            }
        }

        $this->ci->load->model('Users_model');

        $this->criteria = $this->ci->Users_model->get_common_criteria($search_data['full_criteria']);
    }

    public function excludeUsers(array $users_ids)
    {
        $this->exclude_ids = array_merge($users_ids);
    }

    public function exсludeCurrentUser()
    {
        $this->excludeUsers(array($this->user_id));
    }

    public function greaterThan($field_name, $field_value) {
        $this->operation[] = ['operator' => '>', 'field' => $field_name, 'value' => $field_value];
    }

    public function equal($field_name, $field_value) {
        $this->operation[] = ['operator' => '=', 'field' => $field_name, 'value' => $field_value];
    }

    public function getCriteria()
    {
        $this->generateExcludeIds();

        return $this->criteria;
    }

    private function generateExcludeIds()
    {
        $exclude_ids = array_unique($this->exclude_ids);
        switch (count($exclude_ids)) {
            case 0:
                break;

            case 1:
                $this->criteria['where']['id !='] = $exclude_ids[0];
                break;

            default:
                $this->criteria['where_sql'][] = "id NOT IN (" . implode(', ', $exclude_ids) . ")";
                break;
        }
    }

    private function generateOperations()
    {
        foreach ($this->operations as $operation) {
            $this->criteria['where'][$operation['field'] . ' ' . $operation['operator']] =
                $operation['value'];
        }
    }
}
