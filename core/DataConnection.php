<?php

namespace Mii\Model;

/**
 * BaseDao Abstract Class - Provides database connection 
 * and abstract functions to be implemented by the child DAO classes
 */
abstract class DataConnection {

    protected $conn = null;

    const DB_SERVER = "localhost";
    const DB_NAME = "user";
    const DB_USER_READ = "root";
    const DB_PASSWORD_READ = "";
    const DB_USER_WRITE = "root";
    const DB_PASSWORD_WRITE = "";
  
    protected function getConnection($usertype = 'read') {
        //$dsn = 'sqlite:'.APP_PATH.'db/dbname.sqlite';
        $dsn = 'mysql:dbname=' . self::DB_NAME . ';host=' . self::DB_SERVER;
        try {
            if ($usertype == 'read') {
                $this->conn = new \PDO($dsn, self::DB_USER_READ, self::DB_PASSWORD_READ);
            } elseif ($usertype == 'write') {
                $this->conn = new \PDO($dsn, self::DB_USER_WRITE, self::DB_PASSWORD_WRITE);
            } else {
                throw new \Exception('Unregconized connection type.');
            }
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->exec('SET NAMES "utf8"');
        } catch (\PDOException $e) {

            throw new \Exception('Connection failed: ' . $e->getMessage());
        }
    }

}

?>
