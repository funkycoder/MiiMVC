<?php

namespace Mii\Core;

abstract class ObjectModel {

    protected $pkName;
    protected $properties = array();
    protected $optionalProperties = array();
    protected $dataService;
    protected $results; // results from query for many records
    protected $errors;

    function __construct(DataModel $dataModel, $pkName = '', $fields = array(), $optionalFields = array(), $tableName = '') {
        $dataModel->initDataService($this, $pkName, $tableName);
        $this->dataService = $dataModel;
        $this->pkName = $pkName;
        //initialize required property array
        foreach ($fields as $field) {
            $this->properties[$field] = '';
        }
        //Optional properties exist?
        if ($optionalFields) {
            foreach ($optionalFields as $field) {
                $this->optionalProperties[$field] = '';
            }
            //Now append this to object property array
            $this->properties = array_merge($this->properties, $this->optionalProperties);
        }
        //must initialize pkName for insert() have a placeholder to return value
        //assign this var to NULL equal to unset this var (isset=false)
        $this->properties[$pkName] = 0;
    }

    //interceptors (Magic functions)
    public function __get($key) {
        $method = "get{$key}";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->properties[$key];
    }

    public function __set($key, $val) {
        $method = "set{$key}";
        if (method_exists($this, $method)) {
            return $this->$method($val);
        }
        if (isset($this->properties[$key]))
        //make sure pkName have an int value
            $this->properties[$key] = ($key == $this->pkName) ? ((int) $val) : $val;
    }

    public function __clone() {
        //set pkValue =  0;
        $this->properties[$this->pkName] = 0;
        $this->dataService = clone $this->dataService;
    }

    public function getProperties() {
        return $this->properties;
    }

    public function setProperties($arr = array()) {
        if ($arr)
            foreach ($arr as $key => $value)
                $this->$key = $value;
    }

    public function resetProperties() {
        $fields = array_keys($this->properties);
        foreach ($fields as $field)
            $this->properties[$field] = '';
        $this->properties[$this->pkName] = 0;
    }

    public function getResults() {
        return $this->results;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function isEmpty() {
        //Check pkValue = 0? then this object is not filled with data
        return !$this->properties[$this->pkName];
    }

    public function count() {
        return count($this->properties);
    }

    public function insert() {
        $this->errors['data'] = 'Lưu record mới thất bại. ';
        try {
            $success = $this->dataService->insert();
        } catch (\PDOException $e) {
            //getCode() return SQL error code (PDO error code is in $e->errorInfo[1])
            $this->errors['data'] .= ($e->getCode() == '23000') ? '(Record đã tồn tại.)' : '(' . $e->errorInfo[2] . ')';
            return FALSE;
        }
        if ($success)
            $this->errors['data'] = '';
        return $success;
    }

    public function retrieve($pkValue) {
        return $this->dataService->retrieve($pkValue);
    }

    public function update() {
        if ($this->isEmpty()) {
            $this->errors['data'] = 'Record này chưa có dữ liệu (id=0).';
            return FALSE;
        }
        $this->errors['data'] = 'Không có record nào được cập nhật.';
        try {
            $success = $this->dataService->update();
        } catch (\PDOException $e) {
            $this->errors['data'] .= 'Cập nhật record thất bại. (' . $e->errorInfo[2] . ')';
            return FALSE;
        }
        if ($success)
            $this->errors['data'] = '';
        return $success;
    }

    public function delete() {
        $this->errors['data'] = 'Xóa record thất bại.';
        try {
            $success = $this->dataService->delete();
        } catch (\PDOException $e) {
            $this->errors['data'] .= '(' . $e->errorInfo[2] . ')';
            return FALSE;
        }
        if ($success)
            $this->errors['data'] = '';
        return $success;
    }

    public function exists() {
        if ($this->isEmpty())
            return FALSE;
        return $this->dataService->exists();
    }

    public function retrieve_one($wherewhat = '', $bindings = '') {
        $this->dataService->retrieve_one($wherewhat, $bindings);
    }

    public function retrieve_many($wherewhat = '', $bindings = '') {
        $this->results = $this->dataService->retrieve_many($wherewhat, $bindings);
    }

    public function select($selectwhat = '*', $wherewhat = '', $bindings = '', $pdo_fetch_mode = \PDO::FETCH_ASSOC) {
        $this->results = $this->dataService->select($selectwhat, $wherewhat, $bindings, $pdo_fetch_mode);
    }

}

?>
