<?php

namespace Pg\Libraries\Data;

abstract class DataProvider 
{
    const PROVIDER = 'CodeIgniterProvider';
    
    private static $provider = null;
    
    protected $sources = [];
    
    protected $operations = [];
    
    public $limit = 0;
    
    public $page = 1;
    
    public $order_by = [];
        
    public function __construct() 
    {

    }
    
    static public function getProvider() 
    {
        if(is_null(self::$provider)) {
            $class_name = 'Pg\\Libraries\\Data\\Providers\\' . self::PROVIDER;
            self::$provider = new $class_name();
        }
        
        return self::$provider;
    }
    
    public function setSource($source, $fields)
    {
        $this->sources[] = ['source' => $source, 'fields' => $fields]; 
        
        return self::$provider;
    }
    
    public function setCriteriaEqual($field_name, $field_value)
    {
        $this->operations[] = ['operator' => '=', 'field' => $field_name, 'value' => $field_value];
        
        return self::$provider;
    }
    
    public function setCriteriaGreater($field_name, $field_value)
    {
        $this->operations[] = ['operator' => '>', 'field' => $field_name, 'value' => $field_value];
        
        return self::$provider;
    }
    
    public function setLimit($limit)
    {
        $this->limit = $limit;
        
        return self::$provider;
    }  
    
    public function setPage($page)
    {
        $this->page = $page;
        
        return self::$provider;
    }  
    
    public function setOrderBy($field, $direction)
    {
        $this->order_by = [$field => $direction];
        
        return self::$provider;
    }  
    
    public function clear() 
    {
        $this->sources = [];
        $this->operations = [];
        $this->limit = 0;
        $this->page = 1;
        $this->order_by = [];
    }
    
    abstract public function getObject();
    abstract public function getList();
    abstract public function save($id, $data);
    abstract public function delete();
    
}
