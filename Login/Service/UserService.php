<?php

namespace Mii\Login\Service;

use Mii\Login\Model;

require_once '..\Login\Model\UserData.php';


class UserService extends Model\UserData {

    const SESSION_TIME_LIMIT = 900; //15*60 = 15 minutes
    const COOKIE_EXPIRE = 8640000;  //60*60*24*100 seconds = 100 days by default
    const COOKIE_PATH = "/";  //Available in whole domain

    public function __construct() {
        if (!isset($_SESSION)) {
            session_start();
            ob_start();
        }
        parent::__construct();
    }
   

    public function login() {
        $myObject = $this->myObject;
        $success = $this->checkPassword();
        if (!$success) {
            $error ='Đăng nhập thất bại. (Email/Password không đúng)';
            $myObject->setError('login',$error);
            return FALSE;
        }
        $hashOK = $this->insertHash();
        if (!$hashOK) {
            $error = 'Đăng nhập thất bại. Lỗi hệ thống. Xin thử lại.';
            $myObject->setError('login',$error);
            return FALSE;
        }
        //Set user session now.
        $this->setUserSession();
        //Remember login credentials ? set Cookie
        if (isset($myObject->controlFields['remember'])) {
            $this->setUserCookie();
        }
        return TRUE;
    }

    
    private function setUserSession() {
        //new session id. This generate $_COOKIE[PHPSESSID]
        session_regenerate_id();
        //store object properties only
        $_SESSION['user'] = $this->myObject->properties;
        $_SESSION['start'] = time();
    }

    private function unsetUserSession() {
        $_SESSION = array();
        session_destroy();
        //remove also the cookie
        $this->unsetUserCookie();
    }

    private function setUserCookie() {
        setcookie("userid", $this->myObject->userid, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
        setcookie("hash", $this->myObject->hash, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
    }

    private function getUserCookie() {
        $this->myObject->useremail = $_COOKIE['userid'];
        $this->myObject->hash = $_COOKIE['hash'];
    }

    private function unsetUserCookie() {
        foreach (array_keys($_COOKIE) as $cookieName){
            setcookie($cookieName, '', time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
        }       
//        if (isset($_COOKIE[session_name()])) {
//            setcookie(session_name(), "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
//        }
    }
}

?>
