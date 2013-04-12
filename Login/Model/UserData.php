<?php

namespace Mii\Login\Model;

use Mii\Core;

require_once '..\Core\DataModel.php';
/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

/**
* Description of UserService
*
* @author Quan Nguyen
*/
class UserData extends Core\DataModel {

    const SESSION_TIME_LIMIT = 900; //15*60 = 15 minutes
    const COOKIE_EXPIRE = 8640000; //60*60*24*100 seconds = 100 days by default
    const COOKIE_PATH = "/"; //Available in whole domain

    public function __construct() {
        if (!isset($_SESSION)) {
            session_start();
            ob_start();
        }
        parent::__construct();
    }

    public function checkLogin() {
        if (isset($_SESSION['user']) && isset($_SESSION['start'])) {
            $this->myObject->properties = $_SESSION['user'];
            $hashOK = $this->checkHash();
            if (!$hashOK) {
                $this->myObject->setError['login'] = 'Account này đang được đăng nhập ở một máy khác. Vui lòng đăng nhập lại.';
            } elseif ($this->sessionTimeOut()) {
                $this->myObject->setError['login'] = 'Phiên làm việc hết giờ (Session timeout). Vui lòng đăng nhập lại.';
            } else {
                //hash ok and session is not timeout
                return TRUE;
            }
            //error
            $this->unsetUserSession();
            return FALSE;
        } elseif (isset($_COOKIE['userid']) && isset($_COOKIE['hash'])) {
            $this->myObject = $this->retrieve($_COOKIE['userid']);
            //correct userid? get properties from database
            if ($this->myObject->isEmpty()) {
                $this->unsetUserCookie();
                return FALSE;
            }
            $this->setUserSession();
            return TRUE;
        } else {
            //No session, no cookie?
            return FALSE;
        }
    }

    public function login() {
        $myObject = $this->myObject;
        $success = $this->checkPassword();
        if (!$success) {
            $error = 'Đăng nhập thất bại. (Email/Password không đúng)';
            $myObject->setError('login', $error);
            return FALSE;
        }
        $hashOK = $this->insertHash();
        if (!$hashOK) {
            $error = 'Đăng nhập thất bại. Lỗi hệ thống. Xin thử lại.';
            $myObject->setError('login', $error);
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

    private function sessionTimeOut() {
        $now = time();
        if ($now > $_SESSION['start'] + self::SESSION_TIME_LIMIT) {
            return true;
        } else {
            $_SESSION['start'] = time();
            return false;
        }
    }

    private function setUserSession() {
        //new session id. This generate $_COOKIE[PHPSESSID]
        session_regenerate_id();
        //store object properties only
        $_SESSION['user'] = $this->myObject->properties;
        $_SESSION['start'] = time();
    }

//TODO Change this back to private after debug
    public function unsetUserSession() {
        $_SESSION = array();
        session_destroy();
        //remove also the cookie
        $this->unsetUserCookie();
    }

    private function setUserCookie() {
        setcookie("userid", $this->myObject->userid, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
        setcookie("hash", $this->myObject->hash, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
    }

//TODO change this back to private after debugging
    public function unsetUserCookie() {
        foreach (array_keys($_COOKIE) as $cookieName) {
            setcookie($cookieName, '', time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
        }
// if (isset($_COOKIE[session_name()])) {
// setcookie(session_name(), "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
// }
    }

    private function encryptPassword($salt, $password) {
        //sha1 required 40 characters
        return sha1($salt . $password);
    }

    #####################################################################################################################
    # #
    # DATAMODEL EXTENSION - ALL FUNCTIONS WHICH DEAL WITH DATABASE ARE DEFINED HERE #
    # #
    #####################################################################################################################

    public function insert() {
        $myObject = $this->myObject;
        $myObject->salt = time();
        $myObject->timestamp = time();
        $myObject->password = $this->encryptPassword($myObject->salt, $myObject->password);
        return parent::insert();
    }

    public function insertHash() {
        $this->myObject->hash = md5(microtime());
        return parent::update();
    }

    public function update($UPDATE_PASSWORD = FALSE) {
        $myObject = $this->myObject;
        $myObject->timestamp = time();
        IF ($UPDATE_PASSWORD) {
            $myObject->password = $this->encryptPassword($myObject->salt, $myObject->password);
        }
        return parent::update();
    }

    public function checkEmailTaken() {
        $tempObject = retrieve_one_by_field('useremail', $tempObject->myObject->useremail);
        return ($tempObject . isEmpty());
    }

    public function checkPassword() {
        $myObject = $this->myObject;
        $tempObject = $this->retrieve_one_by_field('useremail', $myObject->useremail);
        if ($tempObject->password == $this->encryptPassword($tempObject->salt, $myObject->password)) {
            //correct password? get all related data from database
            $myObject->properties = $tempObject->properties;
            return TRUE;
        }
        //Wrong password!.
        return FALSE;
    }

    public function checkHash() {
        $myObject = $this->myObject;
        $pkName = $myObject->pkName;
        $tempObject = $this->retrieve($myObject->$pkName);
        return ($tempObject->hash == $myObject->hash);
    }

}

?>