<?php

namespace Mii\Core;

//===============================================================
// Data Service Model/ORM
//===============================================================
/**
 * The Data Service Model ORM (Object-Relational Mapping)
 * 
 * MiiMVC provides a "Model" ORM class to let you map your database tables as PHP objects. It is built on PDO and thus requires PHP5.
 * Data objects that extend the Model class will gain the following 6 operations: Select,Insert, Retrieve, Update, Delete and Exists.
 * This Model is inspired by Eric Koh's work. Most of the original codes were rewritten or improved.
 * 
 * @version 1.0 (13 of April, 2013)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @author Quan Nguyen <bsquan2009@yahoo.com> http://drquan.net
 * @copyright (c) 2013, Quan Nguyen 
 * @author Eric Koh <erickoh75@gmail.com> http://kissmvc.com
 * @copyright (c) 2008-2012, Eric Koh {kissmvc.php version 0.72}
 */
abstract class DataModel {
    protected $myObject; //Related object
    protected $pkName; //Keep primary key
    protected $tableName; // Table name
    protected $appConfig;

    public function __construct() {
        $this->appConfig = \AppConfig::getInstance();        
    }

    public function initDataService(ObjectModel $myObject, $pkName = '', $tableName = '') {
        $this->myObject = $myObject;
        $this->pkName = $pkName;
        $this->tableName = $tableName;
    }

    protected function enquote($name) {
        return $this->appConfig->enquote($name);
    }

    protected function getConnection($usertype = 'read') {
       
       return $this->appConfig->getConnection($usertype);
    }

    protected function deflateValue($value) {
        //serialize will store a string representation for the data value (array is ok) 
        //then this string will be compressed by gzdeflate!
        return COMPRESS_ARRAY ? \gzdeflate(\serialize($value)) : \serialize($value);
    }

    protected function inflateValue($value) {
        return \unserialize(COMPRESS_ARRAY ? \gzinflate($value) : $value);
    }

     ####################################################################################################################
    #                                                                                                                  ##
    #               THE FOLLOWING FUNCTIONS WILL BE HANDLED IN OBJECTMODEL FUNCTIONS                                   ##
    #                                                                                                                  ##
    #####################################################################################################################
    
    //Inserts record into database with a new auto-incremented primary key
    public function insert() {
        $conn = $this->getConnection('write');
        $myObject = $this->myObject;
        $pkName = $this->pkName;
        $tableName = $this->tableName;
        //prepare fields and values array.         
        foreach ($myObject->properties as $key => $value) {
            $fields[] = $this->enquote($key);
            $values[] = (is_scalar($value)) ? $value : $this->deflateValue($value);
        }
        //prepared statement question mark holder
        for ($i = 0; $i < count($fields); $i++) {
            $temp[] = '?';
        }
        $sql = 'INSERT INTO ' . $this->enquote($tableName);
        $sql.= ' (' . implode(',', $fields) . ') ';
        $sql .= 'VALUES (' . implode(',', $temp) . ') ';
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        if (!$stmt->rowCount())
            return FALSE;
        //set the id then return this object
        $myObject->$pkName = $conn->lastInsertId();
        return TRUE;
    }

