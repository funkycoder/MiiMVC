<?php

//NO NAMESPACE (GLOBAL)

/*
* This file must be put outside of web directory
 */

/**
 * Description of AppConfig
 *
 * @author Quan Nguyen
 */
################################################################
##                                                            ##
##                 DATABASE CREDENTIALS                       ##
##                                                            ##
################################################################
DEFINE('DB_SERVER', 'localhost');
DEFINE('DB_NAME', 'user');
DEFINE('DB_USER_READ', 'user_read');
DEFINE('DB_PASSWORD_READ', 'read');
DEFINE('DB_USER_WRITE', 'user_write');
DEFINE('DB_PASSWORD_WRITE', 'write');
################################################################
##                                                            ##
##                 GLOBAL SETTINGS                            ##
##                                                            ##
################################################################
//TODO Check documentations to see if all this thing work fine
DEFINE('LIVE', FALSE); // Are we live?
DEFINE('CONTACT_EMAIL', 'bsquan2009@yahoo.com'); // Errors are emailed here
DEFINE('BASE_URI', 'C:/xampp/htdocs/MiiMVC/');
DEFINE('BASE_URL', 'localhost/MiiMVC/Ecom1/');
DEFINE('REDIRECT_URL','localhost/MiiMVC/Ecom1/index.php');
DEFINE('PDF_DIR', 'C:/xampp/htdocs/pdf');

################################################################
##                                                            ##
##                 DATABASE AND OPTIONS                       ##
##                                                            ##
################################################################
//Quote style and compress non scalar value
DEFINE('QUOTE_STYLE', 'MYSQL'); // valid types are MYSQL,MSSQL,ANSI
DEFINE('COMPRESS_ARRAY', TRUE); //valid only for MySQL BLOB field
DEFINE('USER_TABLE','users');
//
################################################################
##                                                            ##
##                 SESSIONS AND COOKIES                       ##
##                                                            ##
################################################################
DEFINE('SESSION_TIME_LIMIT', 900); //15*60 = 15 minutes
DEFINE('COOKIE_EXPIRE', 8640000); //60*60*24*100 seconds = 100 days by default
DEFINE('COOKIE_PATH', '/'); //Available in whole domain

class AppConfig {

    private static $instance;
    private $dsn;

    private function __construct() {
        // will run once only 
        //$dsn = 'sqlite:'.APP_PATH.'db/dbname.sqlite';
        $this->dsn = 'mysql:dbname=' . DB_NAME . ';host=' . DB_SERVER; //contruct connection string
    }

    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //TODO Logging error system must be implemented
    public function getConnection($usertype) {
        try {
            if ($usertype == 'read') {
                $conn = new PDO($this->dsn, DB_USER_READ, DB_PASSWORD_READ);
            } elseif ($usertype == 'write') {
                $conn = new PDO($this->dsn, DB_USER_WRITE, DB_PASSWORD_WRITE);
            } else {
                throw new PDOException(); // usertype not available
            }
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Display error
            $conn->exec('SET NAMES "utf8"');
        } catch (PDOException $e) {
            throw new PDOException('Lỗi kiểu kết nối.');
        } catch (Exception $e) {
            throw new PDOException('Lỗi hệ thống.');
        }
        return $conn;
    }

    public function enquote($name) {
        switch (QUOTE_STYLE) {
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

}

?>
