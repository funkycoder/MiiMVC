<?php

namespace Mii\Model;

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
abstract class DataService {

    protected $pkName;
    protected $tableName;
    protected $QUOTE_STYLE; // valid types are MYSQL,MSSQL,ANSI
    protected $COMPRESS_ARRAY = true;
    protected $rs = array(); // for holding all object property variables

    //Database credentials
    const DB_SERVER = "localhost";
    const DB_NAME = "user";
    const DB_USER_READ = "user_read";
    const DB_PASSWORD_READ = "read";
    const DB_USER_WRITE = "user_write";
    const DB_PASSWORD_WRITE = "write";

    function __construct($pkName = '', $fields = array(), $tableName = '', $quoteStyle = 'MYSQL', $compressArray = true) {
        $this->pkName = $pkName; //Name of auto-incremented Primary Key
        $this->tableName = $tableName; //Corresponding table in database  
        $this->QUOTE_STYLE = $quoteStyle;
        $this->COMPRESS_ARRAY = $compressArray;

        //initialize property array
        foreach ($fields as $field) {
            $this->rs[$field] = '';
        }
        //must initialize pkName for insert() have a placeholder to return value
        //assign this var to NULL equal to unset this var (isset=false)
        $this->rs[$pkName] = 0;
    }

    //interceptors (Magic functions)
    public function __get($key) {
        $method = "get{$key}";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->rs[$key];
    }

    public function __set($key, $val) {
        $method = "set{$key}";
        if (method_exists($this, $method)) {
            return $this->$method($val);
        }
        if (isset($this->rs[$key]))
        //make sure pkName have an int value
            $this->rs[$key] = ($key == $this->pkName) ? ((int) $val) : $val;
    }

    public function getProperties() {
        return $this->rs;
    }

    public function setProperties($arr = array()) {
        if (!is_array($arr))
            return $this;
        foreach ($arr as $key => $value)
            $this->$key = $value;
        return $this;
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
        //serialize will store a string representation fo the data value (array is ok) 
        //then this string will be compressed by gzdeflate!
        return $this->COMPRESS_ARRAY ? \gzdeflate(\serialize($value)) : \serialize($value);
    }

    protected function inflateValue($value) {
        return \unserialize($this->COMPRESS_ARRAY ? \gzinflate($value) : $value);
    }

    protected function resetProperties() {
        $fields = array_keys($this->rs);
        foreach ($fields as $field)
            $this->rs[$field] = '';
        $this->rs[$this->pkName] = 0;
    }

    protected function fill($rs = array()) {
        //the database return a record
        if ($rs) {
            foreach ($rs as $key => $value)
                $this->$key = is_scalar($this->$key) ? $value : $this->inflateValue($value);
            return TRUE;
        } else {
            //if $rs empty then the request return no record. 
            $this->resetProperties();
            return FALSE;
        }
    }

    //Inserts record into database with a new auto-incremented primary key
    public function insert() {
        $conn = $this->getConnection('write');
        $pkName = $this->pkName;
        $tableName = $this->enquote($this->tableName);
        //prepare fields and values array.         
        foreach ($this->rs as $key => $value) {
            $fields[] = $this->enquote($key);
            $values[] = (is_scalar($value)) ? $value : $this->deflateValue($value);
        }
        //prepared statement question mark holder
        for ($i = 0; $i < \count($fields); $i++) {
            $temp[] = '?';
        }
        $sql = 'INSERT INTO ' . $tableName;
        $sql.= ' (' . implode(',', $fields) . ') ';
        $sql .= 'VALUES (' . implode(',', $temp) . ') ';
        $stmt = $conn->prepare($sql);
        //TODO try catch if insert with exist userid
        $stmt->execute($values);
        if (!$stmt->rowCount())
            return FALSE;
        //set the id then return this object
        $this->$pkName = $conn->lastInsertId();
        return TRUE;
    }

