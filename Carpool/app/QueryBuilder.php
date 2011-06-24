<?php

abstract class QueryBase {
    
    protected $_table;
    
    public function __construct($table) {
        $this->_table = $table;
    }
    
}

class Condition {
    
    private $_condText;
    private $_condValues;
    private $_variablesMapping;
    
    public function __construct($condText, $condValues = null) {
        $this->_condText = $condText;
        $this->_condValues = $condValues;
    }
    
    public function __toString() {
        $res = $this->_condText;
        $counter = 1;
        if (isset($this->_condValues)) {
            $this->_variablesMapping = array();
            foreach ($this->_condValues as $val) {
                $varName = ':_cond_' . $counter;
                $res = preg_replace('/\\?/', $varName, $res, 1, $count);
                assert('$count == 1');
                $this->_variablesMapping[$varName] = $val;
                ++$counter;
            }
        }
        return $res;
    }
    
    public function getVariables() {
        return $this->_variablesMapping;
    }
    
    public function hasVariables() {
        return isset($this->_variablesMapping);
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
            //'VALUES(:' . implode(',:', $this->_columns) . ')';
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
            //$sql .= $column . '=:' . $column . ','; 
            $sql .= $column . '=?,';
        }
        $sql = substr($sql, 0, -1);
        
        if (isset($this->_condition)) {
            $sql .= ' WHERE ' . $this->_condition;
        }
        
        return $sql;
    }
    
    
}
