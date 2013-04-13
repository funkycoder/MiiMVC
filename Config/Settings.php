<?php
namespace Mii;
class Settings {
    ################################################################
    ##                                                            ##
    ##                 DATABASE CREDENTIALS AND OPTIONS           ##
    ##                                                            ##
    ################################################################

    static $DB_SERVER = 'localhost';
    static $DB_NAME = 'user';
    static $DB_USER_READ = 'user_read';
    static $DB_PASSWORD_READ = 'read';
    static $DB_USER_WRITE = 'user_write';
    static $DB_PASSWORD_WRITE = 'write';
    //Quote style and compress non scalar value
    static $QUOTE_STYLE = 'MYSQL'; // valid types are MYSQL,MSSQL,ANSI
    static $COMPRESS_ARRAY = TRUE; //valid only for MySQL BLOB field
    
    ################################################################
    ##                                                            ##
    ##                 SESSIONS AND COOKIES                       ##
    ##                                                            ##
    ################################################################
    
    static $SESSION_TIME_LIMIT = 900; //15*60 = 15 minutes
    static $COOKIE_EXPIRE = 8640000; //60*60*24*100 seconds = 100 days by default
    static $COOKIE_PATH = "/"; //Available in whole domain
}

?>
