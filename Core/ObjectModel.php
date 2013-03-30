<?php

namespace Mii\Core;

abstract class ObjectModel {
    protected $pkName;
    protected $properties = array();
    protected $optionalProperties = array();
    protected $dataService;

//TODO cut down variables input here
    function __construct(DataModel $dataModel, $pkName = '', $fields = array(), $optionalFields = array(), $tableName = '') {
        $dataModel->initDataModel($this,$pkName,$tableName);
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

    public function isEmpty() {
        //Check pkValue = 0? then this object is not filled with data
        return !$this->properties[$this->pkName];
    }

//TODO Implement CLONE here
}

?>
