<?php

namespace Pg\Libraries\Data\Providers;

use Pg\Libraries\Data\DataProvider;

class CodeIgniterProvider extends DataProvider 
{
    private $ci = null;
    
    public function __construct() {
        $this->ci = &get_instance();
    }
    
    public function getObject()
    {
        $results = $this->generate();
        if (!empty($results) && is_array($results)) {
            return $results[0];
        }
        
        throw new \Exception('no data');
    }
    
    public function getList()
    {
        $results = $this->generate();        
        if (!empty($results) && is_array($results)) {
            return $results;
        }
        
        return [];
    }
    
    public function save($id, $data)
    {
        if (is_array($id)) {
            $record_ids = $id;
            foreach ($record_ids as $i => $record_id) {
                $sources = $this->sources;
                $record_ids[$i] = $this->save($record_id, $data);
                $this->sources = $sources;
            }
            $id = $record_ids;
        } else {
            if (is_null($id)) {
                $this->ci->db->insert($this->sources[0]['source'], $data);
                $id = $this->ci->db->insert_id();
            } else {
                $this->ci->db->where('id', $id);
                $this->ci->db->update($this->sources[0]['source'], $data);
            }
        }
        
        $this->clear();
        
        return $id;
    }
    
    public function delete()
    {                  
        $this->generateStatments();
             
        $source = array_shift($this->sources);                 
        $this->ci->db->delete($source['source']);

        $this->clear();
    }
    
    public function generate() 
    {
        $source = array_shift($this->sources);        
        $this->ci->db->select(implode(', ', $source['fields']))->from($source['source']);
        
        $this->generateStatments();
        
        $results = $this->ci->db->get()->result_array();

        if (!empty($_ENV['DB_DEBUG'])) {
            fb($this->ci->db->last_query());
        }
        
        $this->clear();

        return $results;
    }    
    
    private function generateStatments() 
    {
        $is_where = false;
        
        $is_where = $is_where || $this->generateExcludeIds();
        $is_where = $is_where || $this->generateOperations();

        if (!$is_where) {
            $this->ci->db->where('1=1', null, false);
        }

        if (!empty($this->order_by)) {
            foreach ($this->order_by as $field => $dir) {
                $this->ci->db->order_by($field . ' ' . $dir);
            }
        }
        
        if ($this->limit > 0) {
            $this->ci->db->limit($this->limit, $this->limit * ($this->page - 1));
        }        
    }
    
    private function generateExcludeIds()
    {
        if (empty($this->exclude_ids)) {
            return false;
        }
        
        $exclude_ids = array_unique($this->exclude_ids);
        switch (count($exclude_ids)) {
            case 0:
                break;

            case 1:
                $this->ci->db->where('id !=', $exclude_ids[0]);
                break;

            default:
                $this->ci->db->where("id NOT IN (" . implode(', ', $exclude_ids) . ")", null, false);
                break;
        }
        
        return true;
    }

    private function generateOperations()
    {
        $is_where = false;
        
        foreach ($this->operations as $operation) {
            if (is_array($operation['value'])) {
                if ($operation['operator'] == '=') {
                    $this->ci->db->where_in($operation['field'], $operation['value']);
                } else {
                    $arr_sql = [];
                    
                    foreach ($operation['value'] as $value) {
                       $arr_sql[] = $operation['field'] . $operation['operator'] . $this->ci->db->escape($value); 
                    }
                    
                    $this->ci->db->where(implode(' AND ', $arr_sql), null, false);
                }
            } else {
                $this->ci->db->where($operation['field'] . ' ' . $operation['operator'], $operation['value']);
            }
            
            $is_where = true;
        }
        
        return $is_where;
    }
}
