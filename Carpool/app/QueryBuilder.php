<?php

abstract class QueryBase {
    
    protected $_table;
    
    public function __construct($table) {
        $this->_table = $table;
    }
    
}

class QueryInsert extends QueryBase {
    
    private $_columns;
    
    public function setColumns($columns) {
        $this->_columns = $columns;
        return $this;
    }
    
    public function __toString() {
        assert('isset($this->_columns) && count($this->_columns) > 0');
        
        $sql = 
            'INSERT INTO ' . $this->_table . '(' . implode(',', $this->_columns) . ') ' .
            'VALUES(';
        for ($i = 0; $i < count($this->_columns); ++$i) {
            $sql .= '?,';
        }
        $sql = substr($sql, 0, -1);
        $sql .= ')';
        
        return $sql;
    }
       
}

class QueryUpdate extends QueryBase {
    
    private $_columns;
    private $_condition;
    
    public function setCondition($condition) {
        $this->_condition = $condition;
        return $this;        
    }
    
    public function setColumns($columns) {
        $this->_columns = $columns;
        return $this;
    }
    
    public function __toString() {
        assert('isset($this->_columns) && count($this->_columns) > 0');
        
        $sql = 
            'UPDATE ' . $this->_table . ' SET ';
        
        foreach ($this->_columns as $column) {
            $sql .= $column . '=?,';
        }
        $sql = substr($sql, 0, -1);
        
        if (isset($this->_condition)) {
            $sql .= ' WHERE ' . $this->_condition;
        }
        
        return $sql;
    }
    
    
}
