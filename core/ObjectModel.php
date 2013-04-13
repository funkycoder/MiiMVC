<?php

namespace Mii\Core;

require_once __DIR__ . '/DataModel.php'; //Corresponding database actions
//=============================================================================================
// Object Model
//============================================================================================
/**
 * Object mapping with columns in database and control fields entered from form submission
 * 
 * MiiMVC provideS an object model separately from the datamodel (deals with database actions). The idea is user 
 * can use all function related to this object using this object reference only. All database actions implemented in
 * DataModel have exception handling implemented here. So, it is recommended to call those function using this obj reference
 * 
 * Remember : Must define properties with the same names as database column names for the framework to work
 * 
 * @version 1.0 (13 of April, 2013)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @author Quan Nguyen <bsquan2009@yahoo.com> http://drquan.net
 * @copyright (c) 2013, Quan Nguyen 
 */

abstract class ObjectModel {

    protected $pkName; //primary key name
    protected $properties = array(); //this represent all columns in database of this object
    protected $optionalProperties = array(); //could be entered using submission or left blank, these will be appended to $properties
    protected $controlFields = array(); //control fields form form submission
    protected $dataService; //take care of the interaction btw this obj and from submission (DataModel obj type)
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
        //must initialize pkName for insert() have a placeholder to return value (Must be NULL or 0)
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
        //no getMethod or class variable? then it could be from this object properties
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
        //deep clone
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
    public function setControlFields($controlFields = array()) {
        $this->controlFields = array_merge($this->controlFields, $controlFields);
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

    /**
     * Validate current object properties including controlFields from form submission
     * 
     * If the fields could be empty then it is ok when it is empty but when it is not empty it will be 
     * checked by function checkFormat. checkFormat must be implemented in child class.
     * 
     * @return None
     */
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

    #####################################################################################################################
    #                                                                                                                  ##
    #               THE FOLLOWING FUNCTIONS ARE EXCEPTION HANDLER FOR DATAMODEL FUNCTIONS                              ##
    #                                                                                                                  ##
    #####################################################################################################################

    /**
     * Insert this object to database
     * 
     * errors['data'] is set if not successful
     * @return boolean 
     */
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

    /**
     * Update this object to database
     * 
     * errors['data'] is set if not successful
     * @return boolean 
     */
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

    /**
     * Delete this object from database
     * 
     * errors['data'] is set if not successful
     * @return boolean 
     */
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

    /**
     * Check where this object exists
     * 
     * return true if object id not empty and a record exist in database
     * 
     * @return boolean 
     */
    public function exists() {
        if ($this->isEmpty())
            return FALSE;
        return $this->dataService->exists();
    }

    /**
     * Get all data of this object from database
     * 
     * errors['data'] is set if not successful
     * @return boolean 
     */
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

    /**
     * Get an object from database
     * 
     * @param int $pkValue Object unique id
     * @return ObjectModel Return an object of the same type (empty object if record not found) 
     */
    public function retrieve($pkValue) {
        return $this->dataService->retrieve($pkValue);
    }

    /**
     * Get an object from database using sql
     * 
     * @param string $wherewhat SQL selection
     * @param string/array $bindings provided values
     * @example $this->retrieve_one('username=? AND userphone=?',array('Quan','123456'));
     * @return ObjectModel Return an object of the same type (empty object if record not found) 
     */
    public function retrieve_one($wherewhat = '', $bindings = '') {
        return $this->dataService->retrieve_one($wherewhat, $bindings);
    }

    /**
     * Get an object from database using sql
     * 
     * @param string $field field name (column name)
     * @param string/array $value provided values
     * @example $this->retrieve_one_by_field('username','Quan');
     * @return ObjectModel Return an object of the same type (empty object if record not found) 
     */
    public function retrieve_one_by_field($field, $value) {
        return $this->dataService->retrieve_one_by_field($field, $value);
    }

    /**
     * Get many objects from database using sql all columns (properties) will be retrieved
     * 
     * @param string $wherewhat SQL selection
     * @param string/array $bindings provided values
     * @example $this->retrieve_many('username LIKE ','Q%'));
     * @return ObjectModel Return an array of objects of the same type (empty array if record not found) 
     */
    public function retrieve_many($wherewhat = '', $bindings = '') {
        return $this->dataService->retrieve_many($wherewhat, $bindings);
    }

    /**
     * Get many objects from database using sql, selected columns could be defined
     * 
     * @param string $selectwhat column names
     * @param string $wherewhat SQL selection
     * @param string/array $bindings provided values
     * @example $this->retrieve_many('username LIKE ','Q%'));
     * @return ObjectModel Return an array of objects of the same type (empty array if record not found) 
     */
    public function select($selectwhat = '*', $wherewhat = '', $bindings = '', $pdo_fetch_mode = \PDO::FETCH_ASSOC) {
        return $this->dataService->select($selectwhat, $wherewhat, $bindings, $pdo_fetch_mode);
    }

}

?>
