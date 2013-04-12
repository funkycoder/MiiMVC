<?php

namespace Mii\Core;

require_once __DIR__ . '/DataModel.php';

abstract class ObjectModel {

    protected $pkName; //primary key name
    protected $properties = array();
    protected $optionalProperties = array();
    protected $controlFields = array(); //control fields form form submission
    protected $dataService; //take care of the interaction btw this obj and from submission
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
        if (isset($this->$key)) {
            return $this->$key;
        }
        return $this->properties[$key];
    }

    public function __set($key, $value) {
        $method = "set{$key}";
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }
        if (isset($this->$key))
            $this->$key = $value;
        elseif (isset($this->properties[$key]))
        //make sure pkName have an int value
            $this->properties[$key] = ($key == $this->pkName) ? ((int) $value) : $value;
    }

    public function __clone() {
        //set pkValue =  0;
        $this->properties[$this->pkName] = 0;
        $this->dataService = clone $this->dataService;
    }

    public function getPkName() {
        return $this->pkName;
    }

    public function getProperties() {
        return $this->properties;
    }

    public function getOptionalProperties() {
        return $this->optionalProperties;
    }

    public function setProperties($arr = array()) {
        //Get values for properties from an array
        if ($arr)
            foreach ($arr as $key => $value)
                $this->$key = $value;
    }

    public function resetProperties() {
        //primary value = 0 and empty string for other properties
        $fields = array_keys($this->properties);
        foreach ($fields as $field)
            $this->properties[$field] = '';
        $this->properties[$this->pkName] = 0;
    }

    public function getControlFields() {
        return $this->controlFields;
    }

//TODO delete this after testing
    public function setControlField($field, $value) {
        $this->controlFields[$field] = $value;
    }
    //TODO To delete
    public function setControlFields($controlFields=  array()){
        $this->controlFields = array_merge($this->controlFields,$controlFields);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getError($field) {
        //if this error has been set then return it
        if (isset($this->errors[$field]))
            return $this->errors[$field];
        //if not set, then return empty string for display use only
        return '';
    }

    public function setError($field, $value) {
        $this->errors[$field] = $value;
    }

    public function isEmpty() {
        //Check pkValue = 0? then this object is not filled with data
        return !$this->properties[$this->pkName];
    }

    public function form_filled() {
        //if the $key in the $_REQUEST has the same name as the properties
        //then assign it the corresponding $key in properties
        foreach ($_REQUEST as $key => $value) {
            if (in_array($key, array_keys($this->properties)))
                $this->$key = is_scalar($value) ? trim($value) : $value;
            //this must be a control field
            else
                $this->controlFields[$key] = $value;
        }
    }

    public function validate() {
        //Check all properties including control fields from form submission
        //ex: newpassword, newpasswordagain
        $allProperties = array_merge($this->properties, $this->controlFields);
        $possibleEmptyFields = array_merge(array_keys($this->optionalProperties), array_keys($this->controlFields), array($this->pkName));
        $success = TRUE;
        foreach ($allProperties as $field => $value) {
            //form_filled already trim the entered value
            if (empty($value)) {
                //if this $field is not allowed to be empty then set error
                if (!in_array($field, $possibleEmptyFields)) {
                    $this->errors[$field] = 'Vui lòng nhập thông tin.';
                    //raise the error flag
                    $success = FALSE;
                }
                //empty field so don't need to check format
                continue;
            }//end if (empty($value))
            //once the $success = FALSE then it will always be FALSE
            //See : $success=$success&&checkFormat ($success = false then checkFormat will be omitted)
            //This is not what we want
            $success = $this->checkFormat($field, $value) && $success;
        }
        return $success;
    }

    abstract function checkFormat($field, $value);

    public function insert() {
        try {
            $success = $this->dataService->insert();
        } catch (\PDOException $e) {
            $this->errors['data'] = 'Lưu record mới thất bại. ';
            //getCode() return SQL error code (PDO error code is in $e->errorInfo[1])
            $this->errors['data'] .= ($e->getCode() == '23000') ? '(Record đã tồn tại.)' : '(' . $e->errorInfo[2] . ')';
            return FALSE;
        }
        if (!$success)
            $this->errors['data'] = 'Lưu record mới thất bại. ';;

        return $success;
    }

    public function update() {
        if ($this->isEmpty()) {
            $this->errors['data'] = 'Record này chưa có dữ liệu (id=0).';
            return FALSE;
        }
        try {
            $success = $this->dataService->update();
        } catch (\PDOException $e) {
            $this->errors['data'] = 'Cập nhật record thất bại. (' . $e->errorInfo[2] . ')';
            return FALSE;
        }
        if (!$success)
            $this->errors['data'] = 'Không có record nào được cập nhật.';;
        return $success;
    }

    public function delete() {
        try {
            $success = $this->dataService->delete();
        } catch (\PDOException $e) {
            $this->errors['data'] = 'Xóa record thất bại.(' . $e->errorInfo[2] . ')';
            return FALSE;
        }
        if (!$success)
            $this->errors['data'] = 'Xóa record thất bại.';
        return $success;
    }

    public function exists() {
        if ($this->isEmpty())
            return FALSE;
        return $this->dataService->exists();
    }

    public function retrieve_data() {
        $pkName = $this->pkName;
        $pkValue = $this->$pkName;
        $tempObject = $this->retrieve($pkValue);
        if ($tempObject->isEmpty()) {
            $this->errors['data'] = 'Record không tồn tại.';
        } else {
            $this->properties = $tempObject->properties;
        }
    }

    public function retrieve($pkValue) {
        return $this->dataService->retrieve($pkValue);
    }

    public function retrieve_one($wherewhat = '', $bindings = '') {
        return $this->dataService->retrieve_one($wherewhat, $bindings);
    }

    public function retrieve_one_by_field($field, $value) {
        return $this->dataService->retrieve_one_by_field($field, $value);
    }

    public function retrieve_many($wherewhat = '', $bindings = '') {
        return $this->dataService->retrieve_many($wherewhat, $bindings);
    }

    public function select($selectwhat = '*', $wherewhat = '', $bindings = '', $pdo_fetch_mode = \PDO::FETCH_ASSOC) {
        return $this->dataService->select($selectwhat, $wherewhat, $bindings, $pdo_fetch_mode);
    }

}

?>