    public function update() {
        $conn = $this->getConnection('write');
        $myObject = $this->myObject;
        $pkName = $this->pkName;
        $pkValue = $myObject->$pkName;
        $tablename = $this->tableName;
        //prepare fields and values array.         
        foreach ($myObject->properties as $key => $value) {
            $fields[] = $this->enquote($key) . '=?';
            $values[] = (is_scalar($value)) ? $value : $this->deflateValue($value);
        }
        $sql = 'UPDATE ' . $this->enquote($tablename) . ' SET ' . implode(',', $fields);
        $sql .= ' WHERE ' . $this->enquote($pkName) . '=' . $pkValue;
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    public function delete() {
        $conn = $this->getConnection('write');
        $myObject = $this->myObject;
        $pkName = $this->pkName;
        $pkValue = $myObject->$pkName;
        $tablename = $this->tableName;
        $sql = 'DELETE FROM ' . $this->enquote($tablename);
        $sql .= ' WHERE ' . $this->enquote($pkName) . '=' . $pkValue;
        $sql .=' LIMIT 1';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        //number of rows affected
        return $stmt->rowCount();
    }

    public function exists() {
        $conn = $this->getConnection();
        $myObject = $this->myObject;
        $pkName = $this->pkName;
        $pkValue = $myObject->$pkName;
        $tableName = $this->tableName;
        $sql = 'SELECT 1 FROM ' . $this->enquote($tableName) . ' WHERE ' . $this->enquote($pkName) . '=' . $pkValue;
        $result = $conn->query($sql);
        return $result->rowCount();
    }

    public function retrieve($pkValue) {
        //get read connection
        $conn = $this->getConnection();
        $pkName = $this->pkName;
        $tableName = $this->tableName;
        $sql = 'SELECT * FROM ' . $this->enquote($tableName) . ' WHERE ' . $this->enquote($pkName) . '=?';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, (int) $pkValue);
        $stmt->execute();
        $rs = $stmt->fetch(\PDO::FETCH_ASSOC);
        $class = get_class($this->myObject);
        $myclass = new $class();
        foreach ($rs as $key => $value)
            $myclass->$key = is_scalar($myclass->$key) ? $value : $this->inflateValue($value);
        return $myclass;
    }

    public function retrieve_one($wherewhat = '', $bindings = '') {
        //get read connection
        $conn = $this->getConnection();
        $tableName = $this->tableName;
        //one value? then convert it to an array
        if (is_scalar($bindings))
            $bindings = trim($bindings) ? array($bindings) : array();
        $sql = 'SELECT * FROM ' . $this->enquote($tableName);
        if ($wherewhat)
            $sql .= ' WHERE ' . $wherewhat;
        $sql.=' LIMIT 1';
        $stmt = $conn->prepare($sql);
        $stmt->execute($bindings);
        $rs = $stmt->fetch(\PDO::FETCH_ASSOC);
        $class = get_class($this->myObject);
        $myclass = new $class();
        foreach ($rs as $key => $value)
            $myclass->$key = is_scalar($myclass->$key) ? $value : $this->inflateValue($value);
        return $myclass;
    }

    public function retrieve_one_by_field($fieldName, $fieldValue) {
        $conn = $this->getConnection();
        $tableName = $this->tableName;
        //one value? then convert it to an array
        $sql = 'SELECT * FROM ' . $this->enquote($tableName);
        $sql .= ' WHERE ' . $this->enquote($fieldName) . "='$fieldValue'";
        $sql.=' LIMIT 1';
        $result = $conn->query($sql);
        $rs = $result->fetch(\PDO::FETCH_ASSOC);
        $class = get_class($this->myObject);
        $myclass = new $class();
        foreach ($rs as $key => $value)
            $myclass->$key = is_scalar($myclass->$key) ? $value : $this->inflateValue($value);
        return $myclass;
    }

    function retrieve_many($wherewhat = '', $bindings = '') {
        return $this->select('*', $wherewhat, $bindings);
    }

    function select($selectwhat = '*', $wherewhat = '', $bindings = '') {
        //get read connection
        $conn = $this->getConnection();
        $myObject = $this->myObject;
        $tableName = $this->tableName;
        //one value? then convert it to an array
        if (is_scalar($bindings))
            $bindings = trim($bindings) ? array($bindings) : array();
        $sql = 'SELECT ' . $selectwhat . ' FROM ' . $this->enquote($tableName);
        if ($wherewhat)
            $sql .= ' WHERE ' . $wherewhat;
        $stmt = $conn->prepare($sql);
        $stmt->execute($bindings);
        $arr = array();
        $class = get_class($myObject);
        while ($rs = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $myclass = new $class();
            foreach ($rs as $key => $value)
                $myclass->$key = is_scalar($myclass->$key) ? $value : $this->inflateValue($value);
            $arr[] = $myclass;
        }
        return $arr;
    }

}

?>