<?php

class QueryBase {
    
    protected $table;
    protected $columns;
    protected $values;
    
    public function table($table) {
        $this->table = $table;
        return $this;
    }
    
    public function data($data) {
        $this->columns = array_keys($data);
        $this->values = array_values($data);
        return $this;
    }
    
    public function getSql() {
        return $this->sql;
    }
    
}

class QueryInsert extends QueryBase {
    
    public function getSql() {
        return 'INSERT INTO ' . $this->table . '(' . join(',', $this->columns) . ') VALUES (' . join($this->values) . ')';
    }
    
}

class SimpleQueryBuilder {
    
    public function insert($table, $values) {
        $queryInsert = new QueryInsert();
        return $queryInsert->getSql();            
    }
    
    
}

