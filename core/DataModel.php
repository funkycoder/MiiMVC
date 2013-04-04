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
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @author Quan Nguyen <bsquan2009@yahoo.com> http://drquan.net
 * @copyright (c) 2013, Quan Nguyen 
 * @author Eric Koh <erickoh75@gmail.com> http://kissmvc.com
 * @copyright (c) 2008-2012, Eric Koh {kissmvc.php version 0.72}
 */
abstract class DataModel {

    protected $QUOTE_STYLE; // valid types are MYSQL,MSSQL,ANSI
    protected $COMPRESS_ARRAY; //valid only for MySQL BLOB field
    protected $myObject;
    protected $pkName;
    protected $tableName;

    //Database credentials

    const DB_SERVER = "localhost";
    const DB_NAME = "user";
    const DB_USER_READ = "user_read";
    const DB_PASSWORD_READ = "read";
    const DB_USER_WRITE = "user_write";
    const DB_PASSWORD_WRITE = "write";

    function __construct($quoteStyle = 'MYSQL', $compressArray = true) {
        $this->QUOTE_STYLE = $quoteStyle;
        $this->COMPRESS_ARRAY = $compressArray;
    }

    public function initDataService(ObjectModel $myObject, $pkName = '', $tableName = '') {
        $this->myObject = $myObject;
        $this->pkName = $pkName;
        $this->tableName = $tableName;
    }

    protected function enquote($name) {
        switch ($this->QUOTE_STYLE) {
            case 'MYSQL' :
                return '`' . $name . '`';
            case 'MSSQL' :
                return '[' . $name . ']';
            case 'ANSI':
                return '"' . $name . '"';
            default :
                return $name;
        }
    }

    protected function getConnection($usertype = 'read') {
        //$dsn = 'sqlite:'.APP_PATH.'db/dbname.sqlite';
        $dsn = 'mysql:dbname=' . self::DB_NAME . ';host=' . self::DB_SERVER;
        try {
            if ($usertype == 'read') {
                $conn = new \PDO($dsn, self::DB_USER_READ, self::DB_PASSWORD_READ);
            } elseif ($usertype == 'write') {
                $conn = new \PDO($dsn, self::DB_USER_WRITE, self::DB_PASSWORD_WRITE);
            } else {
                throw new \Exception('Unregconized connection type.');
            }
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $conn->exec('SET NAMES "utf8"');
        } catch (\PDOException $e) {

            throw new \Exception('Connection failed: ' . $e->getMessage());
        }
        return $conn;
    }

    protected function deflateValue($value) {
        //serialize will store a string representation for the data value (array is ok) 
        //then this string will be compressed by gzdeflate!
        return $this->COMPRESS_ARRAY ? \gzdeflate(\serialize($value)) : \serialize($value);
    }

    protected function inflateValue($value) {
        return \unserialize($this->COMPRESS_ARRAY ? \gzinflate($value) : $value);
    }

    protected function fill($rs = array()) {
        //the database return a record
        $myObject = $this->myObject;
        if ($rs) {
            foreach ($rs as $key => $value)
                $myObject->$key = is_scalar($myObject->$key) ? $value : $this->inflateValue($value);
            return TRUE;
        } else {
            //if $rs empty then the request return no record. 
            $myObject->resetProperties();
            return FALSE;
        }
    }

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
        for ($i = 0; $i < $myObject->count(); $i++) {
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
        return $this->fill($rs);
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
        return $this->fill($rs);
    }

    function retrieve_many($wherewhat = '', $bindings = '') {
        return $this->select('*', $wherewhat, $bindings);
    }

    /**
     * Select multiple records base on input criterion
     * 
     * @example $user = new User();
     * @example $result_array = $user->select("username,password", "username LIKE ?", 'Q%');
     * @example print_r($result_array);
     * @param string $selectwhat
     * @param string $wherewhat
     * @param array $bindings A string is also accepted
     * @param PDO::FETCH_* $pdo_fetch_mode
     * @return array An array of returned records
     */
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
            $myclass->properties = $rs;
            $arr[] = $myclass;
        }
        return $arr;
    }

}

?>