    public function retrieve($pkvalue) {
        //get read connection
        $conn = $this->getConnection();
        $sql = 'SELECT * FROM ' . $this->enquote($this->tableName) . ' WHERE ' . $this->enquote($this->pkName) . '=?';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, (int) $pkvalue);
        $stmt->execute();
        $rs = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $this->fill($rs);
    }

    public function update() {
        $pkValue = $this->rs[$this->pkName];
        //$pkValue = 0?
        if (!$pkValue)
            return FALSE;

        $conn = $this->getConnection('write');
        $pkname = $this->enquote($this->pkName);
        $tablename = $this->enquote($this->tableName);

        //prepare fields and values array.         
        foreach ($this->rs as $key => $value) {
            $fields[] = $this->enquote($key) . '=?';
            $values[] = (is_scalar($value)) ? $value : $this->deflateValue($value);
        }
        $sql = 'UPDATE ' . $tablename . ' SET ' . implode(',', $fields);
        $sql .= ' WHERE ' . $pkname . '=' . $pkValue;
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        //number of rows affected
        return $stmt->rowCount();
    }

    public function delete() {
        $conn = $this->getConnection('write');
        $pkname = $this->enquote($this->pkName);
        $pkvalue = (int) $this->rs[$this->pkName];
        $tablename = $this->enquote($this->tableName);

        $sql = 'DELETE FROM ' . $tablename . ' WHERE ' . $pkname . '=' . $pkvalue . ' LIMIT 1';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        //number of rows affected
        return $stmt->rowCount();
    }

    //returns true if primary key is a positive integer
    //if checkdb is set to true, this function will return true if there exists such a record in the database
    public function exists($checkdb = false) {
        $pkvalue = $this->rs[$this->pkName];
        //prepare for the sql statement, enquote fields
        $pkname = $this->enquote($this->pkName);
        $tablename = $this->enquote(($this->tableName));
        if (!$pkvalue)
            return FALSE;
        //if dont check database then this object is not filled with data
        if (!$checkdb)
            return TRUE;
        //Now check database
        $conn = $this->getConnection();
        $sql = 'SELECT 1 FROM ' . $tablename . ' WHERE ' . $pkname . '=' . $pkvalue;
        $result = $conn->query($sql);
        return $result->rowCount();
    }

    public function retrieve_one($wherewhat = '', $bindings = '') {
        //get read connection
        $conn = $this->getConnection();
        $tableName = $this->enquote($this->tableName);
        //one value? then convert it to an array
        if (is_scalar($bindings))
            $bindings = trim($bindings) ? array($bindings) : array();
        $sql = 'SELECT * FROM ' . $tableName;
        if ($wherewhat)
            $sql .= ' WHERE ' . $wherewhat;
        $sql.=' LIMIT 1';
        $stmt = $conn->prepare($sql);
        $stmt->execute($bindings);
        $rs = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $this->fill($rs);
    }

    function retrieve_many($wherewhat = '', $bindings = '') {
        //get read connection
        $conn = $this->getConnection();
        $tableName = $this->enquote($this->tableName);
        //one value? then convert it to an array
        if (is_scalar($bindings))
            $bindings = trim($bindings) ? array($bindings) : array();
        $sql = 'SELECT * FROM ' . $tableName;
        if ($wherewhat)
            $sql .= ' WHERE ' . $wherewhat;
        $stmt = $conn->prepare($sql);
        $stmt->execute($bindings);
        $arr = array();
        $class = get_class($this);
        while ($rs = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $myclass = new $class();
            $myclass->fill($rs);
            $arr[] = $myclass;
        }
        return $arr;
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
    function select($selectwhat = '*', $wherewhat = '', $bindings = '', $pdo_fetch_mode = \PDO::FETCH_ASSOC) {
        //get read connection
        $conn = $this->getConnection();
        $tableName = $this->enquote($this->tableName);
        //one value? then convert it to an array
        if (is_scalar($bindings))
            $bindings = trim($bindings) ? array($bindings) : array();
        $sql = 'SELECT ' . $selectwhat . ' FROM ' . $tableName;
        if ($wherewhat)
            $sql .= ' WHERE ' . $wherewhat;
        $stmt = $conn->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll($pdo_fetch_mode);
    }

}

?